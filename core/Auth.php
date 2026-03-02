<?php

// Asegurar configuración de sesión antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    // Configuración para evitar problemas en hosting compartido
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.cookie_lifetime', 3600);
    
    // Intentar iniciar sesión
    if (!session_start()) {
        // Fallback si falla el inicio normal (común en algunos hostings)
        error_log("Fallo al iniciar sesión en Auth.php");
    }
}

class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function login($user) {
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
    }

    public static function logout() {
        session_unset();
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
