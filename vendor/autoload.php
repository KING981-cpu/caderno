<?php
// Load .env file into environment variables
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        if ($key === '') continue;
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
        $_SERVER[trim($key)] = trim($value);
    }
}

// Global helper for escaping output
if (!function_exists('e')) {
    function e(?string $text): string
    {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Basic PSR-4 autoloader for App\\ namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize SRE and environment defaults if classes exist
if (file_exists(__DIR__ . '/../app/SRE/Logger.php')) {
    try {
        \App\SRE\Logger::initPath();
    } catch (Throwable $e) {
        // ignore
    }
}

// Set default timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'America/Sao_Paulo');

// Session cookie params (safe defaults)
if (php_sapi_name() !== 'cli') {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
