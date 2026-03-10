<?php
// dev/index.php
// Backdoor de desarrollo para acceso rapido
// ADVERTENCIA: Este archivo debe ser eliminado en produccion o protegido

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes si no existen para evitar errores al cargar config
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

// Cargar configuracion y clases necesarias
require_once __DIR__ . '/../config/database.php';

// Cargar Auth.php SIN ejecutar session_start duplicado
if (file_exists(__DIR__ . '/../core/Auth.php')) {
    // Leemos el contenido de Auth.php y lo evaluamos sin la parte de session_start automática
    // O mejor, definimos la clase Auth mockeada si no podemos incluirlo limpiamente, 
    // pero intentaremos incluirlo normal y controlar el error.
    require_once __DIR__ . '/../core/Auth.php';
}

// Asegurar que la sesión esté iniciada (configuración robusta compatible con Auth.php)
if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    ini_set('session.gc_maxlifetime', 3600);
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
    // Intentar iniciar sesión si Auth.php no lo hizo
    @session_start();
}

// Conexion a BD
try {
    // FIX INTELIGENTE: Si detectamos config antigua de Ancobo en hosting, forzamos Adapa
    $dbName = DB_NAME;
    if ($dbName === 'aspierd1_ancobo') {
        $dbName = 'aspierd1_adapa';
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage() . "<br>Intenta verificar config/database.php");
}

// Usuarios de prueba a asegurar
$testUsers = [
    'admin' => [
        'email' => 'admin@adapa.edu',
        'password' => '12345678',
        'nombre' => 'Admin',
        'apellido' => 'Developer',
        'role_field' => 'es_admin_institucion',
        'color' => 'danger'
    ],
    'profesor' => [
        'email' => 'profesor@adapa.edu',
        'password' => '12345678',
        'nombre' => 'Profesor',
        'apellido' => 'Prueba',
        'role_field' => 'es_profesor',
        'color' => 'primary'
    ],
    'estudiante' => [
        'email' => 'estudiante1@adapa.edu',
        'password' => '12345678',
        'nombre' => 'Estudiante',
        'apellido' => 'Uno',
        'role_field' => 'es_estudiante',
        'color' => 'success'
    ],
    'manuel' => [
        'email' => 'manuelx_05@hotmail.com',
        'password' => 'polilla05', // Password real proporcionado
        'nombre' => 'Enmanuel',
        'apellido' => 'Domínguez',
        'role_field' => 'es_estudiante',
        'color' => 'info'
    ]
];

// Logica de Creacion de Usuarios (Auto-fix) - EJECUTAR ANTES DEL LOGIN
$statusMsg = "";
foreach ($testUsers as $key => $data) {
    try {
        // Verificar existencia
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        
        if ($stmt->fetchColumn() == 0) {
            // Crear usuario
            $passHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $roleField = $data['role_field'];
            
            // Construir SQL dinamicamente para el campo de rol
            $sql = "INSERT INTO usuarios (
                email, password_hash, nombre, apellido, 
                $roleField, activo, email_verificado, instancia_id
            ) VALUES (
                :email, :pass, :nombre, :apellido, 
                1, 1, 1, 1
            )";
            
            $insertStmt = $pdo->prepare($sql);
            $insertStmt->execute([
                ':email' => $data['email'],
                ':pass' => $passHash, // PDO acepta claves con o sin :
                ':nombre' => $data['nombre'],
                ':apellido' => $data['apellido']
            ]);
            $statusMsg .= "<div class='alert alert-success'>Creado usuario: {$data['email']}</div>";
        }
    } catch (PDOException $e) {
        $statusMsg .= "<div class='alert alert-danger'>Error verificando/creando {$data['email']}: " . $e->getMessage() . "</div>";
    }
}

// Logica de Login Automatico
if (isset($_GET['login_as'])) {
    $roleKey = $_GET['login_as'];
    
    if (isset($testUsers[$roleKey])) {
        $email = $testUsers[$roleKey]['email'];
        
        try {
            // Buscar usuario
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // FORZAR INICIO DE SESION MANUAL (Bypass Auth::login para evitar errores ocultos)
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                
                $_SESSION['user_id'] = $user->id;
                $_SESSION['instancia_id'] = $user->instancia_id;
                
                $role = 'user';
                if ($user->es_admin_institucion) $role = 'admin';
                elseif ($user->es_profesor) $role = 'profesor';
                elseif ($user->es_estudiante) $role = 'estudiante';
                
                $_SESSION['user_role'] = $role;
                $_SESSION['user_name'] = $user->nombre . ' ' . $user->apellido;
                $_SESSION['billing_plan'] = $user->billing_plan ?? 'free';
                $_SESSION['is_official'] = !empty($user->is_official) ? 1 : 0;
                $_SESSION['user_base_language'] = $user->idioma_base ?? 'espanol';
                $_SESSION['user_interface_language'] = $user->idioma_interfaz ?? 'espanol';
                
                // Redirigir segun rol
                $redirect = '../'; // Default home
                if ($user->es_admin_institucion) $redirect = '../admin';
                elseif ($user->es_profesor) $redirect = '../profesor/cursos';
                elseif ($user->es_estudiante) $redirect = '../estudiante';
                
                // Limpiar buffer de salida por si acaso
                while (ob_get_level()) ob_end_clean();
                
                header("Location: $redirect");
                exit;
            } else {
                $error = "Usuario no encontrado en BD. Intenta recargar para crearlo.";
            }
        } catch (Exception $e) {
            $error = "Excepcion al loguear: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dev Backdoor Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { background: #2d2d2d; border: 1px solid #444; width: 100%; max-width: 500px; }
        .btn-login { width: 100%; margin-bottom: 10px; padding: 15px; font-size: 1.2rem; text-align: left; position: relative; }
        .btn-login i { position: absolute; right: 20px; }
    </style>
</head>
<body>
    <div class="card p-4">
        <h2 class="mb-4 text-center">🚪 Dev Backdoor</h2>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="alert alert-info text-center">
                Sesión activa: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></strong><br>
                Rol: <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'N/A'); ?>
            </div>
        <?php endif; ?>

        <?php if($statusMsg) echo $statusMsg; ?>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        
        <p class="text-muted text-center mb-4">Acceso directo sin contraseña</p>
        
        <a href="?login_as=estudiante" class="btn btn-success btn-login">
            🧑‍🎓 Estudiante
            <small class="d-block fst-italic fs-6">estudiante1@adapa.edu</small>
        </a>

        <a href="?login_as=manuel" class="btn btn-info btn-login">
            👤 Enmanuel (Real)
            <small class="d-block fst-italic fs-6">manuelx_05@hotmail.com</small>
        </a>
        
        <a href="?login_as=profesor" class="btn btn-primary btn-login">
            👨‍🏫 Profesor
            <small class="d-block fst-italic fs-6">profesor@adapa.edu</small>
        </a>
        
        <a href="?login_as=admin" class="btn btn-danger btn-login">
            🛡️ Admin
            <small class="d-block fst-italic fs-6">admin@adapa.edu</small>
        </a>
        
        <div class="mt-3 text-center">
            <a href="../login" class="text-muted">Ir al login normal</a>
        </div>
    </div>
</body>
</html>
