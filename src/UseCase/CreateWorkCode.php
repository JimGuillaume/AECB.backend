<?php
declare(strict_types=1);

namespace App\UseCase;

use App\Infrastructure\Persistence\UserRepository;

final class CreateWorkCode
{
    public function __construct(private UserRepository $users) {}

    public function execute(string $codeName, string $description, bool $isWorked): array
    {
        return $this->users->createCode($codeName, $description, $isWorked);
    }
}
