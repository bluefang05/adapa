<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../models/ProfesorPlan.php'; ?>

<?php
$activeTeachers = 0;
$managedCourses = 0;
$publicCourses = 0;
$totalStudents = 0;
$openTickets = 0;
$teacherQuery = $_SERVER['QUERY_STRING'] ?? '';
$currentProfessorsUrl = '/admin/profesores' . ($teacherQuery !== '' ? '?' . $teacherQuery : '');

foreach ($teachers as $teacher) {
    $activeTeachers += !empty($teacher->activo) ? 1 : 0;
    $managedCourses += (int) ($teacher->total_cursos ?? 0);
    $publicCourses += (int) ($teacher->cursos_publicos ?? 0);
    $totalStudents += (int) ($teacher->total_estudiantes ?? 0);
    $openTickets += (int) ($teacher->tickets_docente_abiertos ?? 0) + (int) ($teacher->tickets_cursos_abiertos ?? 0);
}

function adminTeacherLoadTone($teacher) {
    $courses = (int) ($teacher->total_cursos ?? 0);
    $students = (int) ($teacher->total_estudiantes ?? 0);
    $openTickets = (int) ($teacher->tickets_docente_abiertos ?? 0) + (int) ($teacher->tickets_cursos_abiertos ?? 0);

    if ($courses === 0) {
        return ['warning', 'Sin carga'];
    }
    if ($openTickets >= 4 || $students >= 80) {
        return ['warning', 'Alta demanda'];
    }
    if ($students >= 25 || $courses >= 3 || $openTickets >= 1) {
        return ['info', 'Carga activa'];
    }

    return ['success', 'Carga controlada'];
}
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-person-workspace"></i> Supervisar profesores</span>
        <h1 class="page-title">Lee la carga real de cada docente y abre cursos o tickets sin dar vueltas.</h1>
        <p class="page-subtitle">
            Esta vista te dice quien esta sosteniendo alumnos, quien tiene incidencia abierta y quien todavia no esta empujando contenido.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al dashboard
            </a>
            <a href="<?php echo url('/admin/usuarios?role=profesor'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-people"></i> Ver todos los profesores
            </a>
            <a href="<?php echo url('/admin/tickets?role=profesor'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-life-preserver"></i> Tickets del equipo docente
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-person-workspace"></i> <?php echo count($teachers); ?> profesores</span>
            <span class="soft-badge <?php echo $activeTeachers < count($teachers) ? 'warning' : 'success'; ?>"><i class="bi bi-person-check"></i> <?php echo $activeTeachers; ?> activos</span>
            <span class="soft-badge"><i class="bi bi-journal-bookmark"></i> <?php echo $managedCourses; ?> cursos</span>
            <span class="soft-badge"><i class="bi bi-broadcast"></i> <?php echo $publicCourses; ?> visibles</span>
            <span class="soft-badge"><i class="bi bi-people"></i> <?php echo $totalStudents; ?> alumnos</span>
            <span class="soft-badge <?php echo $openTickets > 0 ? 'warning' : 'success'; ?>"><i class="bi bi-life-preserver"></i> <?php echo $openTickets; ?> tickets abiertos</span>
            <span class="soft-badge <?php echo ((int) ($teacherSummary['ready_for_review'] ?? 0) > 0) ? 'badge-accent' : 'success'; ?>"><i class="bi bi-patch-check"></i> <?php echo (int) ($teacherSummary['ready_for_review'] ?? 0); ?> listos para revisar</span>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <section class="filter-shell mb-4">
        <div class="panel-body">
            <div class="section-title">
                <h2>Filtros</h2>
            </div>
            <form method="GET" action="<?php echo url('/admin/profesores'); ?>" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label" for="search">Buscar profesor</label>
                    <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Nombre, apellido o correo">
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="status">Estado</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="activos" <?php echo ($status ?? '') === 'activos' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactivos" <?php echo ($status ?? '') === 'inactivos' ? 'selected' : ''; ?>>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="load">Carga</label>
                    <select id="load" name="load" class="form-select">
                        <option value="">Cualquiera</option>
                        <option value="con_cursos" <?php echo ($load ?? '') === 'con_cursos' ? 'selected' : ''; ?>>Con cursos</option>
                        <option value="con_alumnos" <?php echo ($load ?? '') === 'con_alumnos' ? 'selected' : ''; ?>>Con alumnos</option>
                        <option value="con_tickets" <?php echo ($load ?? '') === 'con_tickets' ? 'selected' : ''; ?>>Con tickets abiertos</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-2 responsive-actions">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="<?php echo url('/admin/profesores'); ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Mapa docente</h2>
            <span class="soft-badge"><i class="bi bi-grid"></i> <?php echo count($teachers); ?> resultados</span>
        </div>

        <?php if (empty($teachers)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-person-x"></i></span>
                    <div class="empty-state-copy">No hay profesores para el filtro actual.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($teachers as $teacher): ?>
                    <?php [$loadTone, $loadLabel] = adminTeacherLoadTone($teacher); ?>
                    <?php $openTeacherTickets = (int) ($teacher->tickets_docente_abiertos ?? 0) + (int) ($teacher->tickets_cursos_abiertos ?? 0); ?>
                    <div class="col-xl-6">
                        <article class="surface-card h-100">
                            <div class="card-body">
                                <div class="split-head mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="avatar-token"><?php echo strtoupper(substr((string) ($teacher->nombre ?? '?'), 0, 1)); ?></span>
                                        <div>
                                            <h3 class="h4 mb-1"><?php echo htmlspecialchars(trim(($teacher->nombre ?? '') . ' ' . ($teacher->apellido ?? ''))); ?></h3>
                                            <div class="small text-muted"><?php echo htmlspecialchars($teacher->email ?? ''); ?></div>
                                        </div>
                                    </div>
                                    <div class="badge-row badge-row-end">
                                        <span class="soft-badge <?php echo !empty($teacher->activo) ? 'success' : 'warning'; ?>">
                                            <?php echo !empty($teacher->activo) ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                        <span class="soft-badge <?php echo !empty($teacher->email_verificado) ? 'info' : ''; ?>">
                                            <?php echo !empty($teacher->email_verificado) ? 'Correo verificado' : 'Correo pendiente'; ?>
                                        </span>
                                        <span class="soft-badge <?php echo $loadTone; ?>"><?php echo htmlspecialchars($loadLabel); ?></span>
                                    </div>
                                </div>

                                <div class="course-meta mb-3">
                                    <span><i class="bi bi-credit-card-2-front"></i> <?php echo htmlspecialchars(ProfesorPlan::obtenerEtiquetaPlan($teacher->billing_plan ?? null)); ?></span>
                                    <span><i class="bi bi-book"></i> <?php echo (int) ($teacher->total_cursos ?? 0); ?> cursos</span>
                                    <span><i class="bi bi-broadcast"></i> <?php echo (int) ($teacher->cursos_publicos ?? 0); ?> visibles</span>
                                    <span><i class="bi bi-people"></i> <?php echo (int) ($teacher->total_estudiantes ?? 0); ?> alumnos</span>
                                    <span><i class="bi bi-lightning"></i> <?php echo (int) ($teacher->total_actividades ?? 0); ?> actividades</span>
                                </div>

                                <div class="badge-stack mb-3">
                                    <span class="soft-badge"><i class="bi bi-journal-bookmark"></i> Operacion: <?php echo (int) ($teacher->total_cursos ?? 0); ?> cursos, <?php echo (int) ($teacher->cursos_publicos ?? 0); ?> visibles, <?php echo (int) ($teacher->total_estudiantes ?? 0); ?> alumnos</span>
                                    <span class="soft-badge"><i class="bi bi-life-preserver"></i> Soporte: <?php echo (int) ($teacher->tickets_docente ?? 0); ?> tickets del profesor, <?php echo (int) ($teacher->tickets_cursos ?? 0); ?> de cursos, <?php echo $openTeacherTickets; ?> abiertos</span>
                                </div>

                                <div class="alert context-note mb-3">
                                    <div class="split-head">
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($teacher->admin_focus_label ?? 'Control operativo'); ?></div>
                                            <div class="small text-muted mt-1"><?php echo htmlspecialchars($teacher->admin_focus_hint ?? ''); ?></div>
                                            <?php if (!empty($teacher->hotspot_course_title)): ?>
                                                <div class="small text-muted mt-2"><strong>Curso foco:</strong> <?php echo htmlspecialchars($teacher->hotspot_course_title); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ((int) ($teacher->ready_courses ?? 0) > 0): ?>
                                            <span class="soft-badge info"><?php echo (int) ($teacher->ready_courses ?? 0); ?> listos para revisar</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="responsive-actions">
                                    <a href="<?php echo url('/admin/usuarios/edit/' . (int) $teacher->id); ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Editar perfil
                                    </a>
                                    <a href="<?php echo url('/admin/cursos?teacher=' . (int) $teacher->id); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-journal-bookmark"></i> Ver cursos
                                    </a>
                                    <a href="<?php echo url('/admin/cursos/create?teacher=' . (int) $teacher->id); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus-circle"></i> Crear curso
                                    </a>
                                    <a href="<?php echo url('/admin/tickets?user_id=' . (int) $teacher->id . '&role=profesor'); ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-send"></i> Tickets del profesor
                                    </a>
                                    <a href="<?php echo url('/admin/tickets?owner_id=' . (int) $teacher->id); ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-life-preserver"></i> Tickets de sus cursos
                                    </a>
                                    <form method="POST" action="<?php echo url('/admin/usuarios/toggle-activo/' . (int) $teacher->id); ?>">
                                        <?php echo csrf_input(); ?>
                                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentProfessorsUrl); ?>">
                                        <button type="submit" class="btn btn-sm <?php echo !empty($teacher->activo) ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                            <i class="bi <?php echo !empty($teacher->activo) ? 'bi-pause-circle' : 'bi-play-circle'; ?>"></i>
                                            <?php echo !empty($teacher->activo) ? 'Desactivar' : 'Activar'; ?>
                                        </button>
                                    </form>
                                    <?php if (empty($teacher->email_verificado)): ?>
                                        <form method="POST" action="<?php echo url('/admin/usuarios/verify-email/' . (int) $teacher->id); ?>">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentProfessorsUrl); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-patch-check"></i> Verificar correo
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
