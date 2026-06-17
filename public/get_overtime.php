<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userController = require __DIR__ . '/bootstrap.php';

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;
$year   = isset($_GET['year'])    ? (int) $_GET['year']    : (int) date('Y');
$month  = isset($_GET['month'])   ? (int) $_GET['month']   : (int) date('n');

if ($userId === null || $userId <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Missing or invalid user_id parameter'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($year <= 0 || $month < 1 || $month > 12) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Invalid year or month parameter'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userController->overtime($userId, $year, $month);
