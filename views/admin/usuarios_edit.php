<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../models/ProfesorPlan.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-person-lines-fill"></i> Perfil institucional</span>
        <h1 class="page-title">Edita la cuenta sin perder claridad sobre su rol.</h1>
        <p class="page-subtitle">
            Ajusta nombre, correo, credenciales y permisos desde un formulario limpio y directo.
        </p>
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
                    <div class="section-title">
                        <h2>Editar usuario #<?php echo (int) $user->id; ?></h2>
                        <span class="soft-badge">
                            <?php
                            if ($user->es_admin_institucion) {
                                echo 'Administrador';
                            } elseif ($user->es_profesor) {
                                echo 'Profesor';
                            } else {
                                echo 'Estudiante';
                            }
                            ?>
                        </span>
                    </div>

                    <form action="<?php echo url('/admin/usuarios/edit/' . $user->id); ?>" method="POST" class="row g-3">
                        <?php echo csrf_input(); ?>

                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user->nombre); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user->apellido); ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Correo electronico</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Contrasena nueva</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Dejar en blanco para conservar la actual">
                            <div class="form-text text-muted">Solo cambia la clave si realmente necesitas rotarla.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="estudiante" <?php echo ($user->es_estudiante && !$user->es_profesor && !$user->es_admin_institucion) ? 'selected' : ''; ?>>Estudiante</option>
                                <option value="profesor" <?php echo $user->es_profesor ? 'selected' : ''; ?>>Profesor</option>
                                <option value="admin" <?php echo $user->es_admin_institucion ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                            <div class="form-text text-muted">Cambiar el rol modifica el acceso disponible en la plataforma.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="billing_plan" class="form-label">Plan de cuenta</label>
                            <select class="form-select" id="billing_plan" name="billing_plan">
                                <?php foreach (ProfesorPlan::obtenerPlanesDisponibles() as $planValue => $planLabel): ?>
                                    <option value="<?php echo htmlspecialchars($planValue); ?>" <?php echo ProfesorPlan::normalizarPlan($user->billing_plan ?? null) === $planValue ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($planLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">`Lifetime` solo debe asignarse desde administracion.</div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" id="is_official" name="is_official" value="1" <?php echo !empty($user->is_official) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_official">Cuenta oficial de la plataforma</label>
                            </div>
                            <div class="form-text text-muted">Solo para demos, showcase o cuentas internas del producto.</div>
                        </div>

                        <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar cambios
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
