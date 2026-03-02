<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';

class AdminController extends Controller {
    private $db;

    public function __construct() {
        $this->requireRole('admin');
        $this->db = new Database();
    }

    public function index() {
        // Fetch stats
        $this->db->query("SELECT COUNT(*) as total FROM usuarios");
        $totalUsers = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE es_profesor = 1");
        $totalProfessors = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE es_estudiante = 1");
        $totalStudents = $this->db->single()->total;

        $this->db->query("SELECT COUNT(*) as total FROM cursos");
        $totalCourses = $this->db->single()->total;

        // Fetch recent
        $this->db->query("SELECT * FROM usuarios ORDER BY creado_en DESC LIMIT 5");
        $recentUsers = $this->db->resultSet();

        $this->db->query("SELECT * FROM cursos ORDER BY fecha_creacion DESC LIMIT 5");
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

        $this->db->query("SELECT c.*, u.nombre as profesor_nombre, u.apellido as profesor_apellido 
                          FROM cursos c 
                          LEFT JOIN usuarios u ON c.creado_por = u.id 
                          WHERE c.instancia_id = :instancia_id
                          ORDER BY c.fecha_creacion DESC");
        $this->db->bind(':instancia_id', $instanciaId);
        $courses = $this->db->resultSet();

        $this->view('admin/cursos', ['courses' => $courses]);
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

            // Basic validation
            if (empty($nombre) || empty($apellido) || empty($email)) {
                // Handle error
            }

            // Update user
            $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email";
            
            $password = $_POST['password'] ?? '';
            if (!empty($password)) {
                $sql .= ", password_hash = :password";
            }
            
            // Handle role update logic
            $es_admin = 0;
            $es_profesor = 0;
            $es_estudiante = 0;

            if ($rol == 'admin') {
                $es_admin = 1;
            } elseif ($rol == 'profesor') {
                $es_profesor = 1;
            } elseif ($rol == 'estudiante') {
                $es_estudiante = 1;
            }

            $sql .= ", es_admin_institucion = :es_admin, es_profesor = :es_profesor, es_estudiante = :es_estudiante";
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
            $this->db->bind(':id', $id);
            $this->db->bind(':instancia_id', $instanciaId);

            if ($this->db->execute()) {
                $this->redirect('/admin/usuarios');
            } else {
                // Handle error
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
            $this->redirect('/admin/usuarios');
        }

        $this->db->query("DELETE FROM usuarios WHERE id = :id AND instancia_id = :instancia_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':instancia_id', $instanciaId);
        
        if ($this->db->execute()) {
            $this->redirect('/admin/usuarios');
        } else {
            // Handle error
            $this->redirect('/admin/usuarios');
        }
    }
}
