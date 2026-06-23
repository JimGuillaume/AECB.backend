<?php
declare(strict_types=1);

function require_method(string|array $allowed): void
{
    $allowed = array_map('strtoupper', (array) $allowed);
    $method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, $allowed, true)) {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowed));
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

function require_id_param(string $param = 'id'): int
{
    $value = isset($_GET[$param]) ? (int) $_GET[$param] : null;
    if ($value === null || $value <= 0) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Missing or invalid ' . $param . ' parameter'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    return $value;
}

function parse_year_month(): array
{
    $year  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');
    $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
    if ($year <= 0 || $month < 1 || $month > 12) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Invalid year or month parameter'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    return [$year, $month];
}

function parse_team_ids(): array
{
    if (!isset($_GET['team_ids']) || $_GET['team_ids'] === '') {
        return [];
    }
    return array_values(array_filter(
        array_map('intval', explode(',', $_GET['team_ids'])),
        fn(int $id) => $id > 0
    ));
}
