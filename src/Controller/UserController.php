<?php
declare(strict_types=1);

namespace App\Controller;

use App\Domain\User;
use App\UseCase\CreateUser;
use App\UseCase\GetUserById;
use App\UseCase\ListUsers;
use DomainException;

final class UserController
{
    public function __construct(
        private ListUsers $listUsers,
        private GetUserById $getUserById,
        private CreateUser $createUser
    ) {
    }

    public function index(): void
    {
        $this->respond($this->serializeUsers($this->listUsers->execute()));
    }

    public function show(int $id): void
    {
        $user = $this->getUserById->execute($id);

        if ($user === null) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $this->respond($this->serializeUser($user));
    }

    public function store(): void
    {
        $payload = $this->readJsonBody();

        $firstName = trim((string) ($payload['first_name'] ?? ''));
        $lastName = trim((string) ($payload['last_name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $role = trim((string) ($payload['role'] ?? 'user'));

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            $this->respond(['message' => 'first_name, last_name, email, and password are required'], 422);
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

    /**
     * @param User[] $users
     */
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
}