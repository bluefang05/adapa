<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';

class EnmController extends Controller {
    private function isLocalHost() {
        $hostHeader = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
        $hostOnly = explode(':', $hostHeader)[0];
        return in_array($hostOnly, ['localhost', '127.0.0.1'], true);
    }

    private function isInternalLoginEnabled() {
        $envValue = getenv('ALLOW_INTERNAL_LOGIN');
        $envEnabled = is_string($envValue) && in_array(strtolower($envValue), ['1', 'true', 'yes', 'on'], true);
        $constEnabled = defined('ALLOW_INTERNAL_LOGIN') && ALLOW_INTERNAL_LOGIN;
        return $envEnabled || $constEnabled;
    }

    private function canAccessInternalLogin() {
        if ($this->isLocalHost()) {
            return true;
        }

        return $this->isInternalLoginEnabled() && Auth::isAdmin();
    }

    private function getAccounts() {
        return [
            [
                'key' => 'estudiante',
                'label' => 'Estudiante',
                'email' => 'estudiante1@adapa.edu',
                'accent' => 'success',
            ],
            [
                'key' => 'profesor',
                'label' => 'Profesor',
                'email' => 'profesor@adapa.edu',
                'accent' => 'primary',
            ],
            [
                'key' => 'admin',
                'label' => 'Admin',
                'email' => 'admin@adapa.edu',
                'accent' => 'danger',
            ],
        ];
    }

    public function index() {
        if (!$this->canAccessInternalLogin()) {
            $this->abort(404, '404 Not Found');
        }

        header('X-Robots-Tag: noindex, nofollow', true);

        $accounts = $this->getAccounts();
        require_once __DIR__ . '/../../views/shared/enm/index.php';
    }

    public function loginAs() {
        $this->requirePost();
        require_csrf();

        if (!$this->canAccessInternalLogin()) {
            $this->abort(404, '404 Not Found');
        }

        $accountKey = trim($_POST['account_key'] ?? '');
        $accounts = $this->getAccounts();
        $selected = null;
        foreach ($accounts as $account) {
            if ($account['key'] === $accountKey) {
                $selected = $account;
                break;
            }
        }

        if (!$selected) {
            $this->flash('error', 'Cuenta interna no valida.');
            $this->redirect('/enm');
        }

        $db = new Database();
        $db->query("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $db->bind(':email', $selected['email']);
        $user = $db->single();

        if (!$user || (int) ($user->activo ?? 0) !== 1) {
            $this->flash('error', 'No se pudo acceder con la cuenta interna seleccionada.');
            $this->redirect('/enm');
        }

        Auth::login($user);
        $this->redirect($user->es_admin_institucion ? '/admin' : ($user->es_profesor ? '/profesor/cursos' : '/estudiante'));

    }
}
