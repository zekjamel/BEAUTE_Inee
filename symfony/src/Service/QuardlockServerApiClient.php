<?php

namespace App\Service;

use App\Exception\QuardlockApiException;
use App\Integration\Quardlock\OfficialQuardlockServerApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Trusted-server adapter for Quardlock.
 *
 * The server API key stays exclusively here, on Symfony. Browser code may only
 * receive short-lived client session identifiers created by this service.
 */
final class QuardlockServerApiClient
{
    public function __construct(
        private readonly OfficialQuardlockServerApi $serverApi,
        #[Autowire(env: 'QUARDLOCK_CLIENT_API_BASE_URL')]
        private readonly string $clientApiBaseUrl,
        #[Autowire(env: 'QUARDLOCK_API_KEY')]
        private readonly string $apiKey,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isConfigured(): bool
    {
        return trim($this->apiKey) !== '';
    }

    /**
     * Performs a safe, short-lived connection check: a Client API session is
     * issued then immediately revoked. The identifier is never rendered or
     * persisted locally.
     */
    public function verifyConnection(): void
    {
        try {
            $sessionTokenId = $this->initializeClientSession();
            $this->serverApi->RevokeApiClientSessionToken($this->apiKey, $sessionTokenId);
        } catch (QuardlockApiException $exception) {
            $this->logger->warning('Quardlock Server API rejected a request.', [
                'endpoint' => $exception->getEndpoint(),
                'http_status' => $exception->getHttpStatus(),
            ]);

            throw $exception;
        }
    }

    /**
     * Validates the first Client API call needed before a browser can trigger
     * WebAuthn. The client token is never displayed, stored, or logged.
     */
    public function verifyEnrollmentPreparation(): void
    {
        $sessionTokenId = null;

        try {
            $sessionTokenId = $this->initializeClientSession();
            $response = $this->httpClient->request('GET', rtrim($this->clientApiBaseUrl, '/') . '/GetPrecomputedTokenSerialNumber', [
                'headers' => [
                    'ClientApiToken' => $sessionTokenId,
                    'Origin' => 'https://beaute-inee.wip',
                ],
                'timeout' => 15,
            ]);
            $statusCode = $response->getStatusCode();
            $headers = $response->getHeaders(false);
            $allowedOrigin = $headers['access-control-allow-origin'][0] ?? null;

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new QuardlockApiException(
                    'Quardlock n’a pas accepté le jeton Client API pour préparer l’enrôlement.',
                    'GetPrecomputedTokenSerialNumber',
                    $statusCode,
                );
            }

            if (!in_array($allowedOrigin, ['*', 'https://beaute-inee.wip'], true)) {
                throw new QuardlockApiException(
                    'Quardlock a répondu, mais n’autorise pas le navigateur local à lire cette réponse.',
                    'GetPrecomputedTokenSerialNumber',
                    $statusCode,
                );
            }
        } catch (TransportExceptionInterface $exception) {
            throw new QuardlockApiException(
                'Impossible de joindre l’API Client Quardlock depuis le serveur local.',
                'GetPrecomputedTokenSerialNumber',
                previous: $exception,
            );
        } finally {
            if (is_string($sessionTokenId) && $sessionTokenId !== '') {
                try {
                    $this->serverApi->RevokeApiClientSessionToken($this->apiKey, $sessionTokenId);
                } catch (QuardlockApiException $exception) {
                    $this->logger->warning('Quardlock client session revocation failed after enrollment preparation check.', [
                        'endpoint' => $exception->getEndpoint(),
                        'http_status' => $exception->getHttpStatus(),
                    ]);
                }
            }
        }
    }

    /**
     * @return non-empty-string
     */
    public function initializeClientSession(?string $clientIp = null): string
    {
        $sessionTokenId = $this->serverApi->InitializeApiClientSession(
            $this->apiKey,
            $this->clientEndpoints(),
            $clientIp,
        );

        if (!is_string($sessionTokenId) || trim($sessionTokenId) === '') {
            throw new QuardlockApiException('Quardlock a répondu sans identifiant de session client exploitable.', 'InitializeApiClientSession');
        }

        return $sessionTokenId;
    }

    public function isTokenLocked(string $serialNumber): ?bool
    {
        if (!$this->isConfigured()) {
            throw new QuardlockApiException('La clé API Quardlock n’est pas configurée dans .env.local.', 'IsTokenLocked');
        }

        try {
            $result = $this->serverApi->IsTokenLocked($this->apiKey, $serialNumber);
        } catch (QuardlockApiException $exception) {
            $this->logger->warning('Quardlock token validation failed.', [
                'endpoint' => $exception->getEndpoint(),
                'http_status' => $exception->getHttpStatus(),
            ]);

            throw $exception;
        }

        if (is_bool($result)) {
            return $result;
        }

        if (is_string($result)) {
            return match (strtolower(trim($result))) {
                'true', '1' => true,
                'false', '0' => false,
                default => null,
            };
        }

        return null;
    }

    /** @return list<string> */
    private function clientEndpoints(): array
    {
        $baseUrl = rtrim($this->clientApiBaseUrl, '/');

        return [
            $baseUrl . '/GetChallenge',
            $baseUrl . '/GetClientApiSessionName',
            $baseUrl . '/GetWebAuthnTokenInfo',
            $baseUrl . '/GetPiggybackTokenInfo',
            $baseUrl . '/GetPrecomputedTokenSerialNumber',
            $baseUrl . '/RegisterToken',
        ];
    }

}
