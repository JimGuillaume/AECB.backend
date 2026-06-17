<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class GetUserTeamIds
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(int $userId): array
    {
        return $this->users->findTeamIdsByUserId($userId);
    }
}
