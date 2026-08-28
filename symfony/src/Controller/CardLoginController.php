<?php

namespace App\Controller;

use App\Entity\ConnectedCard;
use App\Entity\User;
use App\Exception\QuardlockApiException;
use App\Service\QuardlockClientApiRelay;
use App\Service\QuardlockServerApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/login/carte')]
final class CardLoginController extends AbstractController
{
    private const SESSION_KEY = 'quardlock_card_login';
    private const SESSION_TTL_SECONDS = 300;

    #[Route('/session', name: 'account_card_login_initialize', methods: ['POST'])]
    public function initialize(
        Request $request,
        QuardlockServerApiClient $quardlock,
        LoggerInterface $logger,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('card_login', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->error('La demande de connexion a expiré. Rechargez la page.', Response::HTTP_FORBIDDEN);
        }

        $this->revokePreviousSession($request, $quardlock);

        try {
            $clientApiToken = $quardlock->initializeLoginClientSession();
        } catch (QuardlockApiException $exception) {
            $logger->warning('Quardlock card login session initialization failed.', [
                'endpoint' => $exception->getEndpoint(),
                'http_status' => $exception->getHttpStatus(),
            ]);

            return $this->error('Le service de connexion par carte est temporairement indisponible.', Response::HTTP_BAD_GATEWAY);
        }

        $handle = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $request->getSession()->set(self::SESSION_KEY, [
            'handle' => $handle,
            'token' => $clientApiToken,
            'webAuthnSessionId' => null,
            'expiresAt' => time() + self::SESSION_TTL_SECONDS,
        ]);

        return new JsonResponse(['handle' => $handle], headers: ['Cache-Control' => 'no-store']);
    }

    #[Route('/client/{operation}', name: 'account_card_login_client_api', methods: ['GET'], requirements: ['operation' => 'GetChallenge'])]
    public function clientApi(
        string $operation,
        Request $request,
        QuardlockClientApiRelay $relay,
    ): Response {
        $session = $this->validSession($request);
        $handle = (string) $request->headers->get('ClientApiToken', '');
        if ($session === null || $handle === '' || !hash_equals($session['handle'], $handle)) {
            return new Response('Session de connexion invalide ou expirée.', Response::HTTP_FORBIDDEN, ['Cache-Control' => 'no-store']);
        }

        try {
            $result = $relay->forward(
                $operation,
                $session['token'],
                $request,
                is_string($session['webAuthnSessionId']) ? $session['webAuthnSessionId'] : null,
            );

            if ($operation === 'GetChallenge' && is_string($result['webAuthnSessionId']) && $result['webAuthnSessionId'] !== '') {
                $session['webAuthnSessionId'] = $result['webAuthnSessionId'];
                $request->getSession()->set(self::SESSION_KEY, $session);
            }

            return new Response($result['content'], $result['status'], [
                'Content-Type' => $result['contentType'],
                'Cache-Control' => 'no-store',
            ]);
        } catch (\InvalidArgumentException) {
            return new Response('Opération Quardlock non autorisée.', Response::HTTP_NOT_FOUND, ['Cache-Control' => 'no-store']);
        } catch (QuardlockApiException) {
            return new Response('Le service Quardlock est temporairement indisponible.', Response::HTTP_BAD_GATEWAY, ['Cache-Control' => 'no-store']);
        }
    }

    #[Route('/verification', name: 'account_card_login_verify', methods: ['POST'])]
    public function verify(
        Request $request,
        EntityManagerInterface $entityManager,
        QuardlockServerApiClient $quardlock,
        Security $security,
        LoggerInterface $logger,
    ): JsonResponse {
        $session = null;

        if (!$this->isCsrfTokenValid('card_login', (string) $request->headers->get('X-CSRF-Token'))) {
            return $this->error('La demande de connexion a expiré. Rechargez la page.', Response::HTTP_FORBIDDEN);
        }

        $session = $this->validSession($request);
        $handle = (string) $request->headers->get('ClientApiToken', '');
        if ($session === null || $handle === '' || !hash_equals($session['handle'], $handle)) {
            return $this->error('La session de connexion a expiré. Recommencez.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $request->toArray();
            if (!$this->validAssertionPayload($payload, $request)) {
                return $this->error('La preuve de la carte est invalide.', Response::HTTP_BAD_REQUEST);
            }

            $serialNumber = $this->serialNumberFromUserHandle($payload['UserHandle']);
            if ($serialNumber === null) {
                return $this->error('Cette carte doit être ré-enrôlée pour permettre la connexion NFC sans numéro.', Response::HTTP_BAD_REQUEST);
            }

            $card = $entityManager->getRepository(ConnectedCard::class)->findOneBy(['quardlockTokenSerialNumber' => $serialNumber]);
            if (!$this->isEligibleCard($card)) {
                return $this->error('Cette carte ne permet plus la connexion.', Response::HTTP_FORBIDDEN);
            }

            $webAuthnSessionId = $session['webAuthnSessionId'];
            if (!is_string($webAuthnSessionId) || $webAuthnSessionId === '') {
                return $this->error('Le défi de connexion est incomplet. Recommencez.', Response::HTTP_BAD_REQUEST);
            }

            $authenticated = $quardlock->authenticateWebAuthnToken(
                $serialNumber,
                $webAuthnSessionId,
                $payload['AuthenticatorData64Encoded'],
                $payload['ClientDataBase64Encoded'],
                $payload['Id'],
                $payload['Signature'],
                $payload['Type'],
                $request->getHost(),
                $request->getClientIp(),
            );
            if (!$authenticated) {
                return $this->error('La carte n’a pas confirmé votre identité.', Response::HTTP_UNAUTHORIZED);
            }

            $user = $card->getCustomer()?->getUser();
            if (!$user instanceof User || !$user->isActive()) {
                return $this->error('Le compte associé à cette carte n’est pas actif.', Response::HTTP_FORBIDDEN);
            }

            $security->login($user, 'form_login', 'main');

            return new JsonResponse([
                'success' => true,
                'redirectUrl' => $this->generateUrl('account_dashboard'),
            ], headers: ['Cache-Control' => 'no-store']);
        } catch (QuardlockApiException $exception) {
            $logger->warning('Quardlock card login verification failed.', [
                'endpoint' => $exception->getEndpoint(),
                'http_status' => $exception->getHttpStatus(),
            ]);

            return $this->error('Le service de connexion par carte est temporairement indisponible.', Response::HTTP_BAD_GATEWAY);
        } catch (\JsonException) {
            return $this->error('La demande de connexion est invalide.', Response::HTTP_BAD_REQUEST);
        } finally {
            $request->getSession()->remove(self::SESSION_KEY);
            if (is_array($session) && is_string($session['token'] ?? null) && $session['token'] !== '') {
                try {
                    $quardlock->revokeClientSession($session['token']);
                } catch (QuardlockApiException) {
                    // The short-lived Quardlock session will expire server-side.
                }
            }
        }
    }

    /** @param array<string, mixed> $payload */
    private function validAssertionPayload(array $payload, Request $request): bool
    {
        foreach (['Id', 'Type', 'ClientDataBase64Encoded', 'AuthenticatorData64Encoded', 'Signature', 'UserHandle'] as $key) {
            if (!is_string($payload[$key] ?? null) || $payload[$key] === '' || strlen($payload[$key]) > 16384) {
                return false;
            }
        }
        if ($payload['Type'] !== 'public-key' || strlen($payload['Id']) > 2048) {
            return false;
        }

        $clientDataJson = base64_decode($payload['ClientDataBase64Encoded'], true);
        $clientData = is_string($clientDataJson) ? json_decode($clientDataJson, true) : null;
        if (!is_array($clientData) || ($clientData['type'] ?? null) !== 'webauthn.get') {
            return false;
        }

        $origin = is_string($clientData['origin'] ?? null) ? rtrim($clientData['origin'], '/') : '';

        return $origin !== '' && hash_equals(rtrim($request->getSchemeAndHttpHost(), '/'), $origin);
    }

    private function serialNumberFromUserHandle(string $userHandle): ?string
    {
        $serialNumber = base64_decode($userHandle, true);
        if (!is_string($serialNumber)) {
            return null;
        }

        $serialNumber = trim($serialNumber);

        return $serialNumber !== '' && mb_strlen($serialNumber) <= 120 ? $serialNumber : null;
    }

    /** @return array{handle: string, token: string, webAuthnSessionId: ?string, expiresAt: int}|null */
    private function validSession(Request $request): ?array
    {
        $session = $request->getSession()->get(self::SESSION_KEY);
        if (!is_array($session)
            || !is_string($session['handle'] ?? null) || $session['handle'] === ''
            || !is_string($session['token'] ?? null) || $session['token'] === ''
            || !is_int($session['expiresAt'] ?? null) || $session['expiresAt'] < time()) {
            return null;
        }

        return $session;
    }

    private function isEligibleCard(mixed $card): bool
    {
        return $card instanceof ConnectedCard
            && $card->getStatus() === 'active'
            && $card->getCollectedAt() !== null
            && $card->getQuardlockEnrollmentStatus() === 'enrolled'
            && is_string($card->getQuardlockTokenSerialNumber())
            && $card->getQuardlockTokenSerialNumber() !== ''
            && $card->getCustomer()?->getUser() instanceof User;
    }

    private function revokePreviousSession(Request $request, QuardlockServerApiClient $quardlock): void
    {
        $previous = $request->getSession()->get(self::SESSION_KEY);
        $request->getSession()->remove(self::SESSION_KEY);
        if (!is_array($previous) || !is_string($previous['token'] ?? null) || $previous['token'] === '') {
            return;
        }

        try {
            $quardlock->revokeClientSession($previous['token']);
        } catch (QuardlockApiException) {
            // A stale short-lived session must not prevent a new login attempt.
        }
    }

    private function error(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['success' => false, 'message' => $message], $status, ['Cache-Control' => 'no-store']);
    }
}
