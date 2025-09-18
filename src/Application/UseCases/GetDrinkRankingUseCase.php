<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\DrinkRepositoryInterface;
use App\Application\Exceptions\ValidationException;

class GetDrinkRankingUseCase
{
    private DrinkRepositoryInterface $drinkRepository;

    public function __construct(DrinkRepositoryInterface $drinkRepository)
    {
        $this->drinkRepository = $drinkRepository;
    }

    public function execute(?string $date = null, ?int $days = null, int $limit = 10): array
    {
        // Validate inputs
        if ($date && $days) {
            throw new ValidationException('Cannot specify both date and days parameters');
        }

        if ($limit < 1 || $limit > 100) {
            throw new ValidationException('Limit must be between 1 and 100');
        }

        // Get ranking for a specific date
        if ($date) {
            try {
                $dateObj = new \DateTime($date);
            } catch (\Exception $e) {
                throw new ValidationException('Invalid date format. Use YYYY-MM-DD');
            }

            return $this->drinkRepository->getDailyRanking($dateObj, $limit);
        }

        // Get ranking for last X days
        if ($days) {
            if ($days < 1 || $days > 365) {
                throw new ValidationException('Days must be between 1 and 365');
            }

            $endDate = new \DateTime();
            $startDate = new \DateTime();
            $startDate->modify("-{$days} days");

            return $this->drinkRepository->getPeriodRanking($startDate, $endDate, $limit);
        }

        // Default to today's ranking
        $today = new \DateTime();
        return $this->drinkRepository->getDailyRanking($today, $limit);
    }
}
