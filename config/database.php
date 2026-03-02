<?php

// Detección automática de entorno (Local vs Producción)
// Si estamos en localhost (XAMPP), usamos credenciales locales
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'adapa');
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Credenciales para el Hosting Compartido (Producción)
    // REEMPLAZA ESTOS VALORES CON LOS DE TU HOSTING
    define('DB_HOST', 'localhost');
    define('DB_USER', 'aspierdl_admin'); // Usuario del hosting (según tu error)
    define('DB_PASS', 'UnoDosTresCuatroCinco12345...'); // Pon aquí tu contraseña real
    define('DB_NAME', 'aspierdl_adapa'); // Nombre de la DB en el hosting
    define('DB_PORT', 3306);
    define('DB_CHARSET', 'utf8mb4');
}

