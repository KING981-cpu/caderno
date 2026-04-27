<?php

namespace App\SRE;

class Logger
{
    private static string $logPath;
    private const LOG_LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
    ];

    public static function initPath(): void
    {
        // Ensure log directory exists
        $logDir = dirname(self::getLogPath());
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
    }

    public static function getLogPath(): string
    {
        if (!isset(self::$logPath)) {
            self::$logPath = getenv('LOG_PATH') ?: '/tmp/caderno.log';
        }
        return self::$logPath;
    }

    public static function setLogPath(string $path): void
    {
        self::$logPath = $path;
        self::initPath();
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    private static function log(string $level, string $message, array $context): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message}";

        if ($contextStr) {
            $logMessage .= " " . $contextStr;
        }

        $logMessage .= PHP_EOL;

        // Use error_log which handles directory creation better
        error_log($logMessage, 3, self::getLogPath());
    }
}

