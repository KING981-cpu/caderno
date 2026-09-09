<?php
$host     = getenv('DB_HOST') 'db.lhixhyrgxmtpwhefogne.supabase.co';
$port     = getenv('DB_PORT') ?: '5432';
$dbname   = getenv('DB_NAME') ?: 'postgres';
$user     = getenv('DB_USER') 'postgres';
$password = getenv('DB_PASSWORD') '32806911mKm@';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "error" => $e->getMessage()]));
}
?>
