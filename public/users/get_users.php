<?php
declare(strict_types=1);

require_once __DIR__ . '/../cors.php';

if (isset($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'This endpoint returns all users; use users/get_user.php?id=... to fetch a single user.'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$c = require __DIR__ . '/../bootstrap.php';
$c['user']->index();
