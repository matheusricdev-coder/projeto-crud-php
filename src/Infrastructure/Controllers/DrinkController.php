<?php

namespace App\Infrastructure\Controllers;

use App\Application\UseCases\GetUserDailyDrinksUseCase;
use App\Application\UseCases\GetDrinkRankingUseCase;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\NotFoundException;

class DrinkController extends BaseController
{
    private GetUserDailyDrinksUseCase $getUserDailyDrinksUseCase;
    private GetDrinkRankingUseCase $getDrinkRankingUseCase;
    private array $pathParams;

    public function __construct(
        GetUserDailyDrinksUseCase $getUserDailyDrinksUseCase,
        GetDrinkRankingUseCase $getDrinkRankingUseCase
    ) {
        $this->getUserDailyDrinksUseCase = $getUserDailyDrinksUseCase;
        $this->getDrinkRankingUseCase = $getDrinkRankingUseCase;
        $this->pathParams = [];
    }

    public function setPathParams(array $params): void
    {
        $this->pathParams = $params;
    }

    public function getUserDailyDrinks(): void
    {
        try {
            $userId = (int) $this->pathParams['iduser'];
            $queryParams = $this->getQueryParams();
            $fromDate = $queryParams['from'] ?? null;
            $toDate = $queryParams['to'] ?? null;

            $result = $this->getUserDailyDrinksUseCase->execute($userId, $fromDate, $toDate);

            $this->sendSuccessResponse([
                'user_id' => $userId,
                'history' => $result
            ], 'Daily drinks history retrieved successfully');
        } catch (ValidationException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (NotFoundException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }

    public function getRanking(): void
    {
        try {
            $queryParams = $this->getQueryParams();
            $date = $queryParams['date'] ?? null;
            $days = isset($queryParams['days']) ? (int) $queryParams['days'] : null;
            $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 10;

            $result = $this->getDrinkRankingUseCase->execute($date, $days, $limit);

            $this->sendSuccessResponse([
                'ranking' => $result,
                'parameters' => [
                    'date' => $date,
                    'days' => $days,
                    'limit' => $limit
                ]
            ], 'Drink ranking retrieved successfully');
        } catch (ValidationException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }
}
