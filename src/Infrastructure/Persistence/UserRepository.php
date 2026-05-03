<?php
namespace App\Infrastructure\Persistence;

use App\Domain\User;
use App\Domain\Repository\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {}
    public function findByEmail(string $email): ?User
    {}
    public function findAll(): array
    {}
    public function create(User $user): User
    {}
    public function delete(int $id): bool
    {}
    public function update(User $user): User
    {}
}