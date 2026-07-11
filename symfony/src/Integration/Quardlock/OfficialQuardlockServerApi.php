<?php

namespace App\Integration\Quardlock;

use App\Exception\QuardlockApiException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// The two files below are distributed by Quardlock as part of their PHP Server API library.
require_once dirname(__DIR__, 3) . '/lib/Quardlock/ServerApi/TeslaServerApiBase.php';
require_once dirname(__DIR__, 3) . '/lib/Quardlock/ServerApi/QuardlockServerApi.php';

/**
 * Uses Quardlock's official public API methods while replacing its raw cURL
 * transport (which echoes diagnostics and calls exit()) with Symfony's safe,
 * observable HTTP transport.
 */
final class OfficialQuardlockServerApi extends \Quardlock\library\ServerApi
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
        // Certificates are introduced in a second phase. Empty values preserve
        // Quardlock's API-key-only mode and disable the library's debug output.
        parent::__construct('', '', '', '', showDebug: false);
    }

    /**
     * @param list<string> $headers
     * @param array<string, mixed>|false $data
     */
    protected function CallAPI(string $method, string $url, array $headers, $data = false)
    {
        $endpoint = basename((string) parse_url($url, PHP_URL_PATH));
        $options = [
            'headers' => $headers,
            'timeout' => 15,
        ];

        if (in_array($method, ['GET', 'DELETE'], true)) {
            $options['query'] = $data === false ? [] : $data;
        } elseif ($data !== false) {
            $options['json'] = $data;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $content = trim($response->getContent(false));
        } catch (TransportExceptionInterface $exception) {
            throw new QuardlockApiException(
                'Impossible de joindre Quardlock. Vérifiez votre connexion réseau et l’URL de l’API.',
                $endpoint,
                previous: $exception,
            );
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new QuardlockApiException(
                match ($statusCode) {
                    401 => 'Quardlock a refusé la requête. Vérifiez la clé API et l’adresse IP de confiance configurée dans Quardlock.',
                    403 => 'Quardlock a refusé cette action ou la limite de licence a été atteinte.',
                    429 => 'La limite de requêtes Quardlock a été atteinte. Réessayez plus tard.',
                    503 => 'Quardlock est temporairement indisponible. Réessayez dans quelques minutes.',
                    default => 'Quardlock a renvoyé une erreur HTTP ' . $statusCode . '.',
                },
                $endpoint,
                $statusCode,
            );
        }

        try {
            $payload = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new QuardlockApiException('Quardlock a renvoyé une réponse non exploitable.', $endpoint, $statusCode);
        }

        if (!is_array($payload) || !array_key_exists('result', $payload)) {
            throw new QuardlockApiException('Quardlock a renvoyé une réponse incomplète.', $endpoint, $statusCode);
        }

        return $payload['result'];
    }
}
