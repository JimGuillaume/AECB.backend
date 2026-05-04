<?php
declare(strict_types=1);

namespace App\Domain;

class User
{
    private ?int $id;
    private string $first_name;
    private string $last_name;
    private string $email;
    private string $password_hash;
    private string $role;

    public function __construct(
        string $first_name,
        string $last_name,
        string $email,
        string $password_hash,
        string $role,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->role = $role;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['password_hash'],
            $row['role'],
            isset($row['id']) ? (int) $row['id'] : null
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function first_name(): string
    {
        return $this->first_name;
    }

    public function last_name(): string
    {
        return $this->last_name;
    }

    public function password_hash(): string
    {
        return $this->password_hash;
    }

    public function role(): string
    {
        return $this->role;
    }
}