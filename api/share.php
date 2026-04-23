<?php
// Intercepter les erreurs fatales et les retourner en JSON
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['error' => 'Erreur serveur: ' . $err['message']]);
    }
});

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$maxJson = 80 * 1024;   // 80 Ko
$maxPng  = 500 * 1024;  // 500 Ko

$jsonRaw = $_POST['data']  ?? '';
$pngData = $_POST['image'] ?? '';

if (strlen($jsonRaw) > $maxJson) {
    http_response_code(413);
    echo json_encode(['error' => 'Frise trop volumineuse (max 80 Ko)']);
    exit;
}

$data = json_decode($jsonRaw, true);
if (!is_array($data) || !array_key_exists('tableaux', $data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$dir = __DIR__ . '/../data_user/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    file_put_contents($dir . '.htaccess', "Options -Indexes\n");
}

// ID aléatoire unique (8 chars hex)
do {
    $id = bin2hex(random_bytes(4));
} while (file_exists($dir . $id . '.json'));

// Sauvegarde JSON
if (file_put_contents($dir . $id . '.json', $jsonRaw) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible d\'écrire dans data_user/']);
    exit;
}

// Sauvegarde PNG
if ($pngData !== '' && preg_match('/^data:image\/png;base64,/', $pngData)) {
    $pngBinary = base64_decode(substr($pngData, strlen('data:image/png;base64,')));
    if ($pngBinary !== false && strlen($pngBinary) <= $maxPng) {
        file_put_contents($dir . $id . '.png', $pngBinary);
    }
}

require __DIR__ . '/../config.php';
echo json_encode(['url' => SITE_URL . '/user/?id=' . $id, 'id' => $id]);
