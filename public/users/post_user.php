<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$userController = require __DIR__ . '/../bootstrap.php';
$userController->store();
