<?php

namespace App\Service;

use App\Exception\QuardlockApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Same-origin relay for the browser-facing Quardlock Client API.
 *
 * Quardlock's browser library is kept for WebAuthn, while its network calls
 * transit through Symfony. This avoids browser CORS restrictions and keeps the
 * actual Client API token on the server.
 */
final class QuardlockClientApiRelay
{
    /** @var array<string, list<string>> */
    private const OPERATIONS = [
        'GetPrecomputedTokenSerialNumber' => ['GET'],
        'GetChallenge' => ['GET'],
        'RegisterToken' => ['POST'],
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'QUARDLOCK_CLIENT_API_BASE_URL')]
        private readonly string $clientApiBaseUrl,
    ) {
    }

    /**
     * @return array{status: int, content: string, contentType: string, webAuthnSessionId: ?string}
     */
    public function forward(
        string $operation,
        string $clientApiToken,
        Request $request,
        ?string $webAuthnSessionId = null,
        ?string $precomputedTokenSerialNumber = null,
    ): array
    {
        if (!isset(self::OPERATIONS[$operation]) || !in_array($request->getMethod(), self::OPERATIONS[$operation], true)) {
            throw new \InvalidArgumentException('Opération Client API Quardlock non autorisée.');
        }

        $url = rtrim($this->clientApiBaseUrl, '/') . '/' . $operation;
        $query = $request->query->all();

        // The official browser library passes this value through an Ajax option
        // which is not consistently serialized as a URL query parameter. The
        // value is therefore kept in the short-lived Symfony enrollment session
        // after it is issued by Quardlock and is attached here only for the final
        // registration request.
        if ($operation === 'RegisterToken') {
            unset($query['precomputedTokenSerialNumber']);

            if ($precomputedTokenSerialNumber !== null && $precomputedTokenSerialNumber !== '') {
                $query['precomputedTokenSerialNumber'] = $precomputedTokenSerialNumber;
            }
        }
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = [
            'ClientApiToken' => $clientApiToken,
            'Accept' => (string) $request->headers->get('Accept', '*/*'),
        ];

        if ($operation === 'RegisterToken') {
            $headers['Content-Type'] = 'application/json';
            if ($webAuthnSessionId !== null && $webAuthnSessionId !== '') {
                $headers['WebAuthnSessionId'] = $webAuthnSessionId;
            }
        }

        try {
            $response = $this->httpClient->request($request->getMethod(), $url, [
                'headers' => $headers,
                'body' => $request->getContent(),
                'timeout' => 30,
            ]);
            $status = $response->getStatusCode();
            $responseHeaders = $response->getHeaders(false);

            return [
                'status' => $status,
                'content' => $response->getContent(false),
                'contentType' => $responseHeaders['content-type'][0] ?? 'application/octet-stream',
                'webAuthnSessionId' => $responseHeaders['webauthnsessionid'][0] ?? null,
            ];
        } catch (TransportExceptionInterface $exception) {
            throw new QuardlockApiException(
                'Symfony ne parvient pas à joindre l’API Client Quardlock.',
                $operation,
                previous: $exception,
            );
        }
    }
}
