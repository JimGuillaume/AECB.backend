<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class GetUserPrestationsForMonth
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(int $userId, int $year, int $month): array
    {
        return $this->users->findPrestationsByUserAndMonth($userId, $year, $month);
    }
}