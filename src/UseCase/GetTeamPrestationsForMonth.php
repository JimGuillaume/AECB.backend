<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class GetTeamPrestationsForMonth
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(array $teamIds, int $year, int $month): array
    {
        return $this->users->findTeamPrestationsByMonth($teamIds, $year, $month);
    }
}
