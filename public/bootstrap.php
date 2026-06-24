<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\AttendanceController;
use App\Controller\AuthController;
use App\Controller\OvertimeController;
use App\Controller\SettingController;
use App\Controller\TeamController;
use App\Controller\UserController;
use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Persistence\OvertimeRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Infrastructure\Security\JwtService;
use App\UseCase\AuthenticateUser;
use App\UseCase\CreateAttendance;
use App\UseCase\CreateUser;
use App\UseCase\CreateWorkCode;
use App\UseCase\CreateWorkSchedule;
use App\UseCase\DeleteAttendance;
use App\UseCase\DeleteUser;
use App\UseCase\DeleteWorkCode;
use App\UseCase\DeleteWorkSchedule;
use App\UseCase\GetTeamPrestationsForMonth;
use App\UseCase\GetUserById;
use App\UseCase\ListTeams;
use App\UseCase\GetUserOvertimeForMonth;
use App\UseCase\GetUserOvertimeForYear;
use App\UseCase\GetUserPrestationsForMonth;
use App\UseCase\GetUserTeamIds;
use App\UseCase\ListUsers;
use App\UseCase\ListUsersByTeams;
use App\UseCase\ListWorkCodes;
use App\UseCase\ListWorkSchedules;
use App\UseCase\UpdateAttendance;
use App\UseCase\UpdateUser;
use App\UseCase\UpdateWorkCode;
use App\UseCase\UpdateWorkSchedule;

// Clé secrète utilisée pour signer les tokens JWT — à déplacer dans une variable d'environnement en production
$jwtSecret     = 'Cle-JwT-SGDB-Maison-Rive-42-OK99';
// Durée de validité du token en secondes (1 heure)
$jwtTtlSeconds = 3600;

$jwtService         = new JwtService($jwtSecret, $jwtTtlSeconds);
$pdo                = DatabaseConnection::create();
$userRepository     = new UserRepository($pdo);
$overtimeRepository = new OvertimeRepository($pdo);

return [
    'auth' => new AuthController(
        $jwtService,
        $jwtTtlSeconds,
        new AuthenticateUser($userRepository),
        new GetUserById($userRepository),
        new GetUserPrestationsForMonth($userRepository),
        new GetUserTeamIds($userRepository),
    ),
    'user' => new UserController(
        $jwtService,
        $jwtTtlSeconds,
        new ListUsers($userRepository),
        new GetUserById($userRepository),
        new GetUserPrestationsForMonth($userRepository),
        new GetUserTeamIds($userRepository),
        new CreateUser($userRepository),
        new UpdateUser($userRepository),
        new DeleteUser($userRepository),
    ),
    'attendance' => new AttendanceController(
        $jwtService,
        $jwtTtlSeconds,
        new CreateAttendance($userRepository),
        new UpdateAttendance($userRepository),
        new DeleteAttendance($userRepository),
        new ListWorkCodes($userRepository),
    ),
    'team' => new TeamController(
        $jwtService,
        $jwtTtlSeconds,
        new ListUsersByTeams($userRepository),
        new GetTeamPrestationsForMonth($userRepository),
        new ListTeams($userRepository),
    ),
    'overtime' => new OvertimeController(
        $jwtService,
        $jwtTtlSeconds,
        new GetUserById($userRepository),
        new GetUserOvertimeForMonth($overtimeRepository),
        new GetUserOvertimeForYear($overtimeRepository),
    ),
    'setting' => new SettingController(
        $jwtService,
        $jwtTtlSeconds,
        new ListWorkSchedules($userRepository),
        new ListWorkCodes($userRepository),
        new CreateWorkCode($userRepository),
        new UpdateWorkCode($userRepository),
        new DeleteWorkCode($userRepository),
        new CreateWorkSchedule($userRepository),
        new UpdateWorkSchedule($userRepository),
        new DeleteWorkSchedule($userRepository),
    ),
];
