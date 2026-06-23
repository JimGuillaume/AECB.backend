<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class CreateWorkSchedule
{
    public function __construct(private UserRepository $users) {}

    public function execute(string $name, float $fraction, float $dailyHours): array
    {
        return $this->users->createSchedule($name, $fraction, $dailyHours);
    }
}
