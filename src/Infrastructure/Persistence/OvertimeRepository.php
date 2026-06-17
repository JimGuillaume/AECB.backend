<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\OvertimeRepositoryInterface;
use PDO;

class OvertimeRepository implements OvertimeRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUserAndMonth(int $userId, int $year, int $month): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                ot.overtime_id,
                ot.user_id,
                ot.month,
                ot.year,
                ot.hours_earned,
                ot.hours_used,
                ot.balance,
                ot.calculated_at,
                ot.updated_at
             FROM overtime_tracking ot
             WHERE ot.user_id = :user_id
               AND ot.year = :year
               AND ot.month = :month
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'year'    => $year,
            'month'   => $month,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findByUserAndYear(int $userId, int $year): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                ot.overtime_id,
                ot.user_id,
                ot.month,
                ot.year,
                ot.hours_earned,
                ot.hours_used,
                ot.balance,
                ot.calculated_at,
                ot.updated_at
             FROM overtime_tracking ot
             WHERE ot.user_id = :user_id
               AND ot.year = :year
             ORDER BY ot.month ASC'
        );

        $stmt->execute([
            'user_id' => $userId,
            'year'    => $year,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
