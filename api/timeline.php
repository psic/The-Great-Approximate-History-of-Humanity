<?php
/**
 * API : renvoie un fichier timeline (pas, tableaux, evenements) pour chargement AJAX.
 * GET vue=moderne|histoire|humanite|terre|univers pour choisir le fichier.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

$vues = [
    'moderne'  => 'moderne.json',
    'histoire' => 'histoire.json',
    'humanite' => 'humanite.json',
    'terre'    => 'terre.json',
    'univers'  => 'univers.json',
];

$vue = isset($_GET['vue']) ? trim($_GET['vue']) : '';
if ($vue === '' || !isset($vues[$vue])) {
    $vue = 'moderne';
}

$jsonPath = dirname(__DIR__) . '/data/' . $vues[$vue];

if (!is_readable($jsonPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Fichier introuvable']);
    exit;
}

$raw = file_get_contents($jsonPath);
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON invalide']);
    exit;
}

$pas = isset($data['pas']) ? (int) $data['pas'] : 100;
if ($pas <= 0) {
    $pas = 100;
}
$data['pas'] = $pas;

if (!isset($data['tableaux']) && isset($data['periodes'])) {
    $data['tableaux'] = [ $data['periodes'] ];
}

$evenements = $data['evenements'] ?? [];
usort($evenements, function ($a, $b) {
    return ($a['date'] ?? 0) <=> ($b['date'] ?? 0);
});
$data['evenements'] = $evenements;

echo json_encode($data, JSON_UNESCAPED_UNICODE);
