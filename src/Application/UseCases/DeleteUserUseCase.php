<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Exceptions\NotFoundException;
use App\Application\Exceptions\ForbiddenException;

class DeleteUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId, int $authenticatedUserId): bool
    {
        // Check if user is trying to delete themselves
        if ($userId !== $authenticatedUserId) {
            throw new ForbiddenException('You can only delete your own account');
        }

        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        return $this->userRepository->delete($userId);
    }
}

