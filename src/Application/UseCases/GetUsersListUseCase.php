<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\UserRepositoryInterface;

class GetUsersListUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $page = 1, int $limit = 10): array
    {
        // Validate pagination parameters
        if ($page < 1) {
            throw new \App\Application\Exceptions\ValidationException('Page must be greater than 0');
        }
        
        if ($limit < 1 || $limit > 100) {
            throw new \App\Application\Exceptions\ValidationException('Limit must be between 1 and 100');
        }

        $users = $this->userRepository->findAll($page, $limit);
        $totalCount = $this->userRepository->getTotalCount();

        $usersData = array_map(function ($user) {
            return $user->toArrayWithoutPassword();
        }, $users);

        return [
            'data' => $usersData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ];
    }
}
