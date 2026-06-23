<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class DeleteWorkCode
{
    public function __construct(private UserRepository $users) {}

    public function execute(int $id): bool
    {
        return $this->users->deleteCode($id);
    }
}
