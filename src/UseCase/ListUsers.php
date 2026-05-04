<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Domain\Repository\UserRepositoryInterface;

final class ListUsers
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(): array
    {
        return $this->users->findAll();
    }
}