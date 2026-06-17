<?php
declare(strict_types=1);

namespace App\Controller;

use App\Domain\User;
use App\Infrastructure\Security\JwtService;
use App\UseCase\AuthenticateUser;
use App\UseCase\DeleteUser;
use App\UseCase\CreateUser;
use App\UseCase\GetUserById;
use App\UseCase\GetUserOvertimeForMonth;
use App\UseCase\GetUserOvertimeForYear;
use App\UseCase\GetUserPrestationsForMonth;
use App\UseCase\ListUsers;
use App\UseCase\UpdateUser;
use DomainException;

final class UserController
{
    private const ALLOWED_ROLES = ['admin', 'manager', 'team_leader', 'worker'];

    public function __construct(
        private ListUsers $listUsers,
        private GetUserById $getUserById,
        private GetUserPrestationsForMonth $getUserPrestationsForMonth,
        private GetUserOvertimeForMonth $getUserOvertimeForMonth,
        private GetUserOvertimeForYear $getUserOvertimeForYear,
        private CreateUser $createUser,
        private UpdateUser $updateUser,
        private DeleteUser $deleteUser,
        private AuthenticateUser $authenticateUser,
        private JwtService $jwtService,
        private int $jwtTtlSeconds
    ) {
    }

    public function index(): void
    {
        $this->respond($this->serializeUsers($this->listUsers->execute()));
    }

    public function show(int $id, int $year, int $month): void
    {
        $user = $this->getUserById->execute($id);

        if ($user === null) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $prestations = $this->getUserPrestationsForMonth->execute($id, $year, $month);

        $this->respond([
            'user' => $this->serializeUser($user),
            'period' => [
                'year' => $year,
                'month' => $month,
            ],
            'prestations' => $this->serializePrestations($prestations),
        ]);
    }

    public function store(): void
    {
        $payload = $this->readJsonBody();

        $firstName = trim((string) ($payload['first_name'] ?? ''));
        $lastName = trim((string) ($payload['last_name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $role = trim((string) ($payload['role'] ?? 'worker'));

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            $this->respond(['message' => 'first_name, last_name, email, and password are required'], 422);
            return;
        }

        if (!$this->isAllowedRole($role)) {
            $this->respond(['message' => 'role must be one of: admin, manager, team_leader, worker'], 422);
            return;
        }

        try {
            $user = $this->createUser->execute($firstName, $lastName, $email, $password, $role);
        } catch (DomainException $exception) {
            $this->respond(['message' => $exception->getMessage()], 409);
            return;
        }

        $this->respond($this->serializeUser($user), 201);
    }

    public function update(int $id): void
    {
        $payload = $this->readJsonBody();

        $firstName = trim((string) ($payload['first_name'] ?? ''));
        $lastName = trim((string) ($payload['last_name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = array_key_exists('password', $payload) ? trim((string) $payload['password']) : null;
        $role = array_key_exists('role', $payload) ? trim((string) $payload['role']) : null;

        if ($firstName === '' || $lastName === '' || $email === '') {
            $this->respond(['message' => 'first_name, last_name, and email are required'], 422);
            return;
        }

        if ($role !== null && $role !== '' && !$this->isAllowedRole($role)) {
            $this->respond(['message' => 'role must be one of: admin, manager, team_leader, worker'], 422);
            return;
        }

        try {
            $user = $this->updateUser->execute($id, $firstName, $lastName, $email, $password, $role);
        } catch (DomainException $exception) {
            $this->respond(['message' => $exception->getMessage()], 409);
            return;
        }

        if ($user === null) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $this->respond($this->serializeUser($user));
    }

    public function destroy(int $id): void
    {
        if (!$this->deleteUser->execute($id)) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $this->respond(['message' => 'User deleted']);
    }

    public function login(): void
    {
        $payload = $this->readJsonBody();

        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->respond(['message' => 'email and password are required'], 422);
            return;
        }

        $user = $this->authenticateUser->execute($email, $password);

        if ($user === null) {
            $this->respond(['message' => 'Invalid credentials'], 401);
            return;
        }

        $token = $this->jwtService->issue([
            'sub' => $user->id(),
            'email' => $user->email(),
            'role' => $user->role(),
        ]);

        $this->setAuthCookie($token);

        $this->respond([
            'message' => 'Login successful',
            'user' => $this->serializeUser($user),
        ]);
    }

    public function me(int $year, int $month): void
    {
        $claims = $this->getAuthenticatedClaims();

        if ($claims === null) {
            $this->respond(['message' => 'Unauthorized'], 401);
            return;
        }

        $userId = isset($claims['sub']) ? (int) $claims['sub'] : null;

        if ($userId === null || $userId <= 0) {
            $this->respond(['message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->getUserById->execute($userId);

        if ($user === null) {
            $this->respond(['message' => 'Unauthorized'], 401);
            return;
        }

        $prestations = $this->getUserPrestationsForMonth->execute($userId, $year, $month);

        $this->respond([
            'message' => 'Authenticated',
            'user' => $this->serializeUser($user),
            'period' => [
                'year' => $year,
                'month' => $month,
            ],
            'prestations' => $this->serializePrestations($prestations),
        ]);
    }

    public function overtime(int $userId, int $year, int $month): void
    {
        $user = $this->getUserById->execute($userId);

        if ($user === null) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $overtime = $this->getUserOvertimeForMonth->execute($userId, $year, $month);

        $this->respond([
            'user_id' => $userId,
            'period'  => [
                'year'  => $year,
                'month' => $month,
            ],
            'overtime' => $overtime !== null ? $this->serializeOvertime($overtime) : null,
        ]);
    }

    public function overtimeYear(int $userId, int $year): void
    {
        $user = $this->getUserById->execute($userId);

        if ($user === null) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $rows = $this->getUserOvertimeForYear->execute($userId, $year);

        $this->respond([
            'user_id' => $userId,
            'year'    => $year,
            'months'  => array_map([$this, 'serializeOvertime'], $rows),
        ]);
    }

    public function logout(): void
    {
        $this->clearAuthCookie();
        $this->respond(['message' => 'Logged out']);
    }

    private function readJsonBody(): array
    {
        $body = file_get_contents('php://input');

        if ($body === false || $body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function respond(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function setAuthCookie(string $token): void
    {
        setcookie('aecb_jwt', $token, [
            'expires' => time() + $this->jwtTtlSeconds,
            'path' => '/',
            'secure' => $this->isHttpsRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function clearAuthCookie(): void
    {
        setcookie('aecb_jwt', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $this->isHttpsRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function getAuthenticatedClaims(): ?array
    {
        $token = $_COOKIE['aecb_jwt'] ?? '';

        if ($token === '') {
            return null;
        }

        return $this->jwtService->verify($token);
    }

    private function isHttpsRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 80) === 443;
    }

    private function isAllowedRole(string $role): bool
    {
        return in_array($role, self::ALLOWED_ROLES, true);
    }

    private function serializeUsers(array $users): array
    {
        return array_map([$this, 'serializeUser'], $users);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id(),
            'first_name' => $user->first_name(),
            'last_name' => $user->last_name(),
            'email' => $user->email(),
            'role' => $user->role(),
        ];
    }

    private function serializeOvertime(array $row): array
    {
        return [
            'overtime_id'   => isset($row['overtime_id']) ? (int) $row['overtime_id'] : null,
            'user_id'       => isset($row['user_id']) ? (int) $row['user_id'] : null,
            'month'         => isset($row['month']) ? (int) $row['month'] : null,
            'year'          => isset($row['year']) ? (int) $row['year'] : null,
            'hours_earned'  => isset($row['hours_earned']) ? (float) $row['hours_earned'] : null,
            'hours_used'    => isset($row['hours_used']) ? (float) $row['hours_used'] : null,
            'balance'       => isset($row['balance']) ? (float) $row['balance'] : null,
            'calculated_at' => $row['calculated_at'] ?? null,
            'updated_at'    => $row['updated_at'] ?? null,
        ];
    }

    private function serializePrestations(array $prestations): array
    {
        return array_map(static function (array $prestation): array {
            return [
                'attendance_id' => isset($prestation['attendance_id']) ? (int) $prestation['attendance_id'] : null,
                'user_id' => isset($prestation['user_id']) ? (int) $prestation['user_id'] : null,
                'team_id' => isset($prestation['team_id']) ? (int) $prestation['team_id'] : null,
                'attendance_date' => $prestation['attendance_date'] ?? null,
                'code_id' => isset($prestation['code_id']) ? (int) $prestation['code_id'] : null,
                'code_key' => $prestation['code_key'] ?? null,
                'hours_value' => isset($prestation['hours_value']) ? (float) $prestation['hours_value'] : null,
                'notes' => $prestation['notes'] ?? null,
                'created_by' => isset($prestation['created_by']) ? (int) $prestation['created_by'] : null,
                'created_at' => $prestation['created_at'] ?? null,
                'updated_at' => $prestation['updated_at'] ?? null,
            ];
        }, $prestations);
    }
}