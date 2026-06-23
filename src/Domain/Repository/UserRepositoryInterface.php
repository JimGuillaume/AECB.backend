<?php

namespace App\Domain\Repository;

use App\Domain\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findAll(): array;
    public function findPrestationsByUserAndMonth(int $userId, int $year, int $month): array;
    public function findTeamIdsByUserId(int $userId): array;
    public function findUsersByTeamIds(array $teamIds): array;
    public function findTeamPrestationsByMonth(array $teamIds, int $year, int $month): array;
    public function findAttendanceById(int $id): ?array;
    public function createAttendance(int $userId, int $teamId, string $date, int $codeId, float $hoursValue, ?string $notes, ?int $createdBy): array;
    public function updateAttendance(int $id, int $codeId, float $hoursValue, ?string $notes): ?array;
    public function deleteAttendance(int $id): bool;
    public function findAllTeams(): array;
    public function findAllCodes(): array;
    public function create(User $user): User;
    public function delete(int $id): bool;
    public function update(User $user): User;
}
