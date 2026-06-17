<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

$userController = require __DIR__ . '/../bootstrap.php';

$teamIds = [];
if (isset($_GET['team_ids']) && $_GET['team_ids'] !== '') {
    $teamIds = array_values(array_filter(
        array_map('intval', explode(',', $_GET['team_ids'])),
        fn(int $id) => $id > 0
    ));
}

$year  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');

if ($year <= 0 || $month < 1 || $month > 12) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Invalid year or month parameter'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userController->teamAttendance($teamIds, $year, $month);
