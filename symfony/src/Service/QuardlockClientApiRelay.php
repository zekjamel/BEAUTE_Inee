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
        #[Autowire(service: 'quardlock.client')]
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'QUARDLOCK_CLIENT_API_BASE_URL')]
        private readonly string $clientApiBaseUrl,
        private readonly QuardlockDiagnosticLogger $diagnosticLogger,
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

        $requestBody = $request->getContent();
        $this->diagnosticLogger->log('client_api_request', [
            'operation' => $operation,
            'method' => $request->getMethod(),
            'request_query_keys' => array_keys($query),
            'request_body_bytes' => strlen($requestBody),
            'request_body_sha256' => hash('sha256', $requestBody),
            'client_api_token_present' => $clientApiToken !== '',
            'origin' => $request->headers->get('Origin'),
            'webauthn_session_id_present' => $webAuthnSessionId !== null && $webAuthnSessionId !== '',
            'webauthn_session_id_sha256' => $webAuthnSessionId !== null ? hash('sha256', $webAuthnSessionId) : null,
            'precomputed_serial_present' => $precomputedTokenSerialNumber !== null && $precomputedTokenSerialNumber !== '',
            'precomputed_serial_length' => $precomputedTokenSerialNumber !== null ? mb_strlen($precomputedTokenSerialNumber) : 0,
            'request_payload' => $this->describeRequestPayload($operation, $requestBody),
            'browser_diagnostic' => $this->decodeBrowserDiagnostic($request),
        ]);

        $headers = [
            'ClientApiToken' => $clientApiToken,
            'Accept' => (string) $request->headers->get('Accept', '*/*'),
        ];
        $origin = $request->headers->get('Origin');
        if (is_string($origin) && $origin !== '') {
            $headers['Origin'] = $origin;
        }

        if ($operation === 'RegisterToken') {
            $headers['Content-Type'] = 'application/json';
            if ($webAuthnSessionId !== null && $webAuthnSessionId !== '') {
                $headers['WebAuthnSessionId'] = $webAuthnSessionId;
            }
        }

        try {
            $response = $this->httpClient->request($request->getMethod(), $url, [
                'headers' => $headers,
                'body' => $requestBody,
                'timeout' => 30,
            ]);
            $status = $response->getStatusCode();
            $responseHeaders = $response->getHeaders(false);
            $content = $response->getContent(false);
            $this->diagnosticLogger->log('client_api_response', [
                'operation' => $operation,
                'status' => $status,
                'content_bytes' => strlen($content),
                'content_sha256' => hash('sha256', $content),
                'response_header_names' => array_keys($responseHeaders),
                'response_payload' => $this->describeResponsePayload($content),
            ]);

            return [
                'status' => $status,
                'content' => $content,
                'contentType' => $responseHeaders['content-type'][0] ?? 'application/octet-stream',
                'webAuthnSessionId' => $responseHeaders['webauthnsessionid'][0] ?? null,
            ];
        } catch (TransportExceptionInterface $exception) {
            $this->diagnosticLogger->log('client_api_transport_error', [
                'operation' => $operation,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            throw new QuardlockApiException(
                'Symfony ne parvient pas à joindre l’API Client Quardlock.',
                $operation,
                previous: $exception,
            );
        }
    }

    /** @return array<string, mixed> */
    private function describeRequestPayload(string $operation, string $body): array
    {
        if ($operation !== 'RegisterToken' || $body === '') {
            return [];
        }

        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            return ['json_valid' => false];
        }

        $description = [
            'json_valid' => true,
            'keys' => array_keys($payload),
        ];
        foreach (['Id', 'Type', 'ClientDataBase64Encoded', 'AttestationDataBase64Encoded'] as $key) {
            $value = $payload[$key] ?? null;
            $description[$key] = [
                'present' => is_string($value) && $value !== '',
                'length' => is_string($value) ? strlen($value) : 0,
                'sha256' => is_string($value) ? hash('sha256', $value) : null,
            ];

            if (in_array($key, ['Id', 'Type'], true) && is_string($value)) {
                $description[$key]['value'] = $value;
            }
        }

        $clientData = $payload['ClientDataBase64Encoded'] ?? null;
        if (is_string($clientData)) {
            $decoded = base64_decode($clientData, true);
            $clientDataJson = is_string($decoded) ? json_decode($decoded, true) : null;
            if (is_array($clientDataJson)) {
                $description['client_data'] = [
                    'type' => is_string($clientDataJson['type'] ?? null) ? $clientDataJson['type'] : null,
                    'origin' => is_string($clientDataJson['origin'] ?? null) ? $clientDataJson['origin'] : null,
                    'challenge_length' => is_string($clientDataJson['challenge'] ?? null) ? strlen($clientDataJson['challenge']) : 0,
                    'challenge_sha256' => is_string($clientDataJson['challenge'] ?? null) ? hash('sha256', $clientDataJson['challenge']) : null,
                ];
            } else {
                $description['client_data'] = ['json_valid' => false];
            }
        }

        return $description;
    }

    /** @return array<string, mixed> */
    private function describeResponsePayload(string $content): array
    {
        $payload = json_decode($content, true);

        if (!is_array($payload)) {
            return ['json_valid' => false];
        }

        return [
            'json_valid' => true,
            'keys' => array_keys($payload),
            'result' => is_bool($payload['result'] ?? null) ? $payload['result'] : null,
            'message' => is_string($payload['message'] ?? null) ? $payload['message'] : null,
        ];
    }

    /** @return array<string, mixed>|null */
    private function decodeBrowserDiagnostic(Request $request): ?array
    {
        $encoded = $request->headers->get('X-Quardlock-Diagnostic');
        if (!is_string($encoded) || $encoded === '') {
            return null;
        }

        $decoded = base64_decode($encoded, true);
        $diagnostic = is_string($decoded) ? json_decode($decoded, true) : null;

        return is_array($diagnostic) ? $diagnostic : ['valid' => false];
    }
}
