<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Domain\Repository\UserRepositoryInterface;

final class DeleteUser
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(int $id): bool
    {
        if ($this->users->findById($id) === null) {
            return false;
        }

        return $this->users->delete($id);
    }
}