<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class AuthController extends Controller {
    private $db;
    private const MAX_FAILED_ATTEMPTS = 8;

    public function __construct() {
        $this->db = new Database();
    }

    public function showLoginForm() {
        if (Auth::isLoggedIn()) {
            $this->redirectUser();
        }

        $error = $_SESSION['login_error'] ?? '';
        unset($_SESSION['login_error']);

        if (!$error && isset($_GET['error'])) {
            if ($_GET['error'] === 'credentials') {
                $error = 'Credenciales incorrectas.';
            } elseif ($_GET['error'] === 'system') {
                $error = 'No se pudo iniciar sesion por un error del sistema.';
            }
        }

        require_once __DIR__ . '/../../views/auth/login.php';
    }

    private function redirectUser() {
        if (Auth::isEstudiante()) {
            $this->redirect('/estudiante');
        } elseif (Auth::isProfesor()) {
            $this->redirect('/profesor/cursos');
        } elseif (Auth::isAdmin()) {
            $this->redirect('/admin');
        } else {
            $this->redirect('/');
        }
    }

    public function login() {
        if (Auth::isLoggedIn()) {
            $this->redirectUser();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginForm();
            return;
        }

        require_csrf();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $this->db->query("SELECT * FROM usuarios WHERE email = :email");
            $this->db->bind(':email', $email);
            $user = $this->db->single();

            if ($user && (int) ($user->activo ?? 0) !== 1) {
                $this->flash('login_error', 'Tu cuenta esta inactiva. Contacta a soporte.');
                $this->redirect('/login?error=credentials');
            }

            if ($user && (int) ($user->intentos_fallidos ?? 0) >= self::MAX_FAILED_ATTEMPTS) {
                $this->flash('login_error', 'Cuenta bloqueada por demasiados intentos fallidos. Contacta a soporte.');
                $this->redirect('/login?error=credentials');
            }

            if ($user && password_verify($password, $user->password_hash)) {
                $this->db->query("UPDATE usuarios SET intentos_fallidos = 0, ultimo_acceso = NOW() WHERE id = :id");
                $this->db->bind(':id', $user->id);
                $this->db->execute();
                Auth::login($user);
                $this->redirectUser();
            } else {
                if ($user) {
                    $this->db->query("UPDATE usuarios SET intentos_fallidos = LEAST(intentos_fallidos + 1, 9999) WHERE id = :id");
                    $this->db->bind(':id', $user->id);
                    $this->db->execute();
                }
                $this->flash('login_error', 'Credenciales incorrectas.');
                $this->redirect('/login?error=credentials');
            }
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->flash('login_error', 'Error del sistema al autenticar. Revisa la conexion a base de datos.');
            $this->redirect('/login?error=system');
        }
    }

    public function logout() {
        $this->requirePost();
        require_csrf();
        Auth::logout();
        $this->redirect('/login');
    }
}
