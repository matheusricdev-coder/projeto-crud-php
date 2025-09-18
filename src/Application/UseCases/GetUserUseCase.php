<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Exceptions\NotFoundException;

class GetUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId): array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return $user->toArrayWithoutPassword();
    }
}

