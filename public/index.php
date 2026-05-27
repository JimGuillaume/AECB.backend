<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';

http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['message' => 'Not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);