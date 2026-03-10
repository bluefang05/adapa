<?php

declare(strict_types=1);

/**
 * Helper class to manage database connections (Local & Remote)
 * Centralizes configuration loading and PDO instantiation.
 */
class RemoteDB {
    
    private static ?array $remoteConfig = null;
    private static ?array $localConfig = null;

    /**
     * Get local database configuration
     */
    public static function getLocalConfig(): array {
        if (self::$localConfig === null) {
            // Default XAMPP/Local settings
            self::$localConfig = [
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => '',
                'name' => 'adapa',
                'port' => 3306,
                'charset' => 'utf8mb4',
                'label' => 'local'
            ];
        }
        return self::$localConfig;
    }

    /**
     * Get remote database configuration from config/database.hosting.php
     */
    public static function getRemoteConfig(): array {
        if (self::$remoteConfig === null) {
            $path = __DIR__ . '/../config/database.hosting.php';
            
            if (!file_exists($path)) {
                throw new RuntimeException("Remote config file not found: $path");
            }

            $config = require $path;
            
            if (!is_array($config)) {
                throw new RuntimeException("Remote config file must return an array.");
            }

            self::$remoteConfig = [
                'host' => $config['host'] ?? '',
                'user' => $config['user'] ?? '',
                'pass' => $config['pass'] ?? '',
                'name' => $config['name'] ?? '',
                'port' => (int)($config['port'] ?? 3306),
                'charset' => $config['charset'] ?? 'utf8mb4',
                'label' => 'remote'
            ];
        }
        return self::$remoteConfig;
    }

    /**
     * Create a PDO connection
     * 
     * @param array $config Database configuration array
     * @return PDO
     */
    public static function connect(array $config): PDO {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        try {
            $pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5 // 5 seconds timeout for remote connections
            ]);
            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException("Connection failed to {$config['label']} DB: " . $e->getMessage());
        }
    }

    /**
     * Connect to Local DB
     */
    public static function connectLocal(): PDO {
        return self::connect(self::getLocalConfig());
    }

    /**
     * Connect to Remote DB
     */
    public static function connectRemote(): PDO {
        return self::connect(self::getRemoteConfig());
    }
}
