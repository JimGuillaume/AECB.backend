<?php
declare(strict_types=1);

use App\Controller\UserController;

return function (UserController $userController): void {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($path === '/users' && $method === 'GET') {
        $userController->index();
        return;
    }

    if (preg_match('#^/users/(\d+)$#', $path, $matches) === 1 && $method === 'GET') {
        $userController->show((int) $matches[1]);
        return;
    }

    if ($path === '/users' && $method === 'POST') {
        $userController->store();
        return;
    }

    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Route not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};