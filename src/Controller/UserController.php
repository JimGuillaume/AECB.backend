<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\CreateUser;
use App\UseCase\DeleteUser;
use App\UseCase\GetUserById;
use App\UseCase\GetUserPrestationsForMonth;
use App\UseCase\GetUserTeamIds;
use App\UseCase\ListUsers;
use App\UseCase\UpdateUser;
use DomainException;

final class UserController extends BaseController
{
    private const ALLOWED_ROLES = ['admin', 'manager', 'team_leader', 'worker'];

    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private ListUsers $listUsers,
        private GetUserById $getUserById,
        private GetUserPrestationsForMonth $getUserPrestationsForMonth,
        private GetUserTeamIds $getUserTeamIds,
        private CreateUser $createUser,
        private UpdateUser $updateUser,
        private DeleteUser $deleteUser,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
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
        $teamIds     = array_map('intval', $this->getUserTeamIds->execute($id));

        $this->respond([
            'user'        => $this->serializeUser($user),
            'period'      => ['year' => $year, 'month' => $month],
            'prestations' => $this->serializePrestations($prestations),
            'team_ids'    => $teamIds,
        ]);
    }

    public function store(): void
    {
        $payload = $this->readJsonBody();

        $firstName = trim((string) ($payload['first_name'] ?? ''));
        $lastName  = trim((string) ($payload['last_name']  ?? ''));
        $email     = trim((string) ($payload['email']      ?? ''));
        $password  =      (string) ($payload['password']   ?? '');
        $role      = trim((string) ($payload['role']       ?? 'worker'));

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
        $lastName  = trim((string) ($payload['last_name']  ?? ''));
        $email     = trim((string) ($payload['email']      ?? ''));
        $password  = array_key_exists('password', $payload) ? trim((string) $payload['password']) : null;
        $role      = array_key_exists('role', $payload)     ? trim((string) $payload['role'])     : null;

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

    private function isAllowedRole(string $role): bool
    {
        return in_array($role, self::ALLOWED_ROLES, true);
    }
}
