<?php

namespace App\Service;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Exception\QuardlockApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/** Card identification grants a bounded visit, never a customer authentication token. */
final class CardVisitService
{
    private const PENDING = 'quardlock_card_visit_pending';
    private const VISIT = 'quardlock_card_visit';

    public function __construct(
        private readonly QuardlockServerApiClient $quardlock,
        private readonly QuardlockClientApiRelay $relay,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function start(Request $request, string $operator): string
    {
        $this->finish($request);
        $token = $this->quardlock->initializeLoginClientSession();
        $handle = bin2hex(random_bytes(32));
        $request->getSession()->set(self::PENDING, [
            'operator' => $operator, 'token' => $token, 'handle' => $handle,
            'expiresAt' => time() + 300, 'webAuthnSessionId' => null, 'challenge' => null,
        ]);

        return $handle;
    }

    public function challenge(Request $request, string $operator): string
    {
        $pending = $this->pending($request, $operator);
        try {
            // Only a single challenge is accepted per identification attempt.
            if ($pending['challenge'] !== null) {
                throw new BadRequestHttpException('Recommencez l’identification.');
            }
            $result = $this->relay->forward('GetChallenge', $pending['token'], $request);
            if ($result['status'] !== 200 || $result['content'] === '' || !$result['webAuthnSessionId']) {
                throw new QuardlockApiException('Défi indisponible.', 'GetChallenge');
            }
            $pending['challenge'] = rtrim(strtr(base64_encode($result['content']), '+/', '-_'), '=');
            $pending['webAuthnSessionId'] = $result['webAuthnSessionId'];
            $request->getSession()->set(self::PENDING, $pending);

            return $result['content'];
        } catch (\Throwable $exception) {
            $this->finish($request);
            throw $exception;
        }
    }

    public function verify(Request $request, string $operator): void
    {
        $pending = $this->pending($request, $operator);
        // Consume before verification: a rejected or replayed proof cannot open a visit.
        $request->getSession()->remove(self::PENDING);
        $request->getSession()->remove(self::VISIT);
        try {
            $payload = $request->toArray();
            foreach (['Id', 'Type', 'ClientDataBase64Encoded', 'AuthenticatorData64Encoded', 'Signature', 'UserHandle'] as $key) {
                if (!is_string($payload[$key] ?? null) || $payload[$key] === '' || strlen($payload[$key]) > 16384) {
                    throw new BadRequestHttpException('La preuve de la carte est invalide.');
                }
            }
            $clientData = json_decode(base64_decode($payload['ClientDataBase64Encoded'], true) ?: '', true);
            $authenticatorData = base64_decode($payload['AuthenticatorData64Encoded'], true);
            $serial = base64_decode($payload['UserHandle'], true);
            if ($payload['Type'] !== 'public-key' || strlen($payload['Id']) > 2048
                || !is_array($clientData) || ($clientData['type'] ?? null) !== 'webauthn.get'
                || ($clientData['origin'] ?? null) !== $request->getSchemeAndHttpHost()
                || ($clientData['crossOrigin'] ?? false) !== false
                || !is_string($pending['challenge']) || ($clientData['challenge'] ?? null) !== $pending['challenge']
                || !is_string($pending['webAuthnSessionId']) || $pending['webAuthnSessionId'] === ''
                || !is_string($authenticatorData) || strlen($authenticatorData) < 37
                || (ord($authenticatorData[32]) & 5) !== 5
                || !is_string($serial) || trim($serial) === '' || mb_strlen($serial) > 120) {
                throw new BadRequestHttpException('La preuve de la carte est invalide.');
            }
            $card = $this->entityManager->getRepository(ConnectedCard::class)->findOneBy(['quardlockTokenSerialNumber' => trim($serial)]);
            if (!$this->eligible($card)) {
                throw new AccessDeniedHttpException('Cette carte ne permet pas l’identification.');
            }
            if (!$this->quardlock->authenticateWebAuthnToken(
                trim($serial), $pending['webAuthnSessionId'], $payload['AuthenticatorData64Encoded'],
                $payload['ClientDataBase64Encoded'], $payload['Id'], $payload['Signature'], $payload['Type'],
                $request->getHost(), $request->getClientIp(),
            )) {
                throw new AccessDeniedHttpException('La carte n’a pas confirmé l’identité.');
            }
            $request->getSession()->set(self::VISIT, [
                'operator' => $operator, 'customerId' => $card->getCustomer()->getId(),
                'cardId' => $card->getId(), 'expiresAt' => time() + 900,
            ]);
        } finally {
            $this->revoke($pending['token']);
        }
    }

    public function customer(Request $request, string $operator): ?Customer
    {
        $visit = $request->getSession()->get(self::VISIT);
        if (!is_array($visit) || ($visit['operator'] ?? null) !== $operator || ($visit['expiresAt'] ?? 0) <= time()) {
            $request->getSession()->remove(self::VISIT);
            return null;
        }
        $card = $this->entityManager->find(ConnectedCard::class, $visit['cardId']);
        if (!$this->eligible($card) || $card->getCustomer()->getId() !== $visit['customerId']) {
            $request->getSession()->remove(self::VISIT);
            return null;
        }

        return $card->getCustomer();
    }

    public function remainingSeconds(Request $request): int
    {
        return max(0, (int) ($request->getSession()->get(self::VISIT)['expiresAt'] ?? 0) - time());
    }

    public function finish(Request $request): void
    {
        $pending = $request->getSession()->remove(self::PENDING);
        $request->getSession()->remove(self::VISIT);
        if (is_array($pending) && is_string($pending['token'] ?? null)) {
            $this->revoke($pending['token']);
        }
    }

    private function pending(Request $request, string $operator): array
    {
        $pending = $request->getSession()->get(self::PENDING);
        if (!is_array($pending) || ($pending['operator'] ?? null) !== $operator || ($pending['expiresAt'] ?? 0) <= time()) {
            $this->finish($request);
            throw new AccessDeniedHttpException('La session d’identification a expiré. Recommencez.');
        }
        if (!hash_equals($pending['handle'], (string) $request->headers->get('ClientApiToken', ''))) {
            throw new AccessDeniedHttpException('Session d’identification invalide.');
        }

        return $pending;
    }

    private function eligible(mixed $card): bool
    {
        return $card instanceof ConnectedCard && $card->getCustomer() instanceof Customer
            && $card->getStatus() === 'active' && $card->getCollectedAt() !== null
            && $card->getQuardlockEnrollmentStatus() === 'enrolled'
            && is_string($card->getQuardlockTokenSerialNumber()) && $card->getQuardlockTokenSerialNumber() !== '';
    }

    private function revoke(string $token): void
    {
        try {
            $this->quardlock->revokeClientSession($token);
        } catch (QuardlockApiException) {
            // Quardlock's short-lived session expires remotely; local access is already removed.
        }
    }
}
