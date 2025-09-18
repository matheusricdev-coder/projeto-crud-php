<?php

namespace App\Application\UseCases;

use App\Domain\Entities\Drink;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\DrinkRepositoryInterface;
use App\Application\Exceptions\NotFoundException;

class IncrementDrinkUseCase
{
    private UserRepositoryInterface $userRepository;
    private DrinkRepositoryInterface $drinkRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        DrinkRepositoryInterface $drinkRepository
    ) {
        $this->userRepository = $userRepository;
        $this->drinkRepository = $drinkRepository;
    }

    public function execute(int $userId): array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        \App\Infrastructure\Database\Database::beginTransaction();
        
        try {
            // Create a new drink record
            $drink = new Drink($userId);
            $this->drinkRepository->create($drink);

            // Increment user's drink counter
            $user->incrementDrinkCounter();
            $this->userRepository->update($user);

            \App\Infrastructure\Database\Database::commit();
            
            return $user->toArrayWithoutPassword();
        } catch (\Exception $e) {
            \App\Infrastructure\Database\Database::rollback();
            throw $e;
        }
    }
}

