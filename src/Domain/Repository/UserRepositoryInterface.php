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
    public function findAllSchedules(): array;
    public function findCodeById(int $id): ?array;
    public function createCode(string $codeName, string $description, bool $isWorked): array;
    public function updateCode(int $id, string $codeName, string $description, bool $isWorked): ?array;
    public function deleteCode(int $id): bool;
    public function findScheduleById(int $id): ?array;
    public function createSchedule(string $name, float $fraction, float $dailyHours): array;
    public function updateSchedule(int $id, string $name, float $fraction, float $dailyHours): ?array;
    public function deleteSchedule(int $id): bool;
    public function create(User $user): User;
    public function delete(int $id): bool;
    public function update(User $user): User;
}
