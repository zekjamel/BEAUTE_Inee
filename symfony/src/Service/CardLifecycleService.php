<?php

namespace App\Service;

use App\Entity\ConnectedCard;
use Doctrine\ORM\EntityManagerInterface;

final class CardLifecycleService
{
    /** @var array<string, array<string, string>> */
    private const TRANSITIONS = [
        'ordered' => ['ready_for_collection' => 'Marquer prête au retrait'],
        'ready_for_collection' => ['collected' => 'Confirmer la remise au client'],
        'collected' => ['initialized' => 'Initialiser la carte'],
        'initialized' => ['configured' => 'Valider la configuration'],
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
            'collected' => $card->setCollectedAt($now),
            'initialized' => $card->setInitializedAt($now),
            'configured' => $card->setConfiguredAt($now),
            'active' => $card->setActivatedAt($now),
            default => null,
        };

        // A card order has one operational source of truth: its linked card.
        // Keeping the order status in sync lets staff follow the pickup journey from either view.
        $card->getSourceOrder()?->setStatus($targetStatus);

        $this->entityManager->flush();
    }
}
