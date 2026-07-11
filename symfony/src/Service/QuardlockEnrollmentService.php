<?php

namespace App\Service;

use App\Entity\ConnectedCard;
use App\Exception\QuardlockApiException;
use Doctrine\ORM\EntityManagerInterface;

final class QuardlockEnrollmentService
{
    private const ENROLLMENT_TTL_SECONDS = 900;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuardlockServerApiClient $quardlock,
    ) {
    }

    /** @return non-empty-string */
    public function start(ConnectedCard $card): string
    {
        $this->assertEligible($card);

        $nonce = bin2hex(random_bytes(32));
        $now = new \DateTimeImmutable();
        $card
            ->setQuardlockEnrollmentStatus('pending')
            ->setQuardlockEnrollmentStartedAt($now)
            ->setQuardlockEnrollmentExpiresAt($now->modify('+' . self::ENROLLMENT_TTL_SECONDS . ' seconds'))
            ->setQuardlockEnrollmentNonceHash(hash('sha256', $nonce));

        $this->entityManager->flush();

        return $nonce;
    }

    /** @return non-empty-string */
    public function issueClientSession(ConnectedCard $card, string $nonce): string
    {
        $this->assertValidNonce($card, $nonce);
        $sessionToken = $this->quardlock->initializeClientSession();
        $card->setQuardlockEnrollmentStatus('session_issued');
        $this->entityManager->flush();

        return $sessionToken;
    }

    public function complete(ConnectedCard $card, string $nonce, string $quardlockTokenSerialNumber): void
    {
        $this->assertValidNonce($card, $nonce);
        $serialNumber = trim($quardlockTokenSerialNumber);

        if ($serialNumber === '' || mb_strlen($serialNumber) > 120) {
            throw new \DomainException('Le numéro de jeton retourné par Quardlock est invalide.');
        }

        $isLocked = $this->quardlock->isTokenLocked($serialNumber);

        if ($isLocked === null) {
            throw new QuardlockApiException('Quardlock ne reconnaît pas le jeton qui vient d’être enrôlé.', 'IsTokenLocked');
        }

        $now = new \DateTimeImmutable();
        $card
            ->setQuardlockTokenSerialNumber($serialNumber)
            ->setQuardlockEnrollmentStatus($isLocked ? 'enrolled_locked' : 'enrolled')
            ->setQuardlockEnrolledAt($now)
            ->setQuardlockEnrollmentNonceHash(null)
            ->setQuardlockEnrollmentExpiresAt(null)
            ->setStatus('initialized')
            ->setInitializedAt($now);
        $card->getSourceOrder()?->setStatus('initialized');

        $this->entityManager->flush();
    }

    public function hasValidEnrollment(ConnectedCard $card, ?string $nonce): bool
    {
        if ($nonce === null || $nonce === '') {
            return false;
        }

        try {
            $this->assertValidNonce($card, $nonce);

            return true;
        } catch (\DomainException) {
            return false;
        }
    }

    private function assertEligible(ConnectedCard $card): void
    {
        if (!in_array($card->getStatus(), ['ready_for_collection', 'collected'], true)) {
            throw new \DomainException('La carte doit être préparée et prête pour le rendez-vous client avant son enrôlement.');
        }

        if ($card->getCustomer() === null) {
            throw new \DomainException('La carte doit être associée à un client avant son enrôlement.');
        }

        if ($card->getCardLabIdentifier() === null || $card->getCardLabIdentifier() === '') {
            throw new \DomainException('Saisissez d’abord l’identifiant physique CardLab de la carte.');
        }
    }

    private function assertValidNonce(ConnectedCard $card, string $nonce): void
    {
        $this->assertEligible($card);

        if ($card->getQuardlockEnrollmentExpiresAt() === null || $card->getQuardlockEnrollmentExpiresAt() < new \DateTimeImmutable()) {
            throw new \DomainException('La session d’enrôlement a expiré. Relancez-la depuis la fiche carte.');
        }

        $hash = $card->getQuardlockEnrollmentNonceHash();
        if ($hash === null || !hash_equals($hash, hash('sha256', $nonce))) {
            throw new \DomainException('La session d’enrôlement n’est plus valide. Relancez-la depuis la fiche carte.');
        }
    }
}
