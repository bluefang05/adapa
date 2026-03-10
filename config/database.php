<?php

// Configuracion simple de BD: HOSTING vs LOCAL
// Se detecta el entorno automaticamente basado en el host

$hostHeader = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
$hostOnly = explode(':', $hostHeader)[0];
$isLocal = in_array($hostOnly, ['localhost', '127.0.0.1'], true);

if (!$isLocal) {
    // HOSTING (Produccion)
    // Credenciales para sv92.ifastnet.com / aspierd1_adapa
    define('DB_HOST', 'localhost'); // En hosting compartido suele ser localhost
    define('DB_USER', 'aspierd1_admin');
    define('DB_PASS', 'UnoDosTresCuatroCinco12345...');
    define('DB_NAME', 'aspierd1_adapa');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
} else {
    // LOCAL (XAMPP)
    // Credenciales por defecto de desarrollo
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'adapa');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
}
