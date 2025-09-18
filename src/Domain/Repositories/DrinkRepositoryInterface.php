<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Drink;

interface DrinkRepositoryInterface
{
    public function create(Drink $drink): Drink;
    public function findByUserId(int $userId, int $page = 1, int $limit = 10): array;
    public function findByUserIdAndDate(int $userId, \DateTime $date): array;
    public function getTotalCountByUserId(int $userId): int;
    public function getUserDailyConsumption(int $userId, \DateTime $startDate, \DateTime $endDate): array;
    public function getDailyRanking(\DateTime $date, int $limit = 10): array;
    public function getPeriodRanking(\DateTime $startDate, \DateTime $endDate, int $limit = 10): array;
}

