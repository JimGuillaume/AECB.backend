<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method !== 'DELETE') {
    http_response_code(405);
    header('Allow: DELETE');
    echo json_encode(['message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Missing id parameter'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$userController = require __DIR__ . '/bootstrap.php';
$userController->destroy($id);