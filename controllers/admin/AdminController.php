<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../models/ProfesorPlan.php';
require_once __DIR__ . '/../../models/Curso.php';

class AdminController extends Controller {
    private $db;

    public function __construct() {
        $this->requireRole('admin');
        $this->db = new Database();
    }

    public function index() {
        $instanciaId = Auth::getInstanciaId();

        // Fetch stats for the current instance only so the dashboard matches the detail screens.
        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE instancia_id = :instancia_id");
        $this->db->bind(':instancia_id', $instanciaId);
        $totalUsers = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE instancia_id = :instancia_id AND es_profesor = 1");
        $this->db->bind(':instancia_id', $instanciaId);
        $totalProfessors = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE instancia_id = :instancia_id AND es_estudiante = 1");
        $this->db->bind(':instancia_id', $instanciaId);
        $totalStudents = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM cursos WHERE instancia_id = :instancia_id");
        $this->db->bind(':instancia_id', $instanciaId);
        $totalCourses = $this->db->single()->total;

        // Fetch recent items for the current instance only.
        $this->db->query("SELECT * FROM usuarios WHERE instancia_id = :instancia_id ORDER BY creado_en DESC LIMIT 5");
        $this->db->bind(':instancia_id', $instanciaId);
        $recentUsers = $this->db->resultSet();

        $this->db->query("SELECT * FROM cursos WHERE instancia_id = :instancia_id ORDER BY fecha_creacion DESC LIMIT 5");
        $this->db->bind(':instancia_id', $instanciaId);
        $recentCourses = $this->db->resultSet();

        $this->view('admin/index', [
            'totalUsers' => $totalUsers,
            'totalProfessors' => $totalProfessors,
            'totalStudents' => $totalStudents,
            'totalCourses' => $totalCourses,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses
        ]);
    }

    public function usuarios() {
        $instanciaId = Auth::getInstanciaId();

        // Pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Filter parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';

        // Build Query
        $sql = "SELECT * FROM usuarios WHERE instancia_id = :instancia_id";
        $countSql = "SELECT COUNT(*) as total FROM usuarios WHERE instancia_id = :instancia_id";
        $params = [':instancia_id' => $instanciaId];

        // Add search filter
        if (!empty($search)) {
            $filter = " AND (nombre LIKE :search OR apellido LIKE :search OR email LIKE :search)";
            $sql .= $filter;
            $countSql .= $filter;
            $params[':search'] = "%$search%";
        }

        // Add role filter
        if (!empty($role)) {
            $roleFilter = "";
            if ($role === 'admin') $roleFilter = " AND es_admin_institucion = 1";
            elseif ($role === 'profesor') $roleFilter = " AND es_profesor = 1";
            elseif ($role === 'estudiante') $roleFilter = " AND es_estudiante = 1";
            
            $sql .= $roleFilter;
            $countSql .= $roleFilter;
        }

        // Get total count for pagination
        $this->db->query($countSql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $totalUsers = $this->db->single()->total;
        $totalPages = ceil($totalUsers / $limit);

        // Get paginated results
        $sql .= " ORDER BY creado_en DESC LIMIT :limit OFFSET :offset";
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        $users = $this->db->resultSet();

        $this->view('admin/usuarios', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'role' => $role
        ]);
    }

    public function cursos() {
        $instanciaId = Auth::getInstanciaId();

        $this->db->query("SELECT c.*, u.nombre as profesor_nombre, u.apellido as profesor_apellido, mr.ruta_archivo as portada_url, mr.alt_text as portada_alt
                          FROM cursos c 
                          LEFT JOIN usuarios u ON c.creado_por = u.id 
                          LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
                          WHERE c.instancia_id = :instancia_id
                          ORDER BY c.fecha_creacion DESC");
        $this->db->bind(':instancia_id', $instanciaId);
        $courses = $this->db->resultSet();

        $this->view('admin/cursos', ['courses' => $courses]);
    }

    public function createUsuario() {
        $instanciaId = Auth::getInstanciaId();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_csrf();

            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $rol = trim($_POST['rol'] ?? 'estudiante');
            $billingPlan = ProfesorPlan::normalizarPlan($_POST['billing_plan'] ?? ProfesorPlan::PLAN_FREE);
            $isOfficial = isset($_POST['is_official']) ? 1 : 0;

            if ($nombre === '' || $apellido === '' || $email === '' || $password === '') {
                $this->flash('error', 'Completa nombre, apellido, correo y contrasena.');
                $this->redirect('/admin/usuarios/create');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('error', 'El correo no tiene un formato valido.');
                $this->redirect('/admin/usuarios/create');
            }

            if (strlen($password) < 8) {
                $this->flash('error', 'La contrasena debe tener al menos 8 caracteres.');
                $this->redirect('/admin/usuarios/create');
            }

            $this->db->query("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $this->db->bind(':email', $email);
            if ($this->db->single()) {
                $this->flash('error', 'Ese correo ya esta registrado.');
                $this->redirect('/admin/usuarios/create');
            }

            [$esAdmin, $esProfesor, $esEstudiante] = $this->mapRolFlags($rol);

            $this->db->query("
                INSERT INTO usuarios (
                    instancia_id, email, password_hash, nombre, apellido,
                    idioma_base, idioma_interfaz,
                    es_estudiante, es_profesor, es_admin_institucion,
                    billing_plan, is_official, vista_default, activo, email_verificado, creado_por
                ) VALUES (
                    :instancia_id, :email, :password_hash, :nombre, :apellido,
                    :idioma_base, :idioma_interfaz,
                    :es_estudiante, :es_profesor, :es_admin_institucion,
                    :billing_plan, :is_official, :vista_default, 1, 0, :creado_por
                )
            ");
            $this->db->bind(':instancia_id', $instanciaId);
            $this->db->bind(':email', $email);
            $this->db->bind(':password_hash', password_hash($password, PASSWORD_BCRYPT));
            $this->db->bind(':nombre', $nombre);
            $this->db->bind(':apellido', $apellido);
            $this->db->bind(':idioma_base', $_POST['idioma_base'] ?? 'espanol');
            $this->db->bind(':idioma_interfaz', $_POST['idioma_interfaz'] ?? 'espanol');
            $this->db->bind(':es_estudiante', $esEstudiante);
            $this->db->bind(':es_profesor', $esProfesor);
            $this->db->bind(':es_admin_institucion', $esAdmin);
            $this->db->bind(':billing_plan', $billingPlan);
            $this->db->bind(':is_official', $isOfficial);
            $this->db->bind(':vista_default', 'estudiante');
            $this->db->bind(':creado_por', Auth::getUserId());

            if ($this->db->execute()) {
                $this->flash('success', 'Usuario creado correctamente.');
                $this->redirect('/admin/usuarios');
            }

            $this->flash('error', 'No fue posible crear el usuario.');
            $this->redirect('/admin/usuarios/create');
        }

        $this->view('admin/usuarios_create');
    }

    public function editUsuario($id) {
        $instanciaId = Auth::getInstanciaId();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_csrf();
            $this->db->query("SELECT * FROM usuarios WHERE id = :id AND instancia_id = :instancia_id");
            $this->db->bind(':id', $id);
            $this->db->bind(':instancia_id', $instanciaId);
            $user = $this->db->single();

            if (!$user) {
                $this->redirect('/admin/usuarios');
            }

            // Process form submission
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $email = $_POST['email'];
            $rol = $_POST['rol']; // admin, profesor, estudiante
            $billingPlan = ProfesorPlan::normalizarPlan($_POST['billing_plan'] ?? ProfesorPlan::PLAN_FREE);
            $isOfficial = isset($_POST['is_official']) ? 1 : 0;

            // Basic validation
            if (empty($nombre) || empty($apellido) || empty($email)) {
                $this->flash('error', 'Nombre, apellido y correo son obligatorios.');
                $this->redirect('/admin/usuarios/edit/' . (int) $id);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('error', 'El correo no tiene un formato valido.');
                $this->redirect('/admin/usuarios/edit/' . (int) $id);
            }

            $this->db->query("SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1");
            $this->db->bind(':email', $email);
            $this->db->bind(':id', $id);
            if ($this->db->single()) {
                $this->flash('error', 'Ese correo ya esta en uso por otro usuario.');
                $this->redirect('/admin/usuarios/edit/' . (int) $id);
            }

            // Update user
            $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email";
            
            $password = $_POST['password'] ?? '';
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $this->flash('error', 'La contrasena nueva debe tener al menos 8 caracteres.');
                    $this->redirect('/admin/usuarios/edit/' . (int) $id);
                }
                $sql .= ", password_hash = :password";
            }
            
            // Handle role update logic
            [$es_admin, $es_profesor, $es_estudiante] = $this->mapRolFlags($rol);

            $sql .= ", es_admin_institucion = :es_admin, es_profesor = :es_profesor, es_estudiante = :es_estudiante";
            $sql .= ", billing_plan = :billing_plan, is_official = :is_official";
            $sql .= " WHERE id = :id AND instancia_id = :instancia_id";

            $this->db->query($sql);
            $this->db->bind(':nombre', $nombre);
            $this->db->bind(':apellido', $apellido);
            $this->db->bind(':email', $email);
            
            if (!empty($password)) {
                $this->db->bind(':password', password_hash($password, PASSWORD_BCRYPT));
            }
            
            $this->db->bind(':es_admin', $es_admin);
            $this->db->bind(':es_profesor', $es_profesor);
            $this->db->bind(':es_estudiante', $es_estudiante);
            $this->db->bind(':billing_plan', $billingPlan);
            $this->db->bind(':is_official', $isOfficial);
            $this->db->bind(':id', $id);
            $this->db->bind(':instancia_id', $instanciaId);

            if ($this->db->execute()) {
                $this->flash('success', 'Usuario actualizado correctamente.');
                $this->redirect('/admin/usuarios');
            } else {
                $this->flash('error', 'No se pudo actualizar el usuario.');
                $this->redirect('/admin/usuarios/edit/' . (int) $id);
            }

        } else {
            // Show edit form
            $this->db->query("SELECT * FROM usuarios WHERE id = :id AND instancia_id = :instancia_id");
            $this->db->bind(':id', $id);
            $this->db->bind(':instancia_id', $instanciaId);
            $user = $this->db->single();

            if (!$user) {
                $this->redirect('/admin/usuarios');
            }

            $this->view('admin/usuarios_edit', ['user' => $user]);
        }
    }

    public function deleteUsuario($id) {
        $instanciaId = Auth::getInstanciaId();

        $this->requirePost();
        require_csrf();

        // Prevent deleting self
        if ($id == $_SESSION['user_id']) {
            $this->flash('error', 'No puedes eliminar tu propia cuenta.');
            $this->redirect('/admin/usuarios');
        }

        $this->db->query("DELETE FROM usuarios WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':instancia_id', $instanciaId);
        
        if ($this->db->execute()) {
            $this->flash('success', 'Usuario eliminado correctamente.');
            $this->redirect('/admin/usuarios');
        } else {
            $this->flash('error', 'No se pudo eliminar el usuario. Verifica dependencias activas.');
            $this->redirect('/admin/usuarios');
        }
    }

    public function createCurso() {
        $instanciaId = Auth::getInstanciaId();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_csrf();

            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            [$nivelPrincipal, $nivelDesde, $nivelHasta] = $this->normalizarRangoCefr(
                $_POST['nivel_cefr'] ?? '',
                $_POST['nivel_cefr_desde'] ?? '',
                $_POST['nivel_cefr_hasta'] ?? ''
            );

            if ($titulo === '') {
                $this->flash('error', 'El titulo del curso es obligatorio.');
                $this->redirect('/admin/cursos/create');
            }

            $requiereCodigo = isset($_POST['requiere_codigo']) ? 1 : 0;
            $codigoAcceso = trim($_POST['codigo_acceso'] ?? '');
            if ($requiereCodigo && $codigoAcceso === '') {
                $codigoAcceso = (new Curso())->generarCodigoAcceso();
            }

            $this->db->query("
                INSERT INTO cursos (
                    instancia_id, plantilla_pensum_id, creado_por, titulo, descripcion,
                    idioma, idioma_objetivo, idioma_base, idioma_ensenanza,
                    nivel_cefr_desde, nivel_cefr_hasta, nivel_cefr, modalidad,
                    es_publico, requiere_codigo, codigo_acceso, tipo_codigo,
                    inscripcion_abierta, max_estudiantes, estado
                ) VALUES (
                    :instancia_id, NULL, :creado_por, :titulo, :descripcion,
                    :idioma, :idioma_objetivo, :idioma_base, :idioma_ensenanza,
                    :nivel_cefr_desde, :nivel_cefr_hasta, :nivel_cefr, :modalidad,
                    :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo,
                    :inscripcion_abierta, :max_estudiantes, :estado
                )
            ");
            $this->db->bind(':instancia_id', $instanciaId);
            $this->db->bind(':creado_por', Auth::getUserId());
            $this->db->bind(':titulo', $titulo);
            $this->db->bind(':descripcion', $descripcion !== '' ? $descripcion : null);
            $this->db->bind(':idioma', $_POST['idioma_objetivo'] ?? 'ingles');
            $this->db->bind(':idioma_objetivo', $_POST['idioma_objetivo'] ?? 'ingles');
            $this->db->bind(':idioma_base', $_POST['idioma_base'] ?? 'espanol');
            $this->db->bind(':idioma_ensenanza', $_POST['idioma_base'] ?? 'espanol');
            $this->db->bind(':nivel_cefr_desde', $nivelDesde);
            $this->db->bind(':nivel_cefr_hasta', $nivelHasta);
            $this->db->bind(':nivel_cefr', $nivelPrincipal);
            $this->db->bind(':modalidad', $_POST['modalidad'] ?? 'perpetuo');
            $this->db->bind(':es_publico', isset($_POST['es_publico']) ? 1 : 0);
            $this->db->bind(':requiere_codigo', $requiereCodigo);
            $this->db->bind(':codigo_acceso', $requiereCodigo ? $codigoAcceso : null);
            $this->db->bind(':tipo_codigo', $requiereCodigo ? ($_POST['tipo_codigo'] ?? 'unico_curso') : null);
            $this->db->bind(':inscripcion_abierta', isset($_POST['inscripcion_abierta']) ? 1 : 0);
            $this->db->bind(':max_estudiantes', max(0, (int) ($_POST['max_estudiantes'] ?? 0)));
            $this->db->bind(':estado', $_POST['estado'] ?? 'activo');

            if ($this->db->execute()) {
                $this->flash('success', 'Curso creado correctamente.');
                $this->redirect('/admin/cursos');
            }

            $this->flash('error', 'No se pudo crear el curso.');
            $this->redirect('/admin/cursos/create');
        }

        $this->view('admin/cursos_create');
    }

    public function editCurso($id) {
        $instanciaId = Auth::getInstanciaId();
        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect('/admin/cursos');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_csrf();
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            [$nivelPrincipal, $nivelDesde, $nivelHasta] = $this->normalizarRangoCefr(
                $_POST['nivel_cefr'] ?? '',
                $_POST['nivel_cefr_desde'] ?? '',
                $_POST['nivel_cefr_hasta'] ?? ''
            );

            if ($titulo === '') {
                $this->flash('error', 'El titulo del curso es obligatorio.');
                $this->redirect('/admin/cursos/edit/' . (int) $id);
            }

            $requiereCodigo = isset($_POST['requiere_codigo']) ? 1 : 0;
            $codigoAcceso = trim($_POST['codigo_acceso'] ?? '');
            if ($requiereCodigo && $codigoAcceso === '') {
                $codigoAcceso = (new Curso())->generarCodigoAcceso();
            }

            $this->db->query("
                UPDATE cursos
                SET
                    titulo = :titulo,
                    descripcion = :descripcion,
                    idioma = :idioma,
                    idioma_objetivo = :idioma_objetivo,
                    idioma_base = :idioma_base,
                    idioma_ensenanza = :idioma_ensenanza,
                    nivel_cefr_desde = :nivel_cefr_desde,
                    nivel_cefr_hasta = :nivel_cefr_hasta,
                    nivel_cefr = :nivel_cefr,
                    modalidad = :modalidad,
                    es_publico = :es_publico,
                    requiere_codigo = :requiere_codigo,
                    codigo_acceso = :codigo_acceso,
                    tipo_codigo = :tipo_codigo,
                    inscripcion_abierta = :inscripcion_abierta,
                    max_estudiantes = :max_estudiantes,
                    estado = :estado
                WHERE id = :id AND instancia_id = :instancia_id
            ");
            $this->db->bind(':titulo', $titulo);
            $this->db->bind(':descripcion', $descripcion !== '' ? $descripcion : null);
            $this->db->bind(':idioma', $_POST['idioma_objetivo'] ?? 'ingles');
            $this->db->bind(':idioma_objetivo', $_POST['idioma_objetivo'] ?? 'ingles');
            $this->db->bind(':idioma_base', $_POST['idioma_base'] ?? 'espanol');
            $this->db->bind(':idioma_ensenanza', $_POST['idioma_base'] ?? 'espanol');
            $this->db->bind(':nivel_cefr_desde', $nivelDesde);
            $this->db->bind(':nivel_cefr_hasta', $nivelHasta);
            $this->db->bind(':nivel_cefr', $nivelPrincipal);
            $this->db->bind(':modalidad', $_POST['modalidad'] ?? 'perpetuo');
            $this->db->bind(':es_publico', isset($_POST['es_publico']) ? 1 : 0);
            $this->db->bind(':requiere_codigo', $requiereCodigo);
            $this->db->bind(':codigo_acceso', $requiereCodigo ? $codigoAcceso : null);
            $this->db->bind(':tipo_codigo', $requiereCodigo ? ($_POST['tipo_codigo'] ?? 'unico_curso') : null);
            $this->db->bind(':inscripcion_abierta', isset($_POST['inscripcion_abierta']) ? 1 : 0);
            $this->db->bind(':max_estudiantes', max(0, (int) ($_POST['max_estudiantes'] ?? 0)));
            $this->db->bind(':estado', $_POST['estado'] ?? 'activo');
            $this->db->bind(':id', (int) $id);
            $this->db->bind(':instancia_id', $instanciaId);

            if ($this->db->execute()) {
                $this->flash('success', 'Curso actualizado correctamente.');
                $this->redirect('/admin/cursos');
            }

            $this->flash('error', 'No se pudo actualizar el curso.');
            $this->redirect('/admin/cursos/edit/' . (int) $id);
        }

        $this->view('admin/cursos_edit', ['course' => $course]);
    }

    public function deleteCurso($id) {
        $instanciaId = Auth::getInstanciaId();

        $this->requirePost();
        require_csrf();

        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect('/admin/cursos');
        }

        $this->db->query("DELETE FROM cursos WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);
        if ($this->db->execute()) {
            $this->flash('success', 'Curso eliminado correctamente.');
            $this->redirect('/admin/cursos');
        }

        $this->flash('error', 'No se pudo eliminar el curso. Verifica si tiene datos asociados.');
        $this->redirect('/admin/cursos');
    }

    private function obtenerCursoInstancia($id, $instanciaId) {
        $this->db->query("
            SELECT c.*, u.nombre AS profesor_nombre, u.apellido AS profesor_apellido
            FROM cursos c
            LEFT JOIN usuarios u ON u.id = c.creado_por
            WHERE c.id = :id AND c.instancia_id = :instancia_id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', (int) $instanciaId);
        return $this->db->single();
    }

    private function mapRolFlags($rol) {
        $esAdmin = 0;
        $esProfesor = 0;
        $esEstudiante = 0;

        if ($rol === 'admin') {
            $esAdmin = 1;
        } elseif ($rol === 'profesor') {
            $esProfesor = 1;
        } else {
            $esEstudiante = 1;
        }

        return [$esAdmin, $esProfesor, $esEstudiante];
    }

    private function normalizarRangoCefr($nivelPrincipal, $nivelDesde, $nivelHasta) {
        $orden = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

        $nivelPrincipal = in_array($nivelPrincipal, $orden, true) ? $nivelPrincipal : 'A1';
        $nivelDesde = in_array($nivelDesde, $orden, true) ? $nivelDesde : $nivelPrincipal;
        $nivelHasta = in_array($nivelHasta, $orden, true) ? $nivelHasta : $nivelDesde;

        if (array_search($nivelDesde, $orden, true) > array_search($nivelHasta, $orden, true)) {
            $nivelHasta = $nivelDesde;
        }

        return [$nivelPrincipal, $nivelDesde, $nivelHasta];
    }
}
