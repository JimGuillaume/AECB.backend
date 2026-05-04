<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\User;

final class GetUserById
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(int $id): ?User
    {
        return $this->users->findById($id);
    }
}