<?php

namespace App\Domain\Repository;

use App\Domain\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findAll(): array;
    public function findPrestationsByUserAndMonth(int $userId, int $year, int $month): array;
    public function create(User $user): User;
    public function delete(int $id): bool;
    public function update(User $user): User;
}
