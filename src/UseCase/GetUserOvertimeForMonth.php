<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\OvertimeRepository;

final class GetUserOvertimeForMonth
{
    public function __construct(private OvertimeRepository $overtime)
    {
    }

    public function execute(int $userId, int $year, int $month): ?array
    {
        return $this->overtime->findByUserAndMonth($userId, $year, $month);
    }
}
