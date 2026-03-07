<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../models/ProfesorPlan.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Teoria.php';
require_once __DIR__ . '/../../models/Actividad.php';

class AdminController extends Controller {
    private $db;
    private $cursoModel;
    private $leccionModel;
    private $teoriaModel;
    private $actividadModel;

    public function __construct() {
        $this->requireRole('admin');
        $this->db = new Database();
        $this->cursoModel = new Curso();
        $this->leccionModel = new Leccion();
        $this->teoriaModel = new Teoria();
        $this->actividadModel = new Actividad();
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

        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE instancia_id = :instancia_id AND activo = 0");
        $this->db->bind(':instancia_id', $instanciaId);
        $inactiveUsers = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM cursos WHERE instancia_id = :instancia_id AND es_publico = 1");
        $this->db->bind(':instancia_id', $instanciaId);
        $publicCourses = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM lesson_issue_reports WHERE instancia_id = :instancia_id AND status <> 'resuelto'");
        $this->db->bind(':instancia_id', $instanciaId);
        $openTickets = $this->db->single()->total;

        // Fetch recent items for the current instance only.
        $this->db->query("SELECT * FROM usuarios WHERE instancia_id = :instancia_id ORDER BY creado_en DESC LIMIT 5");
        $this->db->bind(':instancia_id', $instanciaId);
        $recentUsers = $this->db->resultSet();

        $this->db->query("SELECT * FROM cursos WHERE instancia_id = :instancia_id ORDER BY fecha_creacion DESC LIMIT 5");
        $this->db->bind(':instancia_id', $instanciaId);
        $recentCourses = $this->db->resultSet();

        $this->db->query("
            SELECT
                u.id,
                u.nombre,
                u.apellido,
                u.email,
                (
                    SELECT COUNT(*)
                    FROM cursos c
                    WHERE c.creado_por = u.id
                ) AS total_cursos,
                (
                    SELECT COUNT(DISTINCT i.estudiante_id)
                    FROM inscripciones i
                    INNER JOIN cursos c ON c.id = i.curso_id
                    WHERE c.creado_por = u.id
                ) AS total_estudiantes,
                (
                    SELECT COUNT(*)
                    FROM lesson_issue_reports lir
                    INNER JOIN cursos c ON c.id = lir.curso_id
                    WHERE c.creado_por = u.id
                      AND lir.status <> 'resuelto'
                ) AS tickets_abiertos
            FROM usuarios u
            WHERE u.instancia_id = :instancia_id
              AND u.es_profesor = 1
            ORDER BY tickets_abiertos DESC, total_estudiantes DESC, total_cursos DESC, u.nombre ASC
            LIMIT 4
        ");
        $this->db->bind(':instancia_id', $instanciaId);
        $teacherHotspots = $this->db->resultSet();

        $this->db->query("
            SELECT
                lir.id,
                lir.status,
                lir.issue_type,
                lir.context_type,
                lir.created_at,
                u.nombre,
                u.apellido,
                u.es_profesor,
                u.es_estudiante,
                c.titulo AS curso_titulo
            FROM lesson_issue_reports lir
            INNER JOIN usuarios u ON u.id = lir.usuario_id
            LEFT JOIN cursos c ON c.id = lir.curso_id
            WHERE lir.instancia_id = :instancia_id
            ORDER BY lir.created_at DESC
            LIMIT 5
        ");
        $this->db->bind(':instancia_id', $instanciaId);
        $recentTickets = $this->db->resultSet();

        $recentAdminActivity = $this->obtenerActividadAdmin($instanciaId, 6);

        $this->view('admin/index', [
            'totalUsers' => $totalUsers,
            'totalProfessors' => $totalProfessors,
            'totalStudents' => $totalStudents,
            'totalCourses' => $totalCourses,
            'inactiveUsers' => $inactiveUsers,
            'publicCourses' => $publicCourses,
            'openTickets' => $openTickets,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses,
            'teacherHotspots' => $teacherHotspots,
            'recentTickets' => $recentTickets,
            'recentAdminActivity' => $recentAdminActivity,
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

    public function profesores() {
        $instanciaId = Auth::getInstanciaId();
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $load = trim((string) ($_GET['load'] ?? ''));

        $this->db->query("
            SELECT
                u.*,
                (
                    SELECT COUNT(*)
                    FROM cursos c
                    WHERE c.creado_por = u.id
                ) AS total_cursos,
                (
                    SELECT COUNT(*)
                    FROM cursos c
                    WHERE c.creado_por = u.id
                      AND c.es_publico = 1
                ) AS cursos_publicos,
                (
                    SELECT COUNT(DISTINCT i.estudiante_id)
                    FROM inscripciones i
                    INNER JOIN cursos c ON c.id = i.curso_id
                    WHERE c.creado_por = u.id
                ) AS total_estudiantes,
                (
                    SELECT COUNT(*)
                    FROM actividades a
                    INNER JOIN lecciones l ON l.id = a.leccion_id
                    INNER JOIN cursos c ON c.id = l.curso_id
                    WHERE c.creado_por = u.id
                ) AS total_actividades,
                (
                    SELECT COUNT(*)
                    FROM lesson_issue_reports lir
                    WHERE lir.usuario_id = u.id
                ) AS tickets_docente,
                (
                    SELECT COUNT(*)
                    FROM lesson_issue_reports lir
                    INNER JOIN cursos c ON c.id = lir.curso_id
                    WHERE c.creado_por = u.id
                ) AS tickets_cursos
                ,
                (
                    SELECT COUNT(*)
                    FROM lesson_issue_reports lir
                    INNER JOIN cursos c ON c.id = lir.curso_id
                    WHERE c.creado_por = u.id
                      AND lir.status <> 'resuelto'
                ) AS tickets_cursos_abiertos,
                (
                    SELECT COUNT(*)
                    FROM lesson_issue_reports lir
                    WHERE lir.usuario_id = u.id
                      AND lir.status <> 'resuelto'
                ) AS tickets_docente_abiertos
            FROM usuarios u
            WHERE u.instancia_id = :instancia_id
              AND u.es_profesor = 1
            ORDER BY u.activo DESC, u.nombre ASC, u.apellido ASC
        ");
        $this->db->bind(':instancia_id', $instanciaId);
        $teachers = $this->db->resultSet();

        if ($search !== '' || $status !== '' || $load !== '') {
            $teachers = array_values(array_filter($teachers, function ($teacher) use ($search, $status, $load) {
                if ($search !== '') {
                    $normalize = function ($value) {
                        $value = (string) $value;
                        return function_exists('mb_strtolower')
                            ? mb_strtolower($value, 'UTF-8')
                            : strtolower($value);
                    };
                    $needle = $normalize($search);
                    $haystack = $normalize(trim(($teacher->nombre ?? '') . ' ' . ($teacher->apellido ?? '') . ' ' . ($teacher->email ?? '')));
                    if (strpos($haystack, $needle) === false) {
                        return false;
                    }
                }

                if ($status === 'activos' && empty($teacher->activo)) {
                    return false;
                }
                if ($status === 'inactivos' && !empty($teacher->activo)) {
                    return false;
                }

                if ($load === 'con_cursos' && (int) ($teacher->total_cursos ?? 0) <= 0) {
                    return false;
                }
                if ($load === 'con_alumnos' && (int) ($teacher->total_estudiantes ?? 0) <= 0) {
                    return false;
                }
                if ($load === 'con_tickets' && ((int) ($teacher->tickets_cursos_abiertos ?? 0) + (int) ($teacher->tickets_docente_abiertos ?? 0)) <= 0) {
                    return false;
                }

                return true;
            }));
        }

        $this->view('admin/profesores', [
            'teachers' => $teachers,
            'search' => $search,
            'status' => $status,
            'load' => $load,
        ]);
    }

    public function cursos() {
        $instanciaId = Auth::getInstanciaId();
        $teacherFilter = (int) ($_GET['teacher'] ?? 0);
        $visibilityFilter = trim((string) ($_GET['publico'] ?? ''));
        $estadoFilter = trim((string) ($_GET['estado'] ?? ''));

        $sql = "
            SELECT c.*, u.nombre as profesor_nombre, u.apellido as profesor_apellido, mr.ruta_archivo as portada_url, mr.alt_text as portada_alt
            FROM cursos c
            LEFT JOIN usuarios u ON c.creado_por = u.id
            LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
            WHERE c.instancia_id = :instancia_id
        ";
        $params = [':instancia_id' => $instanciaId];

        if ($teacherFilter > 0) {
            $sql .= " AND c.creado_por = :teacher_id";
            $params[':teacher_id'] = $teacherFilter;
        }
        if ($visibilityFilter === 'publico') {
            $sql .= " AND c.es_publico = 1";
        } elseif ($visibilityFilter === 'privado') {
            $sql .= " AND c.es_publico = 0";
        }
        if ($estadoFilter !== '') {
            $sql .= " AND c.estado = :estado";
            $params[':estado'] = $estadoFilter;
        }

        $sql .= " ORDER BY c.fecha_creacion DESC";

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $courses = $this->db->resultSet();

        $this->view('admin/cursos', [
            'courses' => $courses,
            'teachers' => $this->obtenerResponsablesCurso($instanciaId),
            'teacherFilter' => $teacherFilter,
            'visibilityFilter' => $visibilityFilter,
            'estadoFilter' => $estadoFilter,
        ]);
    }

    public function cursoEstructura($id) {
        $instanciaId = Auth::getInstanciaId();
        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect('/admin/cursos');
        }

        $lecciones = $this->leccionModel->obtenerLeccionesConContenido((int) $course->id);
        foreach ($lecciones as $leccion) {
            $leccion->teorias_detalle = $this->teoriaModel->obtenerTeoriasPorLeccion($leccion->id);
            $leccion->actividades_detalle = $this->actividadModel->obtenerActividadesPorLeccion($leccion->id);
        }

        $this->view('admin/curso_estructura', [
            'course' => $course,
            'lecciones' => $lecciones,
        ]);
    }

    public function tickets() {
        $instanciaId = Auth::getInstanciaId();
        $status = trim((string) ($_GET['status'] ?? ''));
        $context = trim((string) ($_GET['context'] ?? ''));
        $role = trim((string) ($_GET['role'] ?? ''));
        $priority = trim((string) ($_GET['priority'] ?? ''));
        $userId = (int) ($_GET['user_id'] ?? 0);
        $ownerId = (int) ($_GET['owner_id'] ?? 0);

        $sql = "
            SELECT
                lir.*,
                u.nombre,
                u.apellido,
                u.email,
                u.es_admin_institucion,
                u.es_profesor,
                u.es_estudiante,
                c.titulo AS curso_titulo,
                c.creado_por AS curso_owner_id,
                l.titulo AS leccion_titulo,
                a.titulo AS actividad_titulo
            FROM lesson_issue_reports lir
            INNER JOIN usuarios u ON u.id = lir.usuario_id
            LEFT JOIN cursos c ON c.id = lir.curso_id
            LEFT JOIN lecciones l ON l.id = lir.leccion_id
            LEFT JOIN actividades a ON a.id = lir.actividad_id
            WHERE lir.instancia_id = :instancia_id
        ";
        $params = [':instancia_id' => $instanciaId];

        if ($status !== '') {
            $sql .= " AND lir.status = :status";
            $params[':status'] = $status;
        }
        if ($context !== '') {
            $sql .= " AND lir.context_type = :context";
            $params[':context'] = $context;
        }
        if ($role === 'profesor') {
            $sql .= " AND u.es_profesor = 1";
        } elseif ($role === 'estudiante') {
            $sql .= " AND u.es_estudiante = 1";
        }
        if ($userId > 0) {
            $sql .= " AND lir.usuario_id = :user_id";
            $params[':user_id'] = $userId;
        }
        if ($ownerId > 0) {
            $sql .= " AND c.creado_por = :owner_id";
            $params[':owner_id'] = $ownerId;
        }

        $sql .= " ORDER BY lir.created_at DESC";

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $tickets = $this->db->resultSet();
        $tickets = $this->enriquecerTicketsAdmin($tickets);

        if ($priority !== '') {
            $tickets = array_values(array_filter($tickets, function ($ticket) use ($priority) {
                return ($ticket->priority_key ?? '') === $priority;
            }));
        }

        usort($tickets, function ($left, $right) {
            $leftResolved = ($left->status ?? '') === 'resuelto' ? 1 : 0;
            $rightResolved = ($right->status ?? '') === 'resuelto' ? 1 : 0;
            if ($leftResolved !== $rightResolved) {
                return $leftResolved <=> $rightResolved;
            }

            $priorityCompare = (int) ($right->priority_score ?? 0) <=> (int) ($left->priority_score ?? 0);
            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }

            return strtotime((string) ($right->created_at ?? '')) <=> strtotime((string) ($left->created_at ?? ''));
        });

        $ticketPrioritySummary = (object) [
            'alta' => 0,
            'media' => 0,
            'baja' => 0,
            'cerrado' => 0,
        ];
        foreach ($tickets as $ticket) {
            $key = (string) ($ticket->priority_key ?? 'baja');
            if (isset($ticketPrioritySummary->$key)) {
                $ticketPrioritySummary->$key++;
            }
        }

        $this->db->query("
            SELECT
                SUM(CASE WHEN status = 'nuevo' THEN 1 ELSE 0 END) AS nuevos,
                SUM(CASE WHEN status = 'en_revision' THEN 1 ELSE 0 END) AS en_revision,
                SUM(CASE WHEN status = 'resuelto' THEN 1 ELSE 0 END) AS resueltos,
                SUM(CASE WHEN issue_type = 'audio_video' THEN 1 ELSE 0 END) AS audio_video,
                SUM(CASE WHEN issue_type = 'contenido_incorrecto' THEN 1 ELSE 0 END) AS contenido,
                SUM(CASE WHEN context_type = 'actividad' THEN 1 ELSE 0 END) AS actividad,
                SUM(CASE WHEN context_type = 'leccion' THEN 1 ELSE 0 END) AS leccion
            FROM lesson_issue_reports
            WHERE instancia_id = :instancia_id
        ");
        $this->db->bind(':instancia_id', $instanciaId);
        $ticketSummary = $this->db->single();

        $notesByTicket = [];
        if (!empty($tickets)) {
            $notesByTicket = $this->obtenerNotasTickets(array_map(function ($ticket) {
                return (int) $ticket->id;
            }, $tickets));
        }

        $selectedReporter = null;
        if ($userId > 0) {
            $this->db->query("
                SELECT id, nombre, apellido, email
                FROM usuarios
                WHERE id = :id AND instancia_id = :instancia_id
                LIMIT 1
            ");
            $this->db->bind(':id', $userId);
            $this->db->bind(':instancia_id', $instanciaId);
            $selectedReporter = $this->db->single();
        }

        $selectedOwner = null;
        if ($ownerId > 0) {
            $this->db->query("
                SELECT id, nombre, apellido, email
                FROM usuarios
                WHERE id = :id AND instancia_id = :instancia_id
                LIMIT 1
            ");
            $this->db->bind(':id', $ownerId);
            $this->db->bind(':instancia_id', $instanciaId);
            $selectedOwner = $this->db->single();
        }

        $this->view('admin/tickets', [
            'tickets' => $tickets,
            'status' => $status,
            'context' => $context,
            'role' => $role,
            'priority' => $priority,
            'userId' => $userId,
            'ownerId' => $ownerId,
            'selectedReporter' => $selectedReporter,
            'selectedOwner' => $selectedOwner,
            'ticketSummary' => $ticketSummary,
            'ticketPrioritySummary' => $ticketPrioritySummary,
            'notesByTicket' => $notesByTicket,
        ]);
    }

    public function updateTicketStatus($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/tickets');
        $status = trim((string) ($_POST['status'] ?? 'nuevo'));
        $allowed = ['nuevo', 'en_revision', 'resuelto'];
        if (!in_array($status, $allowed, true)) {
            $status = 'nuevo';
        }

        $this->db->query("UPDATE lesson_issue_reports SET status = :status WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);

        if ($this->db->execute()) {
            $this->flash('success', 'Estado del ticket actualizado.');
            $this->registrarActividadAdmin(
                'ticket_status_updated',
                'ticket',
                (int) $id,
                'Actualizo el estado de un ticket a ' . $status . '.',
                ['status' => $status]
            );
        } else {
            $this->flash('error', 'No se pudo actualizar el ticket.');
        }

        $this->redirect($returnTo);
    }

    public function bulkTicketStatus() {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/tickets');
        $status = trim((string) ($_POST['status'] ?? 'nuevo'));
        $ticketIds = $_POST['ticket_ids'] ?? [];
        $allowed = ['nuevo', 'en_revision', 'resuelto'];

        if (!in_array($status, $allowed, true)) {
            $this->flash('error', 'Estado masivo no valido.');
            $this->redirect($returnTo);
        }

        if (!is_array($ticketIds) || empty($ticketIds)) {
            $this->flash('error', 'Selecciona al menos un ticket para aplicar el cambio masivo.');
            $this->redirect($returnTo);
        }

        $ticketIds = array_values(array_unique(array_filter(array_map('intval', $ticketIds))));
        if (empty($ticketIds)) {
            $this->flash('error', 'La seleccion de tickets no es valida.');
            $this->redirect($returnTo);
        }

        $placeholders = [];
        foreach ($ticketIds as $index => $ticketId) {
            $placeholders[] = ':ticket_' . $index;
        }

        $this->db->query("
            UPDATE lesson_issue_reports
            SET status = :status
            WHERE instancia_id = :instancia_id
              AND id IN (" . implode(', ', $placeholders) . ")
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':instancia_id', $instanciaId);
        foreach ($ticketIds as $index => $ticketId) {
            $this->db->bind(':ticket_' . $index, $ticketId);
        }

        if ($this->db->execute()) {
            $affected = $this->db->rowCount();
            $this->registrarActividadAdmin(
                'ticket_bulk_status_updated',
                'ticket',
                null,
                'Actualizo masivamente el estado de tickets a ' . $status . '.',
                ['status' => $status, 'ticket_ids' => $ticketIds, 'affected' => $affected]
            );
            $this->flash('success', 'Se actualizaron ' . $affected . ' tickets.');
        } else {
            $this->flash('error', 'No se pudo aplicar el cambio masivo.');
        }

        $this->redirect($returnTo);
    }

    public function addTicketNote($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/tickets');
        $note = trim((string) ($_POST['note'] ?? ''));

        if ($note === '') {
            $this->flash('error', 'La nota no puede ir vacia.');
            $this->redirect($returnTo);
        }

        $this->db->query("
            SELECT id
            FROM lesson_issue_reports
            WHERE id = :id AND instancia_id = :instancia_id
            LIMIT 1
        ");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);
        $ticket = $this->db->single();
        if (!$ticket) {
            $this->flash('error', 'Ticket no encontrado.');
            $this->redirect($returnTo);
        }

        try {
            $this->db->query("
                INSERT INTO lesson_issue_report_notes (
                    issue_report_id,
                    admin_user_id,
                    note,
                    is_private
                ) VALUES (
                    :issue_report_id,
                    :admin_user_id,
                    :note,
                    1
                )
            ");
            $this->db->bind(':issue_report_id', (int) $id);
            $this->db->bind(':admin_user_id', (int) Auth::getUserId());
            $this->db->bind(':note', $note);
            $this->db->execute();

            $this->registrarActividadAdmin(
                'ticket_note_added',
                'ticket',
                (int) $id,
                'Anadio una nota interna a un ticket.',
                ['note_length' => strlen($note)]
            );
            $this->flash('success', 'Nota interna agregada al ticket.');
        } catch (Exception $e) {
            $this->flash('error', 'No se pudo guardar la nota interna.');
        }

        $this->redirect($returnTo);
    }

    public function actividad() {
        $instanciaId = Auth::getInstanciaId();
        $action = trim((string) ($_GET['action'] ?? ''));
        $target = trim((string) ($_GET['target'] ?? ''));

        $activity = $this->obtenerActividadAdmin($instanciaId, 80, $action, $target);

        $this->view('admin/actividad', [
            'activity' => $activity,
            'action' => $action,
            'target' => $target,
        ]);
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
            $activo = isset($_POST['activo']) ? 1 : 0;
            $emailVerificado = isset($_POST['email_verificado']) ? 1 : 0;

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
                    :billing_plan, :is_official, :vista_default, :activo, :email_verificado, :creado_por
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
            $this->db->bind(':activo', $activo);
            $this->db->bind(':email_verificado', $emailVerificado);
            $this->db->bind(':creado_por', Auth::getUserId());

            if ($this->db->execute()) {
                $this->flash('success', 'Usuario creado correctamente.');
                $this->registrarActividadAdmin(
                    'user_created',
                    'usuario',
                    (int) $this->db->lastInsertId(),
                    'Creo un nuevo usuario en la instancia.',
                    ['rol' => $rol, 'email' => $email]
                );
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
            $activo = isset($_POST['activo']) ? 1 : 0;
            $emailVerificado = isset($_POST['email_verificado']) ? 1 : 0;

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
            $sql .= ", billing_plan = :billing_plan, is_official = :is_official, activo = :activo, email_verificado = :email_verificado";
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
            $this->db->bind(':activo', $activo);
            $this->db->bind(':email_verificado', $emailVerificado);
            $this->db->bind(':id', $id);
            $this->db->bind(':instancia_id', $instanciaId);

            if ($this->db->execute()) {
                $this->flash('success', 'Usuario actualizado correctamente.');
                $this->registrarActividadAdmin(
                    'user_updated',
                    'usuario',
                    (int) $id,
                    'Actualizo un usuario.',
                    ['rol' => $rol, 'email' => $email]
                );
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
            $this->registrarActividadAdmin(
                'user_deleted',
                'usuario',
                (int) $id,
                'Elimino un usuario.',
                []
            );
            $this->redirect('/admin/usuarios');
        } else {
            $this->flash('error', 'No se pudo eliminar el usuario. Verifica dependencias activas.');
            $this->redirect('/admin/usuarios');
        }
    }

    public function toggleUsuarioActivo($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/usuarios');
        if ((int) $id === (int) $_SESSION['user_id']) {
            $this->flash('error', 'No puedes desactivar tu propia cuenta desde aqui.');
            $this->redirect($returnTo);
        }

        $this->db->query("UPDATE usuarios SET activo = CASE WHEN activo = 1 THEN 0 ELSE 1 END WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);

        if ($this->db->execute()) {
            $this->flash('success', 'Estado de acceso del usuario actualizado.');
            $this->registrarActividadAdmin(
                'user_access_toggled',
                'usuario',
                (int) $id,
                'Cambio el acceso de un usuario.',
                []
            );
        } else {
            $this->flash('error', 'No se pudo actualizar el acceso del usuario.');
        }

        $this->redirect($returnTo);
    }

    public function verifyUsuarioEmail($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/usuarios');
        $this->db->query("UPDATE usuarios SET email_verificado = 1 WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);

        if ($this->db->execute()) {
            $this->flash('success', 'Correo marcado como verificado.');
            $this->registrarActividadAdmin(
                'user_email_verified',
                'usuario',
                (int) $id,
                'Marco un correo como verificado.',
                []
            );
        } else {
            $this->flash('error', 'No se pudo verificar el correo.');
        }

        $this->redirect($returnTo);
    }

    public function bulkUsuarioAction() {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/usuarios');
        $action = trim((string) ($_POST['action'] ?? ''));
        $userIds = $this->resolverIdsSeleccionados($_POST['user_ids'] ?? []);

        if (empty($userIds)) {
            $this->flash('error', 'Selecciona al menos un usuario.');
            $this->redirect($returnTo);
        }

        $placeholders = $this->placeholdersDesdeIds('user', $userIds);
        $sql = '';
        $metadata = ['user_ids' => $userIds];

        if ($action === 'activate') {
            $sql = "UPDATE usuarios SET activo = 1 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
        } elseif ($action === 'deactivate') {
            $sql = "UPDATE usuarios SET activo = 0 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ") AND id <> :self_id";
        } elseif ($action === 'verify_email') {
            $sql = "UPDATE usuarios SET email_verificado = 1 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
        } else {
            $this->flash('error', 'Accion masiva de usuarios no valida.');
            $this->redirect($returnTo);
        }

        $this->db->query($sql);
        $this->db->bind(':instancia_id', $instanciaId);
        foreach ($placeholders as $placeholder => $userId) {
            $this->db->bind($placeholder, $userId);
        }
        if ($action === 'deactivate') {
            $this->db->bind(':self_id', (int) Auth::getUserId());
        }

        if ($this->db->execute()) {
            $affected = $this->db->rowCount();
            $this->registrarActividadAdmin(
                'user_bulk_action',
                'usuario',
                null,
                'Aplico una accion masiva sobre usuarios.',
                $metadata + ['action' => $action, 'affected' => $affected]
            );
            $this->flash('success', 'Accion masiva aplicada sobre ' . $affected . ' usuarios.');
        } else {
            $this->flash('error', 'No se pudo aplicar la accion masiva.');
        }

        $this->redirect($returnTo);
    }

    public function createCurso() {
        $instanciaId = Auth::getInstanciaId();
        $selectedTeacherId = $this->resolverResponsableCurso((int) ($_GET['teacher'] ?? 0), $instanciaId, (int) Auth::getUserId());

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            require_csrf();

            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            [$nivelPrincipal, $nivelDesde, $nivelHasta] = $this->normalizarRangoCefr(
                $_POST['nivel_cefr'] ?? '',
                $_POST['nivel_cefr_desde'] ?? '',
                $_POST['nivel_cefr_hasta'] ?? ''
            );

            $creadoPor = $this->resolverResponsableCurso((int) ($_POST['creado_por'] ?? 0), $instanciaId);

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
            $this->db->bind(':creado_por', $creadoPor);
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
                $this->registrarActividadAdmin(
                    'course_created',
                    'curso',
                    (int) $this->db->lastInsertId(),
                    'Creo un curso nuevo.',
                    ['titulo' => $titulo, 'responsable' => $creadoPor]
                );
                $this->redirect('/admin/cursos');
            }

            $this->flash('error', 'No se pudo crear el curso.');
            $this->redirect('/admin/cursos/create');
        }

        $teachers = $this->obtenerResponsablesCurso($instanciaId);
        $this->view('admin/cursos_create', [
            'teachers' => $teachers,
            'selectedTeacherId' => $selectedTeacherId,
        ]);
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
            $creadoPor = $this->resolverResponsableCurso((int) ($_POST['creado_por'] ?? 0), $instanciaId, (int) ($course->creado_por ?? 0));
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
                    creado_por = :creado_por,
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
            $this->db->bind(':creado_por', $creadoPor);
            $this->db->bind(':estado', $_POST['estado'] ?? 'activo');
            $this->db->bind(':id', (int) $id);
            $this->db->bind(':instancia_id', $instanciaId);

            if ($this->db->execute()) {
                $this->flash('success', 'Curso actualizado correctamente.');
                $this->registrarActividadAdmin(
                    'course_updated',
                    'curso',
                    (int) $id,
                    'Actualizo un curso.',
                    ['titulo' => $titulo, 'responsable' => $creadoPor]
                );
                $this->redirect('/admin/cursos');
            }

            $this->flash('error', 'No se pudo actualizar el curso.');
            $this->redirect('/admin/cursos/edit/' . (int) $id);
        }

        $teachers = $this->obtenerResponsablesCurso($instanciaId);
        $this->view('admin/cursos_edit', ['course' => $course, 'teachers' => $teachers]);
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
            $this->registrarActividadAdmin(
                'course_deleted',
                'curso',
                (int) $id,
                'Elimino un curso.',
                []
            );
            $this->redirect('/admin/cursos');
        }

        $this->flash('error', 'No se pudo eliminar el curso. Verifica si tiene datos asociados.');
        $this->redirect('/admin/cursos');
    }

    public function duplicateCurso($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/cursos');
        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect($returnTo);
        }

        $nuevoCursoId = $this->cursoModel->duplicarCurso((int) $id, $instanciaId, Auth::getUserId());
        if (!$nuevoCursoId) {
            $this->flash('error', 'No se pudo duplicar el curso.');
            $this->redirect($returnTo);
        }

        foreach ($this->leccionModel->obtenerLeccionesPorCurso((int) $id) as $leccion) {
            $nuevaLeccionId = $this->leccionModel->duplicarLeccion($leccion->id);
            if (!$nuevaLeccionId) {
                continue;
            }

            $this->leccionModel->actualizarLeccion($nuevaLeccionId, [
                'titulo' => str_replace(' (copia)', '', $leccion->titulo),
                'descripcion' => $leccion->descripcion,
                'orden' => $leccion->orden,
                'duracion_minutos' => $leccion->duracion_minutos,
                'es_obligatoria' => $leccion->es_obligatoria,
                'estado' => 'borrador',
            ]);
            $this->leccionModel->reasignarCurso($nuevaLeccionId, $nuevoCursoId);

            foreach ($this->teoriaModel->obtenerTeoriasPorLeccion($leccion->id) as $teoria) {
                $this->teoriaModel->duplicarTeoria($teoria->id, $nuevaLeccionId, false);
            }

            foreach ($this->actividadModel->obtenerActividadesPorLeccion($leccion->id) as $actividad) {
                $this->actividadModel->duplicarActividad($actividad->id, $nuevaLeccionId, false);
            }
        }

        $this->flash('success', 'Curso duplicado con su estructura completa.');
        $this->registrarActividadAdmin(
            'course_duplicated',
            'curso',
            (int) $nuevoCursoId,
            'Duplico un curso con su estructura.',
            ['source_course_id' => (int) $id]
        );
        $this->redirect($returnTo);
    }

    public function toggleCursoPublico($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/cursos');
        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect($returnTo);
        }

        $this->db->query("UPDATE cursos SET es_publico = CASE WHEN es_publico = 1 THEN 0 ELSE 1 END WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);

        if ($this->db->execute()) {
            $this->flash('success', 'Visibilidad del curso actualizada.');
            $this->registrarActividadAdmin(
                'course_visibility_toggled',
                'curso',
                (int) $id,
                'Cambio la visibilidad de un curso.',
                []
            );
        } else {
            $this->flash('error', 'No se pudo actualizar la visibilidad del curso.');
        }

        $this->redirect($returnTo);
    }

    public function toggleCursoInscripcion($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/cursos');
        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect($returnTo);
        }

        $this->db->query("UPDATE cursos SET inscripcion_abierta = CASE WHEN inscripcion_abierta = 1 THEN 0 ELSE 1 END WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);

        if ($this->db->execute()) {
            $this->flash('success', 'Estado de inscripcion actualizado.');
            $this->registrarActividadAdmin(
                'course_enrollment_toggled',
                'curso',
                (int) $id,
                'Cambio la inscripcion de un curso.',
                []
            );
        } else {
            $this->flash('error', 'No se pudo actualizar la inscripcion del curso.');
        }

        $this->redirect($returnTo);
    }

    public function cycleCursoEstado($id) {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/cursos');
        $course = $this->obtenerCursoInstancia((int) $id, (int) $instanciaId);
        if (!$course) {
            $this->flash('error', 'Curso no encontrado en la instancia.');
            $this->redirect($returnTo);
        }

        $states = ['preparacion', 'activo', 'pausado', 'finalizado', 'archivado'];
        $current = array_search((string) ($course->estado ?? 'preparacion'), $states, true);
        $nextIndex = $current === false ? 0 : (($current + 1) % count($states));
        $nextState = $states[$nextIndex];

        $this->db->query("UPDATE cursos SET estado = :estado WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':estado', $nextState);
        $this->db->bind(':id', (int) $id);
        $this->db->bind(':instancia_id', $instanciaId);

        if ($this->db->execute()) {
            $this->flash('success', 'Estado del curso actualizado a ' . $nextState . '.');
            $this->registrarActividadAdmin(
                'course_state_changed',
                'curso',
                (int) $id,
                'Cambio el estado operativo de un curso.',
                ['estado' => $nextState]
            );
        } else {
            $this->flash('error', 'No se pudo cambiar el estado del curso.');
        }

        $this->redirect($returnTo);
    }

    public function bulkCursoAction() {
        $this->requirePost();
        require_csrf();

        $instanciaId = Auth::getInstanciaId();
        $returnTo = $this->resolverRetornoAdmin('/admin/cursos');
        $action = trim((string) ($_POST['action'] ?? ''));
        $courseIds = $this->resolverIdsSeleccionados($_POST['course_ids'] ?? []);

        if (empty($courseIds)) {
            $this->flash('error', 'Selecciona al menos un curso.');
            $this->redirect($returnTo);
        }

        $placeholders = $this->placeholdersDesdeIds('course', $courseIds);
        $sql = '';
        $params = [':instancia_id' => $instanciaId];

        switch ($action) {
            case 'make_public':
                $sql = "UPDATE cursos SET es_publico = 1 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'make_private':
                $sql = "UPDATE cursos SET es_publico = 0 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'open_enrollment':
                $sql = "UPDATE cursos SET inscripcion_abierta = 1 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'close_enrollment':
                $sql = "UPDATE cursos SET inscripcion_abierta = 0 WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'set_preparacion':
                $sql = "UPDATE cursos SET estado = 'preparacion' WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'set_activo':
                $sql = "UPDATE cursos SET estado = 'activo' WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'set_pausado':
                $sql = "UPDATE cursos SET estado = 'pausado' WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            case 'set_archivado':
                $sql = "UPDATE cursos SET estado = 'archivado' WHERE instancia_id = :instancia_id AND id IN (" . implode(', ', array_keys($placeholders)) . ")";
                break;
            default:
                $this->flash('error', 'Accion masiva de cursos no valida.');
                $this->redirect($returnTo);
        }

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        foreach ($placeholders as $placeholder => $courseId) {
            $this->db->bind($placeholder, $courseId);
        }

        if ($this->db->execute()) {
            $affected = $this->db->rowCount();
            $this->registrarActividadAdmin(
                'course_bulk_action',
                'curso',
                null,
                'Aplico una accion masiva sobre cursos.',
                ['action' => $action, 'course_ids' => $courseIds, 'affected' => $affected]
            );
            $this->flash('success', 'Accion masiva aplicada sobre ' . $affected . ' cursos.');
        } else {
            $this->flash('error', 'No se pudo aplicar la accion masiva a cursos.');
        }

        $this->redirect($returnTo);
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

    private function obtenerResponsablesCurso($instanciaId) {
        $this->db->query("
            SELECT id, nombre, apellido, email, es_admin_institucion, es_profesor
            FROM usuarios
            WHERE instancia_id = :instancia_id
              AND activo = 1
              AND (es_profesor = 1 OR es_admin_institucion = 1)
            ORDER BY es_admin_institucion DESC, nombre ASC, apellido ASC
        ");
        $this->db->bind(':instancia_id', (int) $instanciaId);
        return $this->db->resultSet();
    }

    private function resolverResponsableCurso($requestedId, $instanciaId, $fallbackId = null) {
        if ($requestedId > 0) {
            $this->db->query("
                SELECT id
                FROM usuarios
                WHERE id = :id
                  AND instancia_id = :instancia_id
                  AND activo = 1
                  AND (es_profesor = 1 OR es_admin_institucion = 1)
                LIMIT 1
            ");
            $this->db->bind(':id', (int) $requestedId);
            $this->db->bind(':instancia_id', (int) $instanciaId);
            $user = $this->db->single();
            if ($user) {
                return (int) $user->id;
            }
        }

        if ($fallbackId) {
            return (int) $fallbackId;
        }

        return (int) Auth::getUserId();
    }

    private function obtenerNotasTickets(array $ticketIds) {
        if (empty($ticketIds)) {
            return [];
        }

        $ticketIds = array_values(array_unique(array_map('intval', $ticketIds)));
        $placeholders = [];
        foreach ($ticketIds as $index => $ticketId) {
            $placeholders[] = ':ticket_' . $index;
        }

        try {
            $this->db->query("
                SELECT
                    n.*,
                    u.nombre,
                    u.apellido
                FROM lesson_issue_report_notes n
                INNER JOIN usuarios u ON u.id = n.admin_user_id
                WHERE n.issue_report_id IN (" . implode(', ', $placeholders) . ")
                ORDER BY n.created_at DESC
            ");
            foreach ($ticketIds as $index => $ticketId) {
                $this->db->bind(':ticket_' . $index, $ticketId);
            }
            $rows = $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $ticketId = (int) $row->issue_report_id;
            if (!isset($grouped[$ticketId])) {
                $grouped[$ticketId] = [];
            }
            $grouped[$ticketId][] = $row;
        }

        return $grouped;
    }

    private function enriquecerTicketsAdmin(array $tickets) {
        foreach ($tickets as $ticket) {
            [$score, $key, $label, $tone] = $this->clasificarPrioridadTicket($ticket);
            $ticket->priority_score = $score;
            $ticket->priority_key = $key;
            $ticket->priority_label = $label;
            $ticket->priority_tone = $tone;
        }

        return $tickets;
    }

    private function clasificarPrioridadTicket($ticket) {
        if (($ticket->status ?? '') === 'resuelto') {
            return [0, 'cerrado', 'Cerrado', 'success'];
        }

        $score = 1;
        $issueType = (string) ($ticket->issue_type ?? '');
        $contextType = (string) ($ticket->context_type ?? '');
        $description = (string) ($ticket->description ?? '');

        if (($ticket->status ?? '') === 'nuevo') {
            $score += 1;
        }
        if ($issueType === 'boton_no_funciona' || $issueType === 'audio_video') {
            $score += 2;
        }
        if ($issueType === 'contenido_incorrecto' || $issueType === 'error_visual') {
            $score += 1;
        }
        if ($contextType === 'actividad') {
            $score += 1;
        }
        if (!empty($ticket->es_profesor)) {
            $score += 1;
        }
        if (strlen($description) > 180) {
            $score += 1;
        }

        if ($score >= 5) {
            return [$score, 'alta', 'Alta', 'warning'];
        }
        if ($score >= 3) {
            return [$score, 'media', 'Media', 'info'];
        }

        return [$score, 'baja', 'Baja', 'success'];
    }

    private function obtenerActividadAdmin($instanciaId, $limit = 20, $action = '', $target = '') {
        $sql = "
            SELECT
                aal.*,
                u.nombre,
                u.apellido
            FROM admin_activity_log aal
            INNER JOIN usuarios u ON u.id = aal.admin_user_id
            WHERE aal.instancia_id = :instancia_id
        ";
        $params = [':instancia_id' => (int) $instanciaId];

        if ($action !== '') {
            $sql .= " AND aal.action_type = :action";
            $params[':action'] = $action;
        }
        if ($target !== '') {
            $sql .= " AND aal.target_type = :target";
            $params[':target'] = $target;
        }

        $sql .= " ORDER BY aal.created_at DESC LIMIT :limit";

        try {
            $this->db->query($sql);
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }
            $this->db->bind(':limit', (int) $limit, PDO::PARAM_INT);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    private function registrarActividadAdmin($actionType, $targetType, $targetId, $description, array $metadata = []) {
        try {
            $this->db->query("
                INSERT INTO admin_activity_log (
                    instancia_id,
                    admin_user_id,
                    action_type,
                    target_type,
                    target_id,
                    description,
                    metadata_json
                ) VALUES (
                    :instancia_id,
                    :admin_user_id,
                    :action_type,
                    :target_type,
                    :target_id,
                    :description,
                    :metadata_json
                )
            ");
            $this->db->bind(':instancia_id', (int) Auth::getInstanciaId());
            $this->db->bind(':admin_user_id', (int) Auth::getUserId());
            $this->db->bind(':action_type', (string) $actionType);
            $this->db->bind(':target_type', (string) $targetType);
            $this->db->bind(':target_id', $targetId ? (int) $targetId : null);
            $this->db->bind(':description', (string) $description);
            $this->db->bind(':metadata_json', !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null);
            $this->db->execute();
        } catch (Exception $e) {
            // El log no debe romper la operacion principal.
        }
    }

    private function resolverIdsSeleccionados($values) {
        if (!is_array($values)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $values))));
    }

    private function placeholdersDesdeIds($prefix, array $ids) {
        $bindings = [];
        foreach (array_values($ids) as $index => $id) {
            $bindings[':' . $prefix . '_' . $index] = (int) $id;
        }
        return $bindings;
    }

    private function resolverRetornoAdmin($fallback = '/admin') {
        $returnTo = trim((string) ($_POST['return_to'] ?? $_GET['return_to'] ?? ''));
        if ($returnTo !== '' && strpos($returnTo, '/admin') === 0) {
            return $returnTo;
        }

        return $fallback;
    }
}
