<?php

namespace App\Service;

use App\Entity\QuardlockAuditLog;
use App\Entity\ConnectedCard;
use App\Entity\User;
use App\Exception\QuardlockApiException;
use Doctrine\ORM\EntityManagerInterface;

final class QuardlockAuditService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function logConnectionCheck(?User $actor, ?QuardlockApiException $failure = null): void
    {
        $log = (new QuardlockAuditLog())
            ->setAction('connection_check')
            ->setStatus($failure === null ? 'success' : 'failed')
            ->setInitiatedBy($actor);

        if ($failure === null) {
            $log
                ->setEndpoint('InitializeApiClientSession → RevokeApiClientSessionToken')
                ->setHttpStatus(204)
                ->setMessage('Session Client API de test créée puis révoquée.');
        } else {
            $log
                ->setEndpoint($failure->getEndpoint())
                ->setHttpStatus($failure->getHttpStatus())
                ->setMessage($failure->getMessage());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function logCardEvent(
        ConnectedCard $card,
        ?User $actor,
        string $action,
        string $status,
        string $message,
        ?string $endpoint = null,
        ?int $httpStatus = null,
    ): void {
        $log = (new QuardlockAuditLog())
            ->setAction($action)
            ->setStatus($status)
            ->setMessage($message)
            ->setEndpoint($endpoint)
            ->setHttpStatus($httpStatus)
            ->setConnectedCard($card)
            ->setInitiatedBy($actor);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
