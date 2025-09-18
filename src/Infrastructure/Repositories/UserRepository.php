<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(User $user): User
    {
        $sql = "INSERT INTO users (email, name, password, drink_counter, created_at, updated_at)
                VALUES (:email, :name, :password, :drink_counter, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $user->getEmail(),
            ':name' => $user->getName(),
            ':password' => $user->getPassword(),
            ':drink_counter' => $user->getDrinkCounter(),
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $user->setId((int) $this->db->lastInsertId());
        return $user;
    }

    public function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findAll(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->hydrate($data);
        }

        return $results;
    }

    public function update(User $user): User
    {
        $sql = "UPDATE users SET
                email = :email,
                name = :name,
                password = :password,
                drink_counter = :drink_counter,
                updated_at = :updated_at
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $user->getId(),
            ':email' => $user->getEmail(),
            ':name' => $user->getName(),
            ':password' => $user->getPassword(),
            ':drink_counter' => $user->getDrinkCounter(),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        return $user;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function existsByEmail(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetchColumn() > 0;
    }

    public function getTotalCount(): int
    {
        $sql = "SELECT COUNT(*) FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function hydrate(array $data): User
    {
        return new User(
            $data['email'],
            $data['name'],
            $data['password'],
            (int) $data['id'],
            (int) $data['drink_counter'],
            new \DateTime($data['created_at']),
            new \DateTime($data['updated_at'])
        );
    }
}

