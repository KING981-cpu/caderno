<?php
require_once __DIR__ . '/vendor/autoload.php';
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'caderno';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Erro ao conectar ao banco: ' . $e->getMessage());
    http_response_code(500);
    exit('Erro ao conectar ao banco.');
}
?>
