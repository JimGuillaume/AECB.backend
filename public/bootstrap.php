<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\UserController;
use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Persistence\UserRepository;
use App\Infrastructure\Security\JwtService;
use App\UseCase\AuthenticateUser;
use App\UseCase\CreateUser;
use App\UseCase\DeleteUser;
use App\UseCase\GetUserById;
use App\UseCase\ListUsers;
use App\UseCase\UpdateUser;

$jwtSecret = 'Cle-JwT-SGDB-Maison-Rive-42-OK99';
$jwtTtlSeconds = 3600;

$jwtService = new JwtService($jwtSecret, $jwtTtlSeconds);
$pdo = DatabaseConnection::create();
$userRepository = new UserRepository($pdo);

return new UserController(
    new ListUsers($userRepository),
    new GetUserById($userRepository),
    new CreateUser($userRepository),
    new UpdateUser($userRepository),
    new DeleteUser($userRepository),
    new AuthenticateUser($userRepository),
    $jwtService,
    $jwtTtlSeconds
);
