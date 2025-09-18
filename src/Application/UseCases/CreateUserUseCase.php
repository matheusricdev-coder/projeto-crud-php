<?php

namespace App\Application\UseCases;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Exceptions\ValidationException;

class CreateUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(array $data): User
    {
        $this->validateInput($data);

        $email = trim($data['email']);
        $name = trim($data['name']);
        $password = $data['password'];

        // Check if user already exists
        if ($this->userRepository->existsByEmail($email)) {
            throw new ValidationException('User with this email already exists', 409);
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }

        // Validate password strength
        if (strlen($password) < 6) {
            throw new ValidationException('Password must be at least 6 characters long');
        }

        // Validate name
        if (strlen($name) < 2) {
            throw new ValidationException('Name must be at least 2 characters long');
        }

        $user = new User($email, $name, $password);
        $user->hashPassword();

        return $this->userRepository->create($user);
    }

    private function validateInput(array $data): void
    {
        $requiredFields = ['email', 'name', 'password'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                throw new ValidationException("Field '{$field}' is required");
            }
        }
    }
}

