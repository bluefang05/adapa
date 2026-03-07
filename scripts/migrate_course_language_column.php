<?php

require_once __DIR__ . '/../config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    DB_HOST,
    defined('DB_PORT') ? DB_PORT : 3306,
    DB_NAME,
    defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
);

$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec("ALTER TABLE cursos MODIFY idioma VARCHAR(32) NOT NULL");
$pdo->prepare("UPDATE cursos SET idioma = 'japones' WHERE id IN (19, 20)")->execute();

echo json_encode([
    'database' => DB_NAME,
    'host' => DB_HOST,
    'status' => 'ok',
    'message' => 'cursos.idioma ahora usa VARCHAR(32) y cursos japones actualizados',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
