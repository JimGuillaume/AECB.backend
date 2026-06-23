<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

require_method('GET');

$userId = require_id_param('user_id');
$year   = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

if ($year <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Invalid year parameter'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$c = require __DIR__ . '/../bootstrap.php';
$c['overtime']->year($userId, $year);
