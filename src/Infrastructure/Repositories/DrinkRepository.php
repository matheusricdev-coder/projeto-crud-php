<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Drink;
use App\Domain\Repositories\DrinkRepositoryInterface;
use App\Infrastructure\Database\Database;
use PDO;
use PDOException;

class DrinkRepository implements DrinkRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(Drink $drink): Drink
    {
        $sql = "INSERT INTO drinks (user_id, consumed_at, quantity)
                VALUES (:user_id, :consumed_at, :quantity)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $drink->getUserId(),
            ':consumed_at' => $drink->getConsumedAt()->format('Y-m-d H:i:s'),
            ':quantity' => $drink->getQuantity()
        ]);

        $drink->setId((int) $this->db->lastInsertId());
        return $drink;
    }

    public function findByUserId(int $userId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM drinks WHERE user_id = :user_id
                ORDER BY consumed_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->hydrate($data);
        }

        return $results;
    }

    public function findByUserIdAndDate(int $userId, \DateTime $date): array
    {
        $startDate = $date->format('Y-m-d 00:00:00');
        $endDate = $date->format('Y-m-d 23:59:59');

        $sql = "SELECT * FROM drinks
                WHERE user_id = :user_id
                AND consumed_at BETWEEN :start_date AND :end_date
                ORDER BY consumed_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $results = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $this->hydrate($data);
        }

        return $results;
    }

    public function getTotalCountByUserId(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM drinks WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function getDailyRanking(\DateTime $date): array
    {
        $startDate = $date->format('Y-m-d 00:00:00');
        $endDate = $date->format('Y-m-d 23:59:59');

        $sql = "SELECT u.id as user_id, u.name, u.email,
                       COALESCE(SUM(d.quantity), 0) as total_drinks
                FROM users u
                LEFT JOIN drinks d ON u.id = d.user_id
                    AND d.consumed_at BETWEEN :start_date AND :end_date
                GROUP BY u.id, u.name, u.email
                ORDER BY total_drinks DESC, u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeriodRanking(\DateTime $startDate, \DateTime $endDate): array
    {
        $startDateStr = $startDate->format('Y-m-d 00:00:00');
        $endDateStr = $endDate->format('Y-m-d 23:59:59');

        $sql = "SELECT u.id as user_id, u.name, u.email,
                       COALESCE(SUM(d.quantity), 0) as total_drinks
                FROM users u
                LEFT JOIN drinks d ON u.id = d.user_id
                    AND d.consumed_at BETWEEN :start_date AND :end_date
                GROUP BY u.id, u.name, u.email
                ORDER BY total_drinks DESC, u.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDateStr,
            ':end_date' => $endDateStr
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hydrate(array $data): Drink
    {
        return new Drink(
            (int) $data['user_id'],
            new \DateTime($data['consumed_at']),
            (int) $data['quantity'],
            (int) $data['id']
        );
    }
}

