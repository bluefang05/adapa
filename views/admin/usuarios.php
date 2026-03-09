<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../models/ProfesorPlan.php'; ?>

<?php
$userQuery = $_SERVER['QUERY_STRING'] ?? '';
$currentUsersUrl = '/admin/usuarios' . ($userQuery !== '' ? '?' . $userQuery : '');
$admins = 0;
$profesores = 0;
$estudiantes = 0;
$inactiveUsers = 0;
$pendingEmails = 0;

foreach ($users as $user) {
    if ($user->es_admin_institucion) {
        $admins++;
    } elseif ($user->es_profesor) {
        $profesores++;
    } elseif ($user->es_estudiante) {
        $estudiantes++;
    }
    if (empty($user->activo)) {
        $inactiveUsers++;
    }
    if (empty($user->email_verificado)) {
        $pendingEmails++;
    }
}
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-people-fill"></i> Administracion de usuarios</span>
        <h1 class="page-title">Gestiona acceso, roles y limpieza operativa sin perder contexto.</h1>
        <p class="page-subtitle">
            Filtra por rol, localiza usuarios por nombre o correo y corrige cuentas desde una misma vista de trabajo.
        </p>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-files"></i> <?php echo count($users); ?> resultados visibles</span>
            <span class="soft-badge"><i class="bi bi-shield-check"></i> <?php echo $admins; ?> admins</span>
            <span class="soft-badge"><i class="bi bi-person-workspace"></i> <?php echo $profesores; ?> profesores</span>
            <span class="soft-badge"><i class="bi bi-mortarboard"></i> <?php echo $estudiantes; ?> estudiantes</span>
            <span class="soft-badge <?php echo $inactiveUsers > 0 ? 'warning' : 'success'; ?>"><i class="bi bi-person-slash"></i> <?php echo $inactiveUsers; ?> inactivos</span>
            <span class="soft-badge <?php echo $pendingEmails > 0 ? 'warning' : 'info'; ?>"><i class="bi bi-envelope-exclamation"></i> <?php echo $pendingEmails; ?> correo pendiente</span>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <section class="filter-shell mb-4">
        <div class="panel-body">
            <div class="section-title">
                <h2>Filtros de busqueda</h2>
                <?php if (!empty($search) || !empty($role)): ?>
                    <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Limpiar filtros
                    </a>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?php echo url('/admin/usuarios'); ?>" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label for="search" class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control"
                            placeholder="Nombre, apellido o correo"
                            value="<?php echo htmlspecialchars($search ?? ''); ?>"
                        >
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="role" class="form-label">Rol</label>
                    <select id="role" name="role" class="form-select">
                        <option value="">Todos los roles</option>
                        <option value="admin" <?php echo ($role ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="profesor" <?php echo ($role ?? '') === 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                        <option value="estudiante" <?php echo ($role ?? '') === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Aplicar
                    </button>
                </div>
                <div class="col-lg-2">
                    <span class="inline-stat">
                        <i class="bi bi-files"></i> Pagina <?php echo (int) $currentPage; ?> / <?php echo (int) max($totalPages, 1); ?>
                    </span>
                </div>
            </form>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Usuarios</h2>
            <div class="section-actions">
                <span class="soft-badge"><i class="bi bi-shield-check"></i> Control de roles</span>
                <a href="<?php echo url('/admin/usuarios/create'); ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-person-plus"></i> Crear usuario
                </a>
            </div>
        </div>

        <details class="panel page-assist-card mb-4">
            <summary class="page-assist-summary">
                <div>
                    <div class="metric-label">Herramientas de administracion</div>
                    <div class="fw-semibold mt-1">Accion masiva para los usuarios visibles</div>
                    <div class="small text-muted mt-1">Abre esta seccion cuando necesites activar accesos o marcar correos desde la lista actual.</div>
                </div>
                <span class="soft-badge">1 bloque</span>
            </summary>
            <div class="panel-body pt-0 page-assist-body">
                <form method="POST" action="<?php echo url('/admin/usuarios/bulk-action'); ?>" id="user-bulk-form" class="row g-3 align-items-end">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentUsersUrl); ?>">
                    <div class="col-md-4">
                        <label class="form-label" for="bulk-user-action">Accion</label>
                        <select id="bulk-user-action" name="action" class="form-select">
                            <option value="activate">Activar acceso</option>
                            <option value="deactivate">Desactivar acceso</option>
                            <option value="verify_email">Marcar correo verificado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="user-select-all">
                            <label class="form-check-label" for="user-select-all">Seleccionar usuarios visibles</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-lightning"></i> Aplicar a seleccion</button>
                    </div>
                </form>
            </div>
        </details>

        <div class="data-table-shell">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Plan</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="empty-state">No hay usuarios que coincidan con el filtro actual.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <input class="form-check-input user-select-item" type="checkbox" name="user_ids[]" value="<?php echo (int) $user->id; ?>" form="user-bulk-form" aria-label="Seleccionar usuario <?php echo (int) $user->id; ?>">
                                    </td>
                                    <td>#<?php echo (int) $user->id; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-token"><?php echo strtoupper(substr($user->nombre, 0, 1)); ?></span>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars(trim($user->nombre . ' ' . $user->apellido)); ?></div>
                                                <div class="small text-muted">Cuenta de la instancia actual</div>
                                                <div class="small mt-1 badge-row">
                                                    <span class="soft-badge <?php echo !empty($user->activo) ? 'success' : 'warning'; ?>">
                                                        <?php echo !empty($user->activo) ? 'Activa' : 'Inactiva'; ?>
                                                    </span>
                                                    <span class="soft-badge <?php echo !empty($user->email_verificado) ? 'info' : ''; ?>">
                                                        <?php echo !empty($user->email_verificado) ? 'Correo verificado' : 'Correo pendiente'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user->email); ?></td>
                                    <td>
                                        <?php if ($user->es_admin_institucion): ?>
                                            <span class="soft-badge">Administrador</span>
                                        <?php elseif ($user->es_profesor): ?>
                                            <span class="soft-badge">Profesor</span>
                                        <?php elseif ($user->es_estudiante): ?>
                                            <span class="soft-badge">Estudiante</span>
                                        <?php else: ?>
                                            <span class="soft-badge">Usuario</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="soft-badge <?php echo !empty($user->is_official) ? 'info' : ''; ?>">
                                            <?php echo htmlspecialchars(ProfesorPlan::obtenerEtiquetaPlan($user->billing_plan ?? null)); ?>
                                        </span>
                                        <?php if (!empty($user->is_official)): ?>
                                            <div class="small text-muted mt-1">Cuenta oficial</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user->creado_en)); ?></td>
                                    <td>
                                        <div class="responsive-actions table-actions">
                                            <a href="<?php echo url('/admin/usuarios/edit/' . $user->id); ?>" class="btn btn-sm btn-outline-secondary" title="Editar usuario">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="<?php echo url('/admin/usuarios/toggle-activo/' . $user->id); ?>">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentUsersUrl); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="<?php echo !empty($user->activo) ? 'Desactivar acceso' : 'Activar acceso'; ?>">
                                                    <i class="bi <?php echo !empty($user->activo) ? 'bi-person-dash' : 'bi-person-check'; ?>"></i>
                                                </button>
                                            </form>
                                            <?php if (empty($user->email_verificado)): ?>
                                                <form method="POST" action="<?php echo url('/admin/usuarios/verify-email/' . $user->id); ?>">
                                                    <?php echo csrf_input(); ?>
                                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentUsersUrl); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Marcar correo como verificado">
                                                        <i class="bi bi-patch-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($user->id != $_SESSION['user_id']): ?>
                                                <form method="POST" action="<?php echo url('/admin/usuarios/delete/' . $user->id); ?>" onsubmit="return confirm('Estas seguro de eliminar este usuario?');">
                                                    <?php echo csrf_input(); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar usuario">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Navegacion de usuarios" class="mt-4 pagination-shell">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('/admin/usuarios?page=' . ($currentPage - 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role)); ?>">Anterior</a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo url('/admin/usuarios?page=' . $i . '&search=' . urlencode($search) . '&role=' . urlencode($role)); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('/admin/usuarios?page=' . ($currentPage + 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role)); ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('user-select-all');
    if (!selectAll) {
        return;
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.user-select-item').forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });
});
</script>
