<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class ListWorkSchedules
{
    public function __construct(private UserRepository $users)
    {
    }

    public function execute(): array
    {
        return $this->users->findAllSchedules();
    }
}
