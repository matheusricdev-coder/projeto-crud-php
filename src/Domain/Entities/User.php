<?php

namespace App\Domain\Entities;

class User
{
    private int $id;
    private string $email;
    private string $name;
    private string $password;
    private int $drinkCounter;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        string $email,
        string $name,
        string $password,
        int $id = 0,
        int $drinkCounter = 0,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->id = $id;
        $this->drinkCounter = $drinkCounter;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDrinkCounter(): int
    {
        return $this->drinkCounter;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new \DateTime();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTime();
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
        $this->updatedAt = new \DateTime();
    }

    public function incrementDrinkCounter(): void
    {
        $this->drinkCounter++;
        $this->updatedAt = new \DateTime();
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    public function toArray(): array
    {
        return [
            'iduser' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'drinkCounter' => $this->drinkCounter,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }

    public function toArrayWithoutPassword(): array
    {
        return [
            'iduser' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'drinkCounter' => $this->drinkCounter
        ];
    }
}
