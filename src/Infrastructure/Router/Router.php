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
        $this->addRoute('POST', '/users/', [UserController::class, 'create']);
        $this->addRoute('POST', '/login', [AuthController::class, 'login']);
        $this->addRoute('GET', '/users/', [UserController::class, 'getAll'], true);
        $this->addRoute('GET', '/users/:iduser', [UserController::class, 'getById'], true);
        $this->addRoute('PUT', '/users/:iduser', [UserController::class, 'update'], true);
        $this->addRoute('DELETE', '/users/:iduser', [UserController::class, 'delete'], true);
        $this->addRoute('POST', '/users/:iduser/drink', [UserController::class, 'incrementDrink'], true);
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
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $path = rtrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $pathParams = $this->extractPathParams($route['path'], $path);

                if ($route['requiresAuth']) {
                    try {
                        $pathParams = $this->authMiddleware->handle($pathParams);
                    } catch (\Exception $e) {
                        http_response_code($e->getCode());
                        header('Content-Type: application/json');
                        echo json_encode([
                            'error' => true,
                            'message' => $e->getMessage()
                        ]);
                        exit;
                    }
                }

                $this->callHandler($route['handler'], $pathParams);
                return;
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'Route not found'
        ]);
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

        throw new \Exception("Unknown controller: {$controllerClass}");
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