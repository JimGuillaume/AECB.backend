<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$userController = require __DIR__ . '/../bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id === null || $id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Missing or invalid id parameter'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userController->updateAttendanceRecord($id);
