<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';

$userController = require __DIR__ . '/bootstrap.php';

$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');

if ($year <= 0 || $month < 1 || $month > 12) {
	http_response_code(400);
	header('Content-Type: application/json');
	echo json_encode(['message' => 'Invalid year or month parameter'], JSON_UNESCAPED_UNICODE);
	exit;
}

$userController->me($year, $month);