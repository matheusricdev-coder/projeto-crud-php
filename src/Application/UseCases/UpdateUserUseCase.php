<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\NotFoundException;
use App\Application\Exceptions\ForbiddenException;

class UpdateUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId, array $data, int $authenticatedUserId): array
    {
        // Check if user is trying to update themselves
        if ($userId !== $authenticatedUserId) {
            throw new ForbiddenException('You can only update your own account');
        }

        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        $this->validateInput($data);

        // Update fields if provided
        if (isset($data['email'])) {
            $email = trim($data['email']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException('Invalid email format');
            }

            // Check if email is already taken by another user
            $existingUser = $this->userRepository->findByEmail($email);
            if ($existingUser && $existingUser->getId() !== $userId) {
                throw new ValidationException('Email is already taken by another user');
            }

            $user->setEmail($email);
        }

        if (isset($data['name'])) {
            $name = trim($data['name']);

            if (strlen($name) < 2) {
                throw new ValidationException('Name must be at least 2 characters long');
            }

            $user->setName($name);
        }

        if (isset($data['password'])) {
            $password = $data['password'];

            if (strlen($password) < 6) {
                throw new ValidationException('Password must be at least 6 characters long');
            }

            $user->setPassword($password);
            $user->hashPassword();
        }

        $updatedUser = $this->userRepository->update($user);

        return $updatedUser->toArrayWithoutPassword();
    }

    private function validateInput(array $data): void
    {
        // At least one field must be provided
        if (empty($data)) {
            throw new ValidationException('At least one field must be provided for update');
        }

        // Validate email if provided
        if (isset($data['email']) && empty(trim($data['email']))) {
            throw new ValidationException('Email cannot be empty');
        }

        // Validate name if provided
        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new ValidationException('Name cannot be empty');
        }

        // Validate password if provided
        if (isset($data['password']) && empty($data['password'])) {
            throw new ValidationException('Password cannot be empty');
        }
    }
}

