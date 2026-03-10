<?php

// Asegurar configuración de sesión antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    // Si ya estamos en un contexto donde la sesión se inició (como dev/index.php), no hacemos nada
    // Si no, iniciamos con la configuración estándar
    $secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    
    // Configuración para evitar problemas en hosting compartido
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.cookie_lifetime', 3600);
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');

    if ($secureCookie) {
        ini_set('session.cookie_secure', '1');
    }

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => '/',
            'secure' => $secureCookie,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    
    // Intentar iniciar sesión SOLO si no hay una activa
    if (session_status() === PHP_SESSION_NONE) {
        if (!session_start()) {
             error_log("Fallo al iniciar sesión en Auth.php");
        }
    }
}

class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function login($user) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['instancia_id'] = $user->instancia_id;
        
        // Determine role based on boolean flags
        $role = 'user';
        if ($user->es_admin_institucion) {
            $role = 'admin';
        } elseif ($user->es_profesor) {
            $role = 'profesor';
        } elseif ($user->es_estudiante) {
            $role = 'estudiante';
        }
        $_SESSION['user_role'] = $role;

        // Set user name directly from the user object
        $_SESSION['user_name'] = $user->nombre . ' ' . $user->apellido;
        $_SESSION['billing_plan'] = $user->billing_plan ?? 'free';
        $_SESSION['is_official'] = !empty($user->is_official) ? 1 : 0;
        $_SESSION['user_base_language'] = $user->idioma_base ?? 'espanol';
        $_SESSION['user_interface_language'] = $user->idioma_interfaz ?? 'espanol';
    }

    public static function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_unset();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getInstanciaId() {
        return $_SESSION['instancia_id'] ?? null;
    }

    public static function getUserName() {
        return $_SESSION['user_name'] ?? 'Usuario';
    }

    public static function getUserRole() {
        return $_SESSION['user_role'] ?? 'user';
    }

    public static function getBillingPlan() {
        return $_SESSION['billing_plan'] ?? 'free';
    }

    public static function isOfficial() {
        return !empty($_SESSION['is_official']);
    }

    public static function hasRole($role) {
        return self::getUserRole() === $role;
    }

    public static function getUserBaseLanguage() {
        return $_SESSION['user_base_language'] ?? 'espanol';
    }

    public static function getUserInterfaceLanguage() {
        return $_SESSION['user_interface_language'] ?? 'espanol';
    }

    public static function isProfesor() {
        return self::hasRole('profesor');
    }

    public static function isEstudiante() {
        return self::hasRole('estudiante');
    }

    public static function isAdmin() {
        return self::hasRole('admin');
    }
}
