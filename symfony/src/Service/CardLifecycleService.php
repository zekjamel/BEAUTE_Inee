<?php

namespace App\Service;

use App\Entity\ConnectedCard;
use Doctrine\ORM\EntityManagerInterface;

final class CardLifecycleService
{
    /** @var array<string, array<string, string>> */
    private const TRANSITIONS = [
        'ordered' => ['configuration_in_progress' => 'Démarrer la configuration technique'],
        'configuration_in_progress' => ['ready_for_collection' => 'Marquer prête pour le rendez-vous client'],
        'initialized' => ['active' => 'Activer la carte'],
        // Kept for cards created with the first version of the workflow.
        'configured' => ['active' => 'Activer la carte'],
        'active' => ['suspended' => 'Suspendre la carte'],
        'suspended' => ['active' => 'Réactiver la carte'],
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return array<string, string> */
    public function availableTransitions(ConnectedCard $card): array
    {
        return self::TRANSITIONS[$card->getStatus()] ?? [];
    }

    public function transition(ConnectedCard $card, string $targetStatus): void
    {
        if (!array_key_exists($targetStatus, $this->availableTransitions($card))) {
            throw new \DomainException('Cette transition de carte n’est pas autorisée.');
        }

        $now = new \DateTimeImmutable();
        $card->setStatus($targetStatus);

        match ($targetStatus) {
            'initialized' => $card->setInitializedAt($now),
            'ready_for_collection' => $card->setConfiguredAt($now),
            'active' => $card->setActivatedAt($now),
            default => null,
        };

        // A card order has one operational source of truth: its linked card.
        // Keeping the order status in sync lets staff follow the pickup journey from either view.
        $card->getSourceOrder()?->setStatus($targetStatus);

        $this->entityManager->flush();
    }

    public function confirmCollection(ConnectedCard $card): void
    {
        if ($card->getStatus() !== 'active') {
            throw new \DomainException('La carte doit être activée avant sa remise au client.');
        }

        if ($card->getCollectedAt() !== null) {
            throw new \DomainException('La remise de cette carte a déjà été confirmée.');
        }

        $card->setCollectedAt(new \DateTimeImmutable());
        // The order is fulfilled, while the card itself remains active.
        $card->getSourceOrder()?->setStatus('collected');

        $this->entityManager->flush();
    }
}
