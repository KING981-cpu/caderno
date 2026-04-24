<?php
$host = 'db';
$db   = 'caderno';
$user = 'root';
$pass = '4uB5@S6SdLz';   

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8", $user, $pass);
    echo "Conectou!";
} catch (PDOException $e) {
    echo $e->getMessage();
}