<?php

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../models/ProfesorPlan.php';

class RegisterController extends Controller {
    public function showRegisterForm() {
        if (Auth::isLoggedIn()) {
            $this->redirect('/');
        }

        require_once __DIR__ . '/../../views/auth/register.php';
    }

    public function register() {
        $this->requirePost();
        require_csrf();

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $accountType = $_POST['account_type'] ?? 'estudiante';
        $idiomaBase = trim($_POST['idioma_base'] ?? 'espanol');
        $idiomaInterfaz = trim($_POST['idioma_interfaz'] ?? 'espanol');

        if ($nombre === '' || $email === '' || $password === '') {
            $this->flash('register_error', 'Completa los campos obligatorios.');
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            $this->flash('register_error', 'La contrasena debe tener al menos 8 caracteres.');
            $this->redirect('/register');
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $this->flash('register_error', 'La contrasena debe incluir letras y numeros.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('register_error', 'Email invalido.');
            $this->redirect('/register');
        }

        if (!in_array($accountType, ['estudiante', 'profesor'], true)) {
            $this->flash('register_error', 'Selecciona un tipo de cuenta valido.');
            $this->redirect('/register');
        }

        if (!array_key_exists($idiomaBase, app_supported_languages())) {
            $this->flash('register_error', 'Selecciona un idioma base valido.');
            $this->redirect('/register');
        }

        if (!array_key_exists($idiomaInterfaz, app_interface_languages())) {
            $this->flash('register_error', 'Selecciona un idioma de interfaz valido.');
            $this->redirect('/register');
        }

        try {
            $db = new Database();

            $db->query('SELECT id FROM usuarios WHERE email = :email');
            $db->bind(':email', $email);
            $existing = $db->single();

            if ($existing) {
                $this->flash('register_error', 'El email ya esta registrado.');
                $this->redirect('/register');
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);

            $db->query('SELECT id FROM instancias LIMIT 1');
            $instancia = $db->single();
            $instanciaId = $instancia ? $instancia->id : 1;

            $db->query(
                'INSERT INTO usuarios (
                    instancia_id, email, password_hash, nombre, apellido,
                    idioma_base, idioma_interfaz,
                    es_estudiante, es_profesor, es_admin_institucion,
                    billing_plan, is_official, vista_default, activo, email_verificado, creado_por, creado_en
                ) VALUES (
                    :instancia_id, :email, :password_hash, :nombre, :apellido,
                    :idioma_base, :idioma_interfaz,
                    :es_estudiante, :es_profesor, :es_admin_institucion,
                    :billing_plan, :is_official, :vista_default, :activo, :email_verificado, :creado_por, NOW()
                )'
            );
            $db->bind(':instancia_id', $instanciaId);
            $db->bind(':email', $email);
            $db->bind(':password_hash', $hash);
            $db->bind(':nombre', $nombre);
            $db->bind(':apellido', $apellido);
            $db->bind(':idioma_base', $idiomaBase);
            $db->bind(':idioma_interfaz', $idiomaInterfaz);
            $db->bind(':es_estudiante', $accountType === 'estudiante' ? 1 : 0);
            $db->bind(':es_profesor', $accountType === 'profesor' ? 1 : 0);
            $db->bind(':es_admin_institucion', 0);
            $db->bind(':billing_plan', ProfesorPlan::normalizarPlan(ProfesorPlan::PLAN_FREE));
            $db->bind(':is_official', 0);
            $db->bind(':vista_default', $accountType === 'profesor' ? 'creador' : 'estudiante');
            $db->bind(':activo', 1);
            $db->bind(':email_verificado', 0);
            $db->bind(':creado_por', null);

            if (!$db->execute()) {
                throw new Exception('No se pudo crear el usuario.');
            }

            $this->flash('register_success', $accountType === 'profesor'
                ? 'Cuenta de profesor creada. Empiezas en plan gratuito con un curso piloto.'
                : 'Registro exitoso. Ahora puedes iniciar sesion.'
            );
            $this->redirect('/login');
        } catch (Throwable $e) {
            error_log('Error en registro [' . $email . '|' . $accountType . ']: ' . $e->getMessage());
            $this->flash('register_error', 'No se pudo completar el registro en este momento. Intenta de nuevo.');
            $this->redirect('/register');
        }
    }
}
