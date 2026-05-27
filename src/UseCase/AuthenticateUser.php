<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\User;

final class AuthenticateUser
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user->password_hash())) {
            return null;
        }

        return $user;
    }
}