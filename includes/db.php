<?php
$host = 'localhost';
$db   = 'sistema_tickets';
$user = 'tickets_user';
$pass = 'Ticket123!';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT         => false,
    ]);
} catch (PDOException $e) {
    die("Error en conexion a base de datos: " . $e->getMessage());
}
