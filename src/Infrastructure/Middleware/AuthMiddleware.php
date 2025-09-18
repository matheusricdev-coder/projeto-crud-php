<?php

namespace App\Infrastructure\Middleware;

use App\Application\Services\JWTService;
use App\Application\Exceptions\AuthenticationException;

class AuthMiddleware
{
    private JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(array $pathParams = []): array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($authHeader)) {
            throw new AuthenticationException('Authorization header is required');
        }

        $token = $this->jwtService->extractTokenFromHeader($authHeader);

        if (!$token) {
            throw new AuthenticationException('Invalid authorization header format');
        }

        $payload = $this->jwtService->validateToken($token);

        if (!$payload) {
            throw new AuthenticationException('Invalid or expired token');
        }

        // Add authenticated user info to path params
        $pathParams['authenticated_user_id'] = $payload['user_id'];
        $pathParams['authenticated_user_email'] = $payload['email'];

        return $pathParams;
    }
}

