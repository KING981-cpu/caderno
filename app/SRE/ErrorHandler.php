<?php

namespace App\SRE;

use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(Throwable $exception): void
    {
        Logger::error('Exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ]);

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');

        $isDev = getenv('APP_ENV') === 'development';
        $response = [
            'success' => false,
            'error' => 'An error occurred',
        ];

        if ($isDev) {
            $response['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        echo json_encode($response);
        exit;
    }

    public static function handleError(int $level, string $message, string $file, int $line): void
    {
        Logger::error("PHP Error ({$level}): {$message}", [
            'file' => $file,
            'line' => $line,
        ]);
    }
}
