<?php

namespace App\Infrastructure\Controllers;

use App\Application\UseCases\CreateUserUseCase;
use App\Application\UseCases\GetUserUseCase;
use App\Application\UseCases\GetUsersListUseCase;
use App\Application\UseCases\UpdateUserUseCase;
use App\Application\UseCases\DeleteUserUseCase;
use App\Application\UseCases\IncrementDrinkUseCase;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\AuthenticationException;
use App\Application\Exceptions\NotFoundException;
use App\Application\Exceptions\ForbiddenException;

class UserController extends BaseController
{
    private CreateUserUseCase $createUserUseCase;
    private GetUserUseCase $getUserUseCase;
    private GetUsersListUseCase $getUsersListUseCase;
    private UpdateUserUseCase $updateUserUseCase;
    private DeleteUserUseCase $deleteUserUseCase;
    private IncrementDrinkUseCase $incrementDrinkUseCase;
    private array $pathParams;

    public function __construct(
        CreateUserUseCase $createUserUseCase,
        GetUserUseCase $getUserUseCase,
        GetUsersListUseCase $getUsersListUseCase,
        UpdateUserUseCase $updateUserUseCase,
        DeleteUserUseCase $deleteUserUseCase,
        IncrementDrinkUseCase $incrementDrinkUseCase
    ) {
        $this->createUserUseCase = $createUserUseCase;
        $this->getUserUseCase = $getUserUseCase;
        $this->getUsersListUseCase = $getUsersListUseCase;
        $this->updateUserUseCase = $updateUserUseCase;
        $this->deleteUserUseCase = $deleteUserUseCase;
        $this->incrementDrinkUseCase = $incrementDrinkUseCase;
        $this->pathParams = [];
    }

    public function setPathParams(array $params): void
    {
        $this->pathParams = $params;
    }

    public function create(): void
    {
        try {
            $data = $this->getJsonInput();
            $user = $this->createUserUseCase->execute($data);

            $this->sendSuccessResponse(
                $user->toArrayWithoutPassword(),
                'User created successfully',
                201
            );
        } catch (ValidationException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function getById(): void
    {
        try {
            $userId = (int) $this->pathParams['iduser'];
            $userData = $this->getUserUseCase->execute($userId);

            $this->sendSuccessResponse($userData, 'User retrieved successfully');
        } catch (NotFoundException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function getAll(): void
    {
        try {
            $queryParams = $this->getQueryParams();
            $page = (int) ($queryParams['page'] ?? 1);
            $limit = (int) ($queryParams['limit'] ?? 10);

            $result = $this->getUsersListUseCase->execute($page, $limit);

            $this->sendSuccessResponse($result, 'Users retrieved successfully');
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function update(): void
    {
        try {
            $userId = (int) $this->pathParams['iduser'];
            $data = $this->getJsonInput();
            $authenticatedUserId = $this->pathParams['authenticated_user_id'] ?? 0;

            $userData = $this->updateUserUseCase->execute($userId, $data, $authenticatedUserId);

            $this->sendSuccessResponse($userData, 'User updated successfully');
        } catch (ValidationException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (NotFoundException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (ForbiddenException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function delete(): void
    {
        try {
            $userId = (int) $this->pathParams['iduser'];
            $authenticatedUserId = $this->pathParams['authenticated_user_id'] ?? 0;

            $this->deleteUserUseCase->execute($userId, $authenticatedUserId);

            $this->sendSuccessResponse([], 'User deleted successfully');
        } catch (NotFoundException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (ForbiddenException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function incrementDrink(): void
    {
        try {
            $userId = (int) $this->pathParams['iduser'];

            $userData = $this->incrementDrinkUseCase->execute($userId);

            $this->sendSuccessResponse($userData, 'Drink counter incremented successfully');
        } catch (NotFoundException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }
}

