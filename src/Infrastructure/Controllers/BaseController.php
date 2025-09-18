<?php

namespace App\Infrastructure\Controllers;

abstract class BaseController
{
    protected function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function sendErrorResponse(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'error' => true,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->sendJsonResponse($response, $statusCode);
    }

    protected function sendSuccessResponse(array $data, string $message = 'Success', int $statusCode = 200): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        $this->sendJsonResponse($response, $statusCode);
    }

    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendErrorResponse('Invalid JSON format', 400);
        }

        return $data ?? [];
    }

    protected function getQueryParams(): array
    {
        return $_GET;
    }

    protected function getPathParam(string $param): ?string
    {
        // This will be handled by the router
        return null;
    }
}

