<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class ListUsersByTeams
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(array $teamIds): array
    {
        return $this->users->findUsersByTeamIds($teamIds);
    }
}
