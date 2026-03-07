<?php

// Configuracion de BD con prioridad para variables de entorno.
// Esto evita depender de dominios hardcodeados al pasar a shared hosting.

$hostHeader = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
$hostOnly = explode(':', $hostHeader)[0];
$isLocal = in_array($hostOnly, ['localhost', '127.0.0.1'], true);

$dbHost = getenv('DB_HOST');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$dbName = getenv('DB_NAME');
$dbPort = getenv('DB_PORT');
$dbCharset = getenv('DB_CHARSET');
$hostingConfigFile = __DIR__ . '/database.hosting.php';
$targetConfigFile = __DIR__ . '/database.target.php';
$dbTarget = getenv('APP_DB_TARGET');

if ($dbTarget === false && file_exists($targetConfigFile)) {
    $targetConfig = require $targetConfigFile;
    if (is_string($targetConfig)) {
        $dbTarget = $targetConfig;
    }
}

$dbTarget = strtolower(trim((string) ($dbTarget !== false ? $dbTarget : 'auto')));
if (!in_array($dbTarget, ['auto', 'local', 'remote'], true)) {
    $dbTarget = 'auto';
}

if (file_exists($hostingConfigFile) && ($dbTarget === 'remote' || ($dbTarget === 'auto' && !$isLocal))) {
    $hostingConfig = require $hostingConfigFile;
    if (is_array($hostingConfig)) {
        $dbHost = $dbHost !== false ? $dbHost : ($hostingConfig['host'] ?? false);
        $dbUser = $dbUser !== false ? $dbUser : ($hostingConfig['user'] ?? false);
        $dbPass = $dbPass !== false ? $dbPass : ($hostingConfig['pass'] ?? false);
        $dbName = $dbName !== false ? $dbName : ($hostingConfig['name'] ?? false);
        $dbPort = $dbPort !== false ? $dbPort : ($hostingConfig['port'] ?? false);
        $dbCharset = $dbCharset !== false ? $dbCharset : ($hostingConfig['charset'] ?? false);
    }
}

if ($dbHost !== false && $dbUser !== false && $dbName !== false) {
    define('DB_HOST', $dbHost);
    define('DB_USER', $dbUser);
    define('DB_PASS', $dbPass !== false ? $dbPass : '');
    define('DB_NAME', $dbName);
    define('DB_PORT', (int) ($dbPort !== false ? $dbPort : 3306));
    define('DB_CHARSET', $dbCharset !== false ? $dbCharset : 'utf8mb4');
} elseif ($isLocal || $dbTarget === 'local') {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'adapa');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Produccion sin secretos embebidos.
    // Configura DB_* como variables de entorno o crea config/database.hosting.php.
    define('DB_HOST', 'localhost');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_NAME', '');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
    error_log('DB config missing in production: set env vars DB_HOST/DB_USER/DB_PASS/DB_NAME or provide config/database.hosting.php');
}
