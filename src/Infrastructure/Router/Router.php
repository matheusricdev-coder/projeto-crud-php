<?php

namespace App\Infrastructure\Router;

use App\Infrastructure\Controllers\UserController;
use App\Infrastructure\Controllers\AuthController;
use App\Infrastructure\Middleware\AuthMiddleware;
use App\Application\Exceptions\NotFoundException;

class Router
{
    private array $routes = [];
    private AuthMiddleware $authMiddleware;

    public function __construct(AuthMiddleware $authMiddleware)
    {
        $this->authMiddleware = $authMiddleware;
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        // Public routes
        $this->addRoute('POST', '/users/', [UserController::class, 'create']);
        $this->addRoute('POST', '/login', [AuthController::class, 'login']);
        
        // User routes (authenticated)
        $this->addRoute('GET', '/users/', [UserController::class, 'getAll'], true);
        $this->addRoute('GET', '/users/:iduser', [UserController::class, 'getById'], true);
        $this->addRoute('PUT', '/users/:iduser', [UserController::class, 'update'], true);
        $this->addRoute('DELETE', '/users/:iduser', [UserController::class, 'delete'], true);
        $this->addRoute('POST', '/users/:iduser/drink', [UserController::class, 'incrementDrink'], true);
        
        // Optional features (authenticated)
        $this->addRoute('GET', '/users/:iduser/drinks/daily', ['App\\Infrastructure\\Controllers\\DrinkController', 'getUserDailyDrinks'], true);
        $this->addRoute('GET', '/drinks/ranking', ['App\\Infrastructure\\Controllers\\DrinkController', 'getRanking'], true);
    }

    public function addRoute(string $method, string $path, array $handler, bool $requiresAuth = false): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'requiresAuth' => $requiresAuth
        ];
    }

    public function handleRequest(): void
    {
        $method = $this->getRequestMethod();
        $path = $this->getRequestPath();

        // Handle OPTIONS requests for CORS
        if ($method === 'OPTIONS') {
            http_response_code(200);
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            exit;
        }

        // Set CORS headers for all responses
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Normalize path
        $path = $this->normalizePath($path);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $pathParams = $this->extractPathParams($route['path'], $path);

                if ($route['requiresAuth']) {
                    try {
                        $pathParams = $this->authMiddleware->handle($pathParams);
                    } catch (\Exception $e) {
                        $this->sendJsonError($e->getMessage(), $e->getCode() ?: 401);
                    }
                }

                $this->callHandler($route['handler'], $pathParams);
                return;
            }
        }

        $this->sendJsonError('Route not found', 404);
    }
    
    private function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    private function getRequestPath(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }
    
    private function normalizePath(string $path): string
    {
        // Remove trailing slash except for root path
        return $path === '/' ? '/' : rtrim($path, '/');
    }
    
    private function sendJsonError(string $message, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '([a-zA-Z0-9_]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $requestPath) === 1;
    }

    private function extractPathParams(string $routePath, string $requestPath): array
    {
        $params = [];

        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '([a-zA-Z0-9_]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            preg_match_all('/:([a-zA-Z0-9_]+)/', $routePath, $paramNames);

            for ($i = 1; $i < count($matches); $i++) {
                $paramName = $paramNames[1][$i - 1];
                $params[$paramName] = $matches[$i];
            }
        }

        return $params;
    }

    private function callHandler(array $handler, array $pathParams): void
    {
        [$controllerClass, $method] = $handler;

        $controller = $this->createController($controllerClass);

        if (method_exists($controller, 'setPathParams')) {
            $controller->setPathParams($pathParams);
        }

        $controller->$method();
    }

    private function createController(string $controllerClass): object
    {
        if ($controllerClass === UserController::class) {
            return $this->createUserController();
        }

        if ($controllerClass === AuthController::class) {
            return $this->createAuthController();
        }
        
        if ($controllerClass === 'App\\Infrastructure\\Controllers\\DrinkController') {
            return $this->createDrinkController();
        }

        throw new \Exception("Unknown controller: {$controllerClass}");
    }
    
    private function createDrinkController(): \App\Infrastructure\Controllers\DrinkController
    {
        $userRepository = new \App\Infrastructure\Repositories\UserRepository();
        $drinkRepository = new \App\Infrastructure\Repositories\DrinkRepository();
        
        return new \App\Infrastructure\Controllers\DrinkController(
            new \App\Application\UseCases\GetUserDailyDrinksUseCase(
                $drinkRepository,
                $userRepository
            ),
            new \App\Application\UseCases\GetDrinkRankingUseCase(
                $drinkRepository
            )
        );
    }

    private function createUserController(): UserController
    {
        return new UserController(
            new \App\Application\UseCases\CreateUserUseCase(
                new \App\Infrastructure\Repositories\UserRepository()
            ),
            new \App\Application\UseCases\GetUserUseCase(
                new \App\Infrastructure\Repositories\UserRepository()
            ),
            new \App\Application\UseCases\GetUsersListUseCase(
                new \App\Infrastructure\Repositories\UserRepository()
            ),
            new \App\Application\UseCases\UpdateUserUseCase(
                new \App\Infrastructure\Repositories\UserRepository()
            ),
            new \App\Application\UseCases\DeleteUserUseCase(
                new \App\Infrastructure\Repositories\UserRepository()
            ),
            new \App\Application\UseCases\IncrementDrinkUseCase(
                new \App\Infrastructure\Repositories\UserRepository(),
                new \App\Infrastructure\Repositories\DrinkRepository()
            )
        );
    }

    private function createAuthController(): AuthController
    {
        return new AuthController(
            new \App\Application\UseCases\LoginUseCase(
                new \App\Infrastructure\Repositories\UserRepository(),
                new \App\Application\Services\JWTService()
            )
        );
    }
}