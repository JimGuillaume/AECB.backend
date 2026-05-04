<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\User;
use DomainException;

final class CreateUser
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $role = 'user'
    ): User {
        $existingUser = $this->users->findByEmail($email);

        if ($existingUser !== null) {
            throw new DomainException('Email already exists');
        }

        $user = new User(
            $firstName,
            $lastName,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        );

        return $this->users->create($user);
    }
}