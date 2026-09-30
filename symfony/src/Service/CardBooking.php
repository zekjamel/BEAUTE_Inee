<?php

namespace App\Service;

use App\Entity\ConnectedCard;
use App\Entity\CustomerOrder;
use Doctrine\ORM\EntityManagerInterface;

final class CardBooking
{
    public const URL = 'https://www.sumupbookings.com/beaute-inee';

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getUrl(): string
    {
        return self::URL;
    }

    public function forCard(ConnectedCard $card): bool
    {
        $order = $card->getSourceOrder();

        return $order !== null && $order->getPaidAt() !== null
            && !in_array($order->getStatus(), ['cancelled', 'canceled', 'refunded', 'failed'], true)
            && in_array($card->getStatus(), ['ordered', 'configuration_in_progress', 'ready_for_collection'], true)
            && $card->getActivatedAt() === null && $card->getCollectedAt() === null
            && $card->getInitializedAt() === null;
    }

    public function forOrder(?CustomerOrder $order): bool
    {
        if ($order === null || $order->getPaidAt() === null) {
            return false;
        }
        foreach ($this->entityManager->getRepository(ConnectedCard::class)->findBy(['sourceOrder' => $order]) as $card) {
            if ($this->forCard($card)) {
                return true;
            }
        }

        return false;
    }
}
