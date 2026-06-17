<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class CreateAttendance
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(
        int $userId,
        int $teamId,
        string $date,
        int $codeId,
        float $hoursValue,
        ?string $notes,
        ?int $createdBy
    ): array {
        return $this->users->createAttendance($userId, $teamId, $date, $codeId, $hoursValue, $notes, $createdBy);
    }
}
