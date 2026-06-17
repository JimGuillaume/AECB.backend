<?php
declare(strict_types=1);

namespace App\Domain\Repository;

interface OvertimeRepositoryInterface
{
    public function findByUserAndMonth(int $userId, int $year, int $month): ?array;
    public function findByUserAndYear(int $userId, int $year): array;
}
