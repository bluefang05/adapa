<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class AuthController extends Controller {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function showLoginForm() {
        if (Auth::isLoggedIn()) {
            $this->redirectUser();
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

            if ($user && password_verify($password, $user->password_hash)) {
                // Login successful
                Auth::login($user);
                $this->redirectUser();
            } else {
                // Login failed
                $this->flash('login_error', 'Credenciales incorrectas.');
                $this->redirect('/login?error=credentials');
            }
        } catch (Exception $e) {
            // Database connection error or other exception
            error_log("Login error: " . $e->getMessage());
            $this->flash('login_error', 'Error del sistema: ' . $e->getMessage());
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
