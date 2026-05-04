<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\UserController;
use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Persistence\UserRepository;
use App\UseCase\CreateUser;
use App\UseCase\GetUserById;
use App\UseCase\ListUsers;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$pdo = DatabaseConnection::createFromEnv();
$userRepository = new UserRepository($pdo);

$userController = new UserController(
    new ListUsers($userRepository),
    new GetUserById($userRepository),
    new CreateUser($userRepository)
);

$routeHandler = require __DIR__ . '/routes.php';
$routeHandler($userController);