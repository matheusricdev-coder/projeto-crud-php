<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Services\JWTService;
use App\Application\Exceptions\AuthenticationException;
use App\Application\Exceptions\ValidationException;

class LoginUseCase
{
    private UserRepositoryInterface $userRepository;
    private JWTService $jwtService;

    public function __construct(UserRepositoryInterface $userRepository, JWTService $jwtService)
    {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    public function execute(array $data): array
    {
        $this->validateInput($data);

        $email = trim($data['email']);
        $password = $data['password'];

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        if (!$user->verifyPassword($password)) {
            throw new AuthenticationException('Invalid password');
        }

        $token = $this->jwtService->generateToken($user->getId(), $user->getEmail());

        return [
            'token' => $token,
            'iduser' => $user->getId(),
            'name' => $user->getName(),
            'drinkCounter' => $user->getDrinkCounter()
        ];
    }

    private function validateInput(array $data): void
    {
        $requiredFields = ['email', 'password'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                throw new ValidationException("Field '{$field}' is required");
            }
        }
    }
}

