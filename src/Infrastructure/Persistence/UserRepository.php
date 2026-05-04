<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\User;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'mapRowToUser'], $rows);
    }

    public function create(User $user): User
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (first_name, last_name, email, password_hash, role)
             VALUES (:first_name, :last_name, :email, :password_hash, :role)'
        );

        $stmt->execute([
            'first_name' => $user->first_name(),
            'last_name' => $user->last_name(),
            'email' => $user->email(),
            'password_hash' => $user->password_hash(),
            'role' => $user->role(),
        ]);

        return $user;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function update(User $user): User
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET first_name = :first_name,
                 last_name = :last_name,
                 email = :email,
                 password_hash = :password_hash,
                 role = :role
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $user->id(),
            'first_name' => $user->first_name(),
            'last_name' => $user->last_name(),
            'email' => $user->email(),
            'password_hash' => $user->password_hash(),
            'role' => $user->role(),
        ]);

        return $user;
    }

    private function mapRowToUser(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['password_hash'],
            $row['role']
        );
    }
}