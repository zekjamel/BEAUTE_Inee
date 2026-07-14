<?php
/**
 * Endpoint de logging des consentements cookies.
 * Reçoit les données via POST (FormData / sendBeacon).
 * Écrit une ligne JSONL dans consent_logs/consent.jsonl.
 *
 * Données stockées : timestamp, IP anonymisée, user-agent tronqué,
 * version du consent, action, catégories acceptées.
 */

header('Content-Type: application/json');

/* Contrôler l'Origin : n'autoriser que le même hôte */
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
if ($origin) {
    $parts = parse_url($origin);
    $origin_host = $parts['host'] ?? '';
    if ($origin_host === $host) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
}

/* ── Sécurité : POST uniquement ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

/* ── Validation des entrées (support JSON ou form-data) ── */
$rawData = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $rawData = $json;
    }
} else {
    $rawData = $_POST;
}

$version = isset($rawData['version']) ? preg_replace('/[^0-9.]/', '', (string)$rawData['version']) : 'unknown';
$timestamp = isset($rawData['timestamp']) ? (string)$rawData['timestamp'] : '';
$action = isset($rawData['action']) ? preg_replace('/[^a-z_]/', '', (string)$rawData['action']) : 'unknown';
$categories = $rawData['categories'] ?? [];

/* Normaliser timestamp ISO8601, fallback maintenant */
try {
    if ($timestamp !== '') {
        $dt = new DateTime($timestamp);
        $timestamp = $dt->format(DateTime::ATOM);
    } else {
        $timestamp = (new DateTime())->format(DateTime::ATOM);
    }
} catch (Exception $e) {
    $timestamp = (new DateTime())->format(DateTime::ATOM);
}

/* Valider / nettoyer catégories — ne garder que booléens pour clés autorisées */
$allowed = ['necessary', 'preferences', 'analytics', 'marketing'];
$cats = [];
if (is_string($categories)) {
    $decoded = json_decode($categories, true);
    if (is_array($decoded)) {
        $categories = $decoded;
    } else {
        $categories = [];
    }
}
if (is_array($categories)) {
    foreach ($allowed as $k) {
        $cats[$k] = isset($categories[$k]) ? (bool)$categories[$k] : false;
    }
} else {
    foreach ($allowed as $k) { $cats[$k] = false; }
}

/* ── Anonymisation de l'IP ── */
/* ── Anonymisation de l'IP ── */
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    /* IPv4 : masquer le dernier octet */
    $ip_anon = preg_replace('/\.\d+$/', '.0', $ip);
} elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    /* IPv6 : ne garder que les 4 premiers groupes */
    $parts = explode(':', $ip);
    $ip_anon = implode(':', array_slice($parts, 0, 4)) . '::';
} else {
    $ip_anon = '0.0.0.0';
}

/* ── Construction de l'entrée ── */
$ua = mb_substr(strip_tags($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120);
$entry = json_encode([
    'ts'         => $timestamp,
    'ip'         => $ip_anon,
    'ua'         => $ua,
    'version'    => $version,
    'action'     => $action,
    'categories' => $cats,
], JSON_UNESCAPED_UNICODE);

/* ── Écriture dans le fichier JSONL ── */
$logDir  = __DIR__ . '/../consent_logs';
$logFile = $logDir . '/consent.jsonl';

/* Créer le répertoire si absent */
if (!is_dir($logDir)) {
    mkdir($logDir, 0750, true);
}

/* Rotation : si le fichier dépasse 5 Mo, archiver */
if (file_exists($logFile) && filesize($logFile) > 5 * 1024 * 1024) {
    rename($logFile, $logDir . '/consent_' . date('Ymd_His') . '.jsonl');
}

$written = file_put_contents($logFile, $entry . "\n", FILE_APPEND | LOCK_EX);
if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Log write failed']);
    exit;
}

echo json_encode(['ok' => true]);

if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Log write failed']);
    exit;
}

echo json_encode(['ok' => true]);
