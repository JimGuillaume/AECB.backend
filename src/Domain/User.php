<?php
namespace App\Domain;

class User
{
    private int $id;
    private string $email;
    private string $password_hash;
    private string $first_name;
    private string $last_name;
    private string $role;

    public function __construct(int $id, string $first_name, string $last_name, string $email, string $password_hash, string $role)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->role = $role;
    }

    public function id(): int { return $this->id; }
    public function email(): string { return $this->email; }
    public function first_name(): string { return $this->first_name; }
    public function last_name(): string { return $this->last_name; }
    public function password_hash(): string { return $this->password_hash; }
    public function role(): string { return $this->role; }
}