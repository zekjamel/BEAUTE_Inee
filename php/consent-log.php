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
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? htmlspecialchars($_SERVER['HTTP_ORIGIN']) : '*'));

/* ── Sécurité : POST uniquement ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

/* ── Validation des entrées ── */
$version    = isset($_POST['version'])    ? preg_replace('/[^0-9.]/', '', $_POST['version'])    : 'unknown';
$timestamp  = isset($_POST['timestamp'])  ? preg_replace('/[^0-9T:Z\-+.]/', '', $_POST['timestamp']) : date('c');
$action     = isset($_POST['action'])     ? preg_replace('/[^a-z_]/', '', $_POST['action'])     : 'unknown';
$categories = isset($_POST['categories']) ? $_POST['categories'] : '{}';

/* Valider le JSON des catégories */
$cats = json_decode($categories, true);
if (!is_array($cats)) {
    $cats = [];
}
/* N'autoriser que les clés connues */
$allowed = ['necessary', 'preferences', 'analytics', 'marketing'];
$cats = array_intersect_key($cats, array_flip($allowed));

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
$entry = json_encode([
    'ts'         => $timestamp,
    'ip'         => $ip_anon,
    'ua'         => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 120),
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
