<?php

namespace App\SRE;

class Middleware
{
    public static function logRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = self::getClientIp();

        Logger::info("Request: {$method} {$path}", [
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
    }

    public static function logResponse(int $statusCode): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        Logger::info("Response: {$statusCode} {$method} {$path}", [
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    private static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
}
