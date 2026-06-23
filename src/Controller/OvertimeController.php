<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Security\JwtService;
use App\UseCase\GetUserById;
use App\UseCase\GetUserOvertimeForMonth;
use App\UseCase\GetUserOvertimeForYear;

final class OvertimeController extends BaseController
{
    public function __construct(
        JwtService $jwtService,
        int $jwtTtlSeconds,
        private GetUserById $getUserById,
        private GetUserOvertimeForMonth $getUserOvertimeForMonth,
        private GetUserOvertimeForYear $getUserOvertimeForYear,
    ) {
        parent::__construct($jwtService, $jwtTtlSeconds);
    }

    public function month(int $userId, int $year, int $month): void
    {
        $user = $this->getUserById->execute($userId);

        if ($user === null) {
            $this->respond(['message' => 'User not found'], 404);
            return;
        }

        $overtime = $this->getUserOvertimeForMonth->execute($userId, $year, $month);

        $this->respond([
            'user_id' => $userId,
            'period'  => ['year' => $year, 'month' => $month],
            'overtime' => $overtime !== null ? $this->serializeOvertime($overtime) : null,
        ]);
    }

    public function year(int $userId, int $year): void
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

    private function serializeOvertime(array $row): array
    {
        return [
            'overtime_id'   => isset($row['overtime_id'])  ? (int)   $row['overtime_id']  : null,
            'user_id'       => isset($row['user_id'])       ? (int)   $row['user_id']       : null,
            'month'         => isset($row['month'])         ? (int)   $row['month']         : null,
            'year'          => isset($row['year'])          ? (int)   $row['year']          : null,
            'hours_earned'  => isset($row['hours_earned'])  ? (float) $row['hours_earned']  : null,
            'hours_used'    => isset($row['hours_used'])    ? (float) $row['hours_used']    : null,
            'balance'       => isset($row['balance'])       ? (float) $row['balance']       : null,
            'calculated_at' => $row['calculated_at'] ?? null,
            'updated_at'    => $row['updated_at']    ?? null,
        ];
    }
}
