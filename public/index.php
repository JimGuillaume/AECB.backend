<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Persistence\UserRepository;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$pdo = DatabaseConnection::createFromEnv();

$userRepository = new UserRepository($pdo);

// from here, pass $userRepository into your use cases/controllers