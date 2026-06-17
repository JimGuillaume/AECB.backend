<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class UpdateAttendance
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(int $id, int $codeId, float $hoursValue, ?string $notes): ?array
    {
        return $this->users->updateAttendance($id, $codeId, $hoursValue, $notes);
    }
}
