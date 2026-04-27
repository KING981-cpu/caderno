<?php

namespace App\SRE;

use App\Core\Database;

class HealthCheck
{
    public static function check(): array
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'services' => [
                'database' => self::checkDatabase(),
            ],
        ];

        $anyUnhealthy = array_any(
            array_values($status['services']),
            fn($s) => $s['status'] !== 'healthy'
        );

        if ($anyUnhealthy) {
            $status['status'] = 'degraded';
        }

        return $status;
    }

    private static function checkDatabase(): array
    {
        try {
            $pdo = Database::getInstance();
            $pdo->query('SELECT 1');

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }
}

if (!function_exists('array_any')) {
    function array_any(array $array, callable $callback): bool
    {
        foreach ($array as $item) {
            if ($callback($item)) {
                return true;
            }
        }
        return false;
    }
}
