<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = $error ?? '';

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container auth-layout">
    <div class="auth-shell">
        <section class="page-hero auth-hero">
            <span class="eyebrow"><i class="bi bi-door-open"></i> Acceso</span>
            <h1 class="page-title">Inicia sesion en ADAPA</h1>
            <p class="page-subtitle">Entra a tus cursos, retoma actividades pendientes y administra tu espacio segun tu rol.</p>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Estudiante</div>
                    <div class="metric-value">Aprende</div>
                    <div class="metric-note">Retoma teoria, practica y progreso.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Profesor</div>
                    <div class="metric-value">Gestiona</div>
                    <div class="metric-note">Crea cursos, lecciones y revisa respuestas.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Admin</div>
                    <div class="metric-value">Coordina</div>
                    <div class="metric-note">Supervisa usuarios, cursos e instancia.</div>
                </div>
            </div>
        </section>

        <section class="form-shell auth-card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="demo-access-shell">
                    <div class="demo-access-header">
                        <h2>Acceso rapido de demostracion</h2>
                        <p>Completa el formulario automaticamente con una cuenta de prueba.</p>
                    </div>
                    <div class="demo-access-grid">
                        <button type="button" class="btn btn-outline-success demo-access-btn" onclick="fillLogin('estudiante1@adapa.edu', 'estudiante123')">
                            <span><i class="bi bi-mortarboard"></i> Estudiante demo</span>
                            <span class="soft-badge success">Estudiante</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary demo-access-btn" onclick="fillLogin('profesor@adapa.edu', 'profesor123')">
                            <span><i class="bi bi-person-video3"></i> Profesor demo</span>
                            <span class="soft-badge info">Profesor</span>
                        </button>
                        <button type="button" class="btn btn-outline-danger demo-access-btn" onclick="fillLogin('admin@adapa.edu', 'admin123')">
                            <span><i class="bi bi-shield-lock"></i> Admin demo</span>
                            <span class="soft-badge warning">Admin</span>
                        </button>
                    </div>
                </div>

                <form method="POST" action="<?php echo url('/login'); ?>" class="auth-form">
                    <?php echo csrf_input(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electronico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="nombre@ejemplo.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contrasena</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="********">
                        </div>
                    </div>

                    <div class="responsive-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Ingresar
                        </button>
                        <a href="<?php echo url('/'); ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Volver al inicio
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <div class="text-center mt-3">
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="toggleDebugMode()" title="Toggle Debug Mode">
                <i class="bi bi-bug"></i> Debug mode
            </button>
        </div>
    </div>
</div>

<script>
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    document.querySelector('button[type="submit"]').focus();
}

function toggleDebugMode() {
    const url = new URL(window.location.href);
    const debugParam = url.searchParams.get('debug');

    if (debugParam === '1') {
        url.searchParams.delete('debug');
    } else {
        url.searchParams.set('debug', '1');
    }

    window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
