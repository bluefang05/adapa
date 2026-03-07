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
            <p class="page-subtitle">Accede y continua donde lo dejaste.</p>
        </section>

        <section class="form-shell auth-card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo url('/login'); ?>" class="auth-form">
                    <?php echo csrf_input(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electronico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="nombre@ejemplo.com" autocomplete="email" autocapitalize="off" spellcheck="false">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contrasena</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="********" autocomplete="current-password">
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

    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
