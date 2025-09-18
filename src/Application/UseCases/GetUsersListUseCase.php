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
        $users = $this->userRepository->findAll($page, $limit);
        $totalCount = $this->userRepository->getTotalCount();

        $usersData = array_map(function ($user) {
            return $user->toArrayWithoutPassword();
        }, $users);

        return [
            'users' => $usersData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ];
    }
}
