<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../models/ProfesorPlan.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-person-plus-fill"></i> Nuevo usuario</span>
        <h1 class="page-title">Crea cuentas con rol y plan desde el panel administrativo.</h1>
        <p class="page-subtitle">Alta rapida de usuario para administracion, docencia o estudiantes.</p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a usuarios
            </a>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-8">
            <div class="form-shell">
                <div class="card-body">
                    <form action="<?php echo url('/admin/usuarios/create'); ?>" method="POST" class="row g-3">
                        <?php echo csrf_input(); ?>

                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Correo electronico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Contrasena inicial</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            <div class="form-text text-muted">Minimo 8 caracteres.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="estudiante">Estudiante</option>
                                <option value="profesor">Profesor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="billing_plan" class="form-label">Plan de cuenta</label>
                            <select class="form-select" id="billing_plan" name="billing_plan">
                                <?php foreach (ProfesorPlan::obtenerPlanesDisponibles() as $planValue => $planLabel): ?>
                                    <option value="<?php echo htmlspecialchars($planValue); ?>">
                                        <?php echo htmlspecialchars($planLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="idioma_base" class="form-label">Idioma base</label>
                            <select class="form-select" id="idioma_base" name="idioma_base">
                                <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'espanol' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="idioma_interfaz" class="form-label">Idioma de interfaz</label>
                            <select class="form-select" id="idioma_interfaz" name="idioma_interfaz">
                                <?php foreach (app_interface_languages() as $languageValue => $languageLabel): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'espanol' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" id="is_official" name="is_official" value="1">
                                <label class="form-check-label" for="is_official">Cuenta oficial de la plataforma</label>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear usuario
                            </button>
                            <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
