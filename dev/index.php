<?php
require_once __DIR__ . '/../config/database.php';

if (!defined('BASE_URL')) {
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/dev/index.php'));
    $applicationDirectory = str_replace('\\', '/', dirname($scriptDirectory));
    define('BASE_URL', $applicationDirectory === '/' || $applicationDirectory === '.' ? '' : rtrim($applicationDirectory, '/'));
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Auth.php';

$devToolsConfig = require __DIR__ . '/../config/dev_tools.php';
if (empty($devToolsConfig['enabled'])) {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

require_once __DIR__ . '/../views/partials/header.php';
?>
<div class="container py-4">
    <div class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-tools"></i> Dev</span>
        <h1 class="page-title">Herramientas de desarrollo</h1>
        <p class="page-subtitle">Accesos de laboratorio para pruebas controladas.</p>
    </div>

    <div class="card">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="metric-label">Usuarios</div>
                <h2 class="h4 mb-1">CRUD completo de usuarios</h2>
                <p class="text-muted mb-0">Crear, buscar, editar, cambiar contraseña, activar y eliminar usuarios.</p>
            </div>
            <a class="btn btn-primary btn-lg" href="<?php echo url('/dev/users_lab.php'); ?>">
                <i class="bi bi-people-fill"></i> Abrir CRUD de usuarios
            </a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
