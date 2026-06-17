<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\UserController;
use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Persistence\OvertimeRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Infrastructure\Security\JwtService;
use App\UseCase\AuthenticateUser;
use App\UseCase\CreateAttendance;
use App\UseCase\CreateUser;
use App\UseCase\DeleteAttendance;
use App\UseCase\DeleteUser;
use App\UseCase\GetTeamPrestationsForMonth;
use App\UseCase\GetUserById;
use App\UseCase\GetUserOvertimeForMonth;
use App\UseCase\GetUserOvertimeForYear;
use App\UseCase\GetUserPrestationsForMonth;
use App\UseCase\GetUserTeamIds;
use App\UseCase\ListUsers;
use App\UseCase\ListUsersByTeams;
use App\UseCase\ListWorkCodes;
use App\UseCase\UpdateAttendance;
use App\UseCase\UpdateUser;

$jwtSecret = 'Cle-JwT-SGDB-Maison-Rive-42-OK99'; //32Char
$jwtTtlSeconds = 3600;

$jwtService = new JwtService($jwtSecret, $jwtTtlSeconds);
$pdo = DatabaseConnection::create();
$userRepository = new UserRepository($pdo);
$overtimeRepository = new OvertimeRepository($pdo);

return new UserController(
    new ListUsers($userRepository),
    new GetUserById($userRepository),
    new GetUserPrestationsForMonth($userRepository),
    new GetUserOvertimeForMonth($overtimeRepository),
    new GetUserOvertimeForYear($overtimeRepository),
    new CreateUser($userRepository),
    new UpdateUser($userRepository),
    new DeleteUser($userRepository),
    new AuthenticateUser($userRepository),
    $jwtService,
    $jwtTtlSeconds,
    new GetUserTeamIds($userRepository),
    new ListUsersByTeams($userRepository),
    new GetTeamPrestationsForMonth($userRepository),
    new CreateAttendance($userRepository),
    new UpdateAttendance($userRepository),
    new DeleteAttendance($userRepository),
    new ListWorkCodes($userRepository)
);
