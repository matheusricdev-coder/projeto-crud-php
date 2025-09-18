<?php

namespace App\Domain\Entities;

class Drink
{
    private int $id;
    private int $userId;
    private \DateTime $consumedAt;
    private int $quantity;

    public function __construct(
        int $userId,
        \DateTime $consumedAt = null,
        int $quantity = 1,
        int $id = 0
    ) {
        $this->userId = $userId;
        $this->consumedAt = $consumedAt ?? new \DateTime();
        $this->quantity = $quantity;
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getConsumedAt(): \DateTime
    {
        return $this->consumedAt;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setConsumedAt(\DateTime $consumedAt): void
    {
        $this->consumedAt = $consumedAt;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'consumedAt' => $this->consumedAt->format('Y-m-d H:i:s'),
            'quantity' => $this->quantity
        ];
    }
}
