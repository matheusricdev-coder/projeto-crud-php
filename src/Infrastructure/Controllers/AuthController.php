<?php

namespace App\Infrastructure\Controllers;

use App\Application\UseCases\LoginUseCase;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\AuthenticationException;

class AuthController extends BaseController
{
    private LoginUseCase $loginUseCase;

    public function __construct(LoginUseCase $loginUseCase)
    {
        $this->loginUseCase = $loginUseCase;
    }

    public function login(): void
    {
        try {
            $data = $this->getJsonInput();
            $result = $this->loginUseCase->execute($data);

            $this->sendSuccessResponse($result, 'Login successful');
        } catch (ValidationException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (AuthenticationException $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            $this->sendErrorResponse('Internal server error', 500);
        }
    }
}

