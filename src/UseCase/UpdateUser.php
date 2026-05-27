<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\User;
use DomainException;

final class UpdateUser
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function execute(
        int $id,
        string $firstName,
        string $lastName,
        string $email,
        ?string $password = null,
        ?string $role = null
    ): ?User {
        $existingUser = $this->users->findById($id);

        if ($existingUser === null) {
            return null;
        }

        $emailOwner = $this->users->findByEmail($email);

        if ($emailOwner !== null && $emailOwner->id() !== $id) {
            throw new DomainException('Email already exists');
        }

        $user = new User(
            $firstName,
            $lastName,
            $email,
            $password !== null && $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : $existingUser->password_hash(),
            $role !== null && $role !== '' ? $role : $existingUser->role(),
            $id
        );

        return $this->users->update($user);
    }
}