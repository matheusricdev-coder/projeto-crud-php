<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\DrinkRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Exceptions\NotFoundException;
use App\Application\Exceptions\ValidationException;

class GetUserDailyDrinksUseCase
{
    private DrinkRepositoryInterface $drinkRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        DrinkRepositoryInterface $drinkRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->drinkRepository = $drinkRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId, ?string $fromDate = null, ?string $toDate = null): array
    {
        // Check if user exists
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('User not found');
        }

        // Validate and parse dates
        $from = null;
        $to = null;

        if ($fromDate) {
            try {
                $from = new \DateTime($fromDate);
            } catch (\Exception $e) {
                throw new ValidationException('Invalid from date format. Use YYYY-MM-DD');
            }
        } else {
            // Default to 30 days ago
            $from = new \DateTime();
            $from->modify('-30 days');
        }

        if ($toDate) {
            try {
                $to = new \DateTime($toDate);
            } catch (\Exception $e) {
                throw new ValidationException('Invalid to date format. Use YYYY-MM-DD');
            }
        } else {
            // Default to today
            $to = new \DateTime();
        }

        // Ensure from date is before to date
        if ($from > $to) {
            throw new ValidationException('From date must be before to date');
        }

        // Get daily consumption history
        return $this->drinkRepository->getUserDailyConsumption($userId, $from, $to);
    }
}
