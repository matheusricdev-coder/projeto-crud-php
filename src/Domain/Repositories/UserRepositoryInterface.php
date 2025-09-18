<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function create(User $user): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findAll(int $page = 1, int $limit = 10): array;
    public function update(User $user): User;
    public function delete(int $id): bool;
    public function existsByEmail(string $email): bool;
    public function getTotalCount(): int;
}
