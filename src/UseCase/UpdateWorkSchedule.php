<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class UpdateWorkSchedule
{
    public function __construct(private UserRepository $users) {}

    public function execute(int $id, string $name, float $fraction, float $dailyHours): ?array
    {
        return $this->users->updateSchedule($id, $name, $fraction, $dailyHours);
    }
}
