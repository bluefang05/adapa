<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = $_SESSION['register_success'] ?? '';
$error = $_SESSION['register_error'] ?? '';

unset($_SESSION['register_success'], $_SESSION['register_error']);

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container auth-layout">
    <div class="auth-shell">
        <section class="page-hero auth-hero auth-hero-success">
            <span class="eyebrow"><i class="bi bi-person-plus"></i> Registro</span>
            <h1 class="page-title">Crea tu cuenta</h1>
            <p class="page-subtitle">Configura tu cuenta en menos de un minuto.</p>
        </section>

        <section class="form-shell auth-card">
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo url('/register'); ?>" class="auth-form">
                    <?php echo csrf_input(); ?>
                    <div class="row g-3 mb-2">
                        <div class="col-12">
                            <label class="form-label">Tipo de cuenta inicial</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-check h-100">
                                        <input class="form-check-input" type="radio" name="account_type" value="estudiante" checked>
                                        <span class="form-check-label d-block">
                                            <strong>Estudiante</strong><br>
                                            <span class="text-muted">Aprende y sigue tu progreso.</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-check h-100">
                                        <input class="form-check-input" type="radio" name="account_type" value="profesor">
                                        <span class="form-check-label d-block">
                                            <strong>Profesor</strong><br>
                                            <span class="text-muted">Crea cursos y gestiona estudiantes.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Tu nombre" autocomplete="given-name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Tu apellido" autocomplete="family-name">
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Correo electronico *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="nombre@ejemplo.com" autocomplete="email" autocapitalize="off" spellcheck="false">
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="password" class="form-label">Contrasena *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="********" minlength="8" autocomplete="new-password">
                            </div>
                            <div class="form-text">La contrasena debe tener al menos 8 caracteres y combinar letras con numeros.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="idioma_base" class="form-label">Idioma base *</label>
                            <select class="form-select" id="idioma_base" name="idioma_base" required>
                                <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'espanol' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="idioma_interfaz" class="form-label">Idioma de interfaz *</label>
                            <select class="form-select" id="idioma_interfaz" name="idioma_interfaz" required>
                                <?php foreach (app_interface_languages() as $languageValue => $languageLabel): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'espanol' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="responsive-actions mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-person-plus"></i> Crear cuenta
                        </button>
                        <a href="<?php echo url('/login'); ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Ya tengo cuenta
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
