<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php
function adminDashboardTicketRole($ticket) {
    if (!empty($ticket->es_profesor)) {
        return 'Profesor';
    }
    if (!empty($ticket->es_estudiante)) {
        return 'Estudiante';
    }
    return 'Usuario';
}

function adminDashboardTicketTone($status) {
    switch ($status) {
        case 'resuelto':
            return 'success';
        case 'en_revision':
            return 'info';
        default:
            return 'warning';
    }
}

$adminReadyToReview = (int) ($catalogSummary['ready_to_review'] ?? 0);
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-speedometer2"></i> Control institucional</span>
        <h1 class="page-title">Vista operativa de la instancia.</h1>
        <p class="page-subtitle">
            Entra a usuarios, cursos y soporte sin perder de vista lo que de verdad requiere intervencion.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-primary">
                <i class="bi bi-people"></i> Gestionar usuarios
            </a>
            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-journal-bookmark"></i> Revisar cursos
            </a>
            <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-life-preserver"></i> Bandeja de tickets
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-people"></i> <?php echo (int) $totalUsers; ?> usuarios</span>
            <span class="soft-badge"><i class="bi bi-journal-bookmark"></i> <?php echo (int) $totalCourses; ?> cursos</span>
            <span class="soft-badge <?php echo $openTickets > 0 ? 'warning' : 'success'; ?>"><i class="bi bi-life-preserver"></i> <?php echo (int) $openTickets; ?> tickets abiertos</span>
            <span class="soft-badge <?php echo $adminReadyToReview > 0 ? 'badge-accent' : 'success'; ?>"><i class="bi bi-patch-check"></i> <?php echo $adminReadyToReview; ?> listos para revisar</span>
        </div>
    </section>

    <details class="panel page-assist-card mb-4">
        <summary class="page-assist-summary">
            <div>
                <div class="metric-label">Pulso institucional</div>
                <div class="fw-semibold mt-1">Indicadores secundarios y control interno</div>
                <div class="small text-muted mt-1">Abre esta seccion para ver salud del catalogo, accesos secundarios y bitacora administrativa.</div>
            </div>
            <span class="soft-badge">8 focos</span>
        </summary>
        <div class="panel-body pt-0 page-assist-body">
            <div class="summary-stat-grid">
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Profesores</div>
                    <div class="summary-stat-value"><?php echo (int) $totalProfessors; ?></div>
                    <div class="summary-stat-copy">Docentes con acceso activo al panel.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Estudiantes</div>
                    <div class="summary-stat-value"><?php echo (int) $totalStudents; ?></div>
                    <div class="summary-stat-copy">Base actual de aprendizaje dentro de la instancia.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Usuarios inactivos</div>
                    <div class="summary-stat-value"><?php echo (int) $inactiveUsers; ?></div>
                    <div class="summary-stat-copy">Cuentas bloqueadas o desactivadas.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Visibles en catalogo</div>
                    <div class="summary-stat-value"><?php echo (int) $publicCourses; ?></div>
                    <div class="summary-stat-copy">Oferta disponible para estudiantes ahora mismo.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Sin estructura</div>
                    <div class="summary-stat-value"><?php echo (int) ($catalogSummary['without_lessons'] ?? 0); ?></div>
                    <div class="summary-stat-copy">Cursos que todavia no tienen lecciones reales.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Sin practica</div>
                    <div class="summary-stat-value"><?php echo (int) ($catalogSummary['without_practice'] ?? 0); ?></div>
                    <div class="summary-stat-copy">Rutas con teoria, pero sin actividad suficiente.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Listos para revisar</div>
                    <div class="summary-stat-value"><?php echo $adminReadyToReview; ?></div>
                    <div class="summary-stat-copy">Cursos que ya merecen revision final.</div>
                </article>
                <article class="summary-stat-card">
                    <div class="summary-stat-label">Con soporte abierto</div>
                    <div class="summary-stat-value"><?php echo (int) ($catalogSummary['with_open_tickets'] ?? 0); ?></div>
                    <div class="summary-stat-copy">Cursos que siguen arrastrando incidencias abiertas.</div>
                </article>
            </div>

            <div class="responsive-actions mt-4">
                <a href="<?php echo url('/admin/profesores'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-workspace"></i> Supervisar profesores
                </a>
                <a href="<?php echo url('/admin/actividad'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-activity"></i> Abrir bitacora
                </a>
            </div>

            <?php if (!empty($recentAdminActivity)): ?>
                <section class="mt-4">
                    <div class="split-head mb-3">
                        <div>
                            <h2 class="h5 mb-1">Actividad administrativa reciente</h2>
                            <div class="small text-muted">Lectura interna, util cuando necesites rastrear cambios recientes.</div>
                        </div>
                        <span class="soft-badge info"><?php echo count($recentAdminActivity); ?> registros</span>
                    </div>
                    <div class="stack-list">
                        <?php foreach (array_slice($recentAdminActivity, 0, 4) as $entry): ?>
                            <article class="stack-item">
                                <div class="split-head">
                                    <div>
                                        <p class="stack-item-title"><?php echo htmlspecialchars($entry->description); ?></p>
                                        <div class="stack-item-subtitle">
                                            <?php echo htmlspecialchars(trim(($entry->nombre ?? '') . ' ' . ($entry->apellido ?? ''))); ?>
                                            &middot; <?php echo htmlspecialchars($entry->action_type); ?>
                                        </div>
                                    </div>
                                    <span class="soft-badge info"><?php echo htmlspecialchars($entry->target_type); ?></span>
                                </div>
                                <div class="course-meta mt-2">
                                    <span><i class="bi bi-hash"></i> <?php echo (int) ($entry->target_id ?? 0); ?></span>
                                    <span><i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </details>

    <div class="row g-4">
        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Usuarios recientes</h2>
                        <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
                    </div>

                    <?php if (empty($recentUsers)): ?>
                        <div class="empty-state">Todavia no hay usuarios recientes para mostrar.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($recentUsers as $user): ?>
                                <article class="stack-item">
                                    <div class="split-head">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-token"><?php echo strtoupper(substr($user->nombre, 0, 1)); ?></span>
                                            <div>
                                                <p class="stack-item-title"><?php echo htmlspecialchars(trim($user->nombre . ' ' . $user->apellido)); ?></p>
                                                <div class="stack-item-subtitle"><?php echo htmlspecialchars($user->email); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="soft-badge">
                                                <?php
                                                if ($user->es_admin_institucion) {
                                                    echo 'Admin';
                                                } elseif ($user->es_profesor) {
                                                    echo 'Profesor';
                                                } else {
                                                    echo 'Estudiante';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="responsive-actions mt-2">
                                        <a href="<?php echo url('/admin/usuarios/edit/' . $user->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Cursos recientes</h2>
                        <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
                    </div>

                    <?php if (empty($recentCourses)): ?>
                        <div class="empty-state">Todavia no hay cursos recientes para mostrar.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($recentCourses as $course): ?>
                                <?php $editorialState = app_course_editorial_snapshot($course); ?>
                                <?php $catalogStatus = app_course_catalog_status($course); ?>
                                <?php $publishedLessons = (int) ($course->published_lessons ?? 0); ?>
                                <article class="stack-item">
                                    <div>
                                        <p class="stack-item-title"><?php echo htmlspecialchars($course->titulo); ?></p>
                                        <div class="stack-item-subtitle">
                                            Creado el <?php echo date('d/m/Y', strtotime($course->fecha_creacion)); ?>
                                        </div>
                                        <div class="mt-2 badge-row">
                                            <span class="soft-badge badge-<?php echo htmlspecialchars($editorialState['tone'] ?? 'info'); ?>">
                                                <?php echo htmlspecialchars($editorialState['label'] ?? 'En progreso'); ?>
                                            </span>
                                            <span class="soft-badge <?php echo htmlspecialchars($catalogStatus['tone']); ?>">
                                                <?php echo htmlspecialchars($catalogStatus['label']); ?>
                                            </span>
                                            <span class="soft-badge <?php echo htmlspecialchars($course->admin_focus_tone ?? 'info'); ?>">
                                                <?php echo htmlspecialchars($course->admin_focus_label ?? 'Controlado'); ?>
                                            </span>
                                            <span class="soft-badge"><?php echo (int) ($course->total_lecciones ?? 0); ?> lecciones</span>
                                            <span class="soft-badge"><?php echo $publishedLessons; ?> publicadas</span>
                                            <span class="soft-badge"><?php echo (int) ($course->total_actividades ?? 0); ?> actividades</span>
                                            <?php if ((int) ($course->open_tickets ?? 0) > 0): ?>
                                                <span class="soft-badge warning"><?php echo (int) ($course->open_tickets ?? 0); ?> tickets abiertos</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted mt-2"><?php echo htmlspecialchars($catalogStatus['hint']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($course->admin_focus_hint ?? ($editorialState['hint'] ?? '')); ?></div>
                                    </div>
                                    <div class="responsive-actions stack-item-actions">
                                        <a href="<?php echo url('/admin/cursos/edit/' . $course->id); ?>" class="btn btn-outline-secondary btn-sm" aria-label="Editar curso <?php echo htmlspecialchars($course->titulo); ?>">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <a href="<?php echo url('/admin/cursos/estructura/' . $course->id); ?>" class="btn btn-outline-secondary btn-sm" aria-label="Estructura de <?php echo htmlspecialchars($course->titulo); ?>">
                                            <i class="bi bi-diagram-3"></i> Estructura
                                        </a>
                                        <?php if ((int) ($course->open_tickets ?? 0) > 0): ?>
                                            <a href="<?php echo url('/admin/tickets?course_id=' . (int) $course->id); ?>" class="btn btn-outline-secondary btn-sm" aria-label="Tickets del curso <?php echo htmlspecialchars($course->titulo); ?>">
                                                <i class="bi bi-life-preserver"></i> Tickets
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Focos docentes</h2>
                        <a href="<?php echo url('/admin/profesores'); ?>" class="btn btn-outline-primary btn-sm">Ver supervision</a>
                    </div>

                    <?php if (empty($teacherHotspots)): ?>
                        <div class="empty-state">Todavia no hay profesores con carga para destacar.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($teacherHotspots as $teacher): ?>
                                <article class="stack-item">
                                    <div class="split-head">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-token"><?php echo strtoupper(substr($teacher->nombre, 0, 1)); ?></span>
                                            <div>
                                                <p class="stack-item-title"><?php echo htmlspecialchars(trim($teacher->nombre . ' ' . $teacher->apellido)); ?></p>
                                                <div class="stack-item-subtitle"><?php echo htmlspecialchars($teacher->email); ?></div>
                                            </div>
                                        </div>
                                        <div class="badge-row badge-row-end">
                                            <span class="soft-badge <?php echo ((int) ($teacher->tickets_abiertos ?? 0) > 0) ? 'warning' : 'success'; ?>">
                                                <?php echo (int) ($teacher->tickets_abiertos ?? 0); ?> tickets abiertos
                                            </span>
                                            <span class="soft-badge info"><?php echo (int) ($teacher->total_estudiantes ?? 0); ?> alumnos</span>
                                            <span class="soft-badge <?php echo htmlspecialchars($teacher->admin_focus_tone ?? 'info'); ?>">
                                                <?php echo htmlspecialchars($teacher->admin_focus_label ?? 'Control operativo'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="course-meta mt-2">
                                        <span><i class="bi bi-book"></i> <?php echo (int) ($teacher->total_cursos ?? 0); ?> cursos</span>
                                        <span><i class="bi bi-people"></i> <?php echo (int) ($teacher->total_estudiantes ?? 0); ?> estudiantes</span>
                                    </div>
                                    <div class="small text-muted mt-2"><?php echo htmlspecialchars($teacher->admin_focus_hint ?? ''); ?></div>
                                    <?php if (!empty($teacher->hotspot_course_title)): ?>
                                        <div class="small text-muted mt-1"><strong>Curso foco:</strong> <?php echo htmlspecialchars($teacher->hotspot_course_title); ?></div>
                                    <?php endif; ?>
                                    <div class="responsive-actions stack-item-actions mt-2">
                                        <a href="<?php echo url('/admin/cursos?teacher=' . (int) $teacher->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-journal-bookmark"></i> Cursos
                                        </a>
                                        <a href="<?php echo url('/admin/tickets?owner_id=' . (int) $teacher->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-life-preserver"></i> Tickets
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Tickets recientes</h2>
                        <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-primary btn-sm">Abrir bandeja</a>
                    </div>

                    <?php if (empty($recentTickets)): ?>
                        <div class="empty-state">Todavia no hay tickets recientes para mostrar.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($recentTickets as $ticket): ?>
                                <article class="stack-item">
                                    <div class="split-head">
                                        <div>
                                            <p class="stack-item-title"><?php echo htmlspecialchars($ticket->issue_type); ?></p>
                                            <div class="stack-item-subtitle">
                                                <?php echo htmlspecialchars(trim(($ticket->nombre ?? '') . ' ' . ($ticket->apellido ?? ''))); ?>
                                                &middot; <?php echo htmlspecialchars(adminDashboardTicketRole($ticket)); ?>
                                            </div>
                                        </div>
                                        <div class="badge-row badge-row-end">
                                            <span class="soft-badge <?php echo $ticket->priority_tone ?? 'info'; ?>">
                                                <?php echo htmlspecialchars($ticket->priority_label ?? 'Media'); ?>
                                            </span>
                                            <span class="soft-badge <?php echo adminDashboardTicketTone($ticket->status); ?>">
                                                <?php echo htmlspecialchars($ticket->status); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="course-meta mt-2">
                                        <span><i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($ticket->created_at)); ?></span>
                                        <span><i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($ticket->context_type); ?></span>
                                        <?php if (!empty($ticket->curso_titulo)): ?>
                                            <span><i class="bi bi-book"></i> <?php echo htmlspecialchars($ticket->curso_titulo); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted mt-2"><?php echo htmlspecialchars($ticket->recommended_action ?? ''); ?></div>
                                    <div class="responsive-actions mt-2">
                                        <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i> Ver ticket
                                        </a>
                                        <?php if (!empty($ticket->curso_id)): ?>
                                            <a href="<?php echo url('/admin/cursos/estructura/' . (int) $ticket->curso_id); ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-diagram-3"></i> Estructura
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Focos del catalogo</h2>
                        <a href="<?php echo url('/admin/cursos?editorial=visible_con_ajustes'); ?>" class="btn btn-outline-primary btn-sm">Ver catalogo</a>
                    </div>

                    <?php if (empty($catalogHotspots)): ?>
                        <div class="empty-state">No hay cursos recientes que esten pidiendo revision fuerte.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($catalogHotspots as $course): ?>
                                <?php $editorialState = app_course_editorial_snapshot($course); ?>
                                <?php $publishedLessons = (int) ($course->published_lessons ?? 0); ?>
                                <?php $catalogStatus = app_course_catalog_status($course); ?>
                                <article class="stack-item">
                                    <div class="split-head">
                                        <div>
                                            <p class="stack-item-title"><?php echo htmlspecialchars($course->titulo); ?></p>
                                            <div class="stack-item-subtitle">
                                                <?php echo htmlspecialchars(trim(($course->profesor_nombre ?? '') . ' ' . ($course->profesor_apellido ?? '')) ?: 'Sin responsable'); ?>
                                            </div>
                                        </div>
                                        <div class="badge-row badge-row-end">
                                            <span class="soft-badge badge-<?php echo htmlspecialchars($editorialState['tone'] ?? 'info'); ?>">
                                                <?php echo htmlspecialchars($editorialState['label'] ?? 'En progreso'); ?>
                                            </span>
                                            <span class="soft-badge <?php echo htmlspecialchars($course->admin_focus_tone ?? 'info'); ?>">
                                                <?php echo htmlspecialchars($course->admin_focus_label ?? 'Controlado'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="course-meta mt-2">
                                        <span><i class="bi bi-journal-richtext"></i> <?php echo (int) ($course->total_lecciones ?? 0); ?> lecciones</span>
                                        <span><i class="bi bi-lightning"></i> <?php echo (int) ($course->total_actividades ?? 0); ?> actividades</span>
                                        <span><i class="bi bi-broadcast"></i> <?php echo htmlspecialchars($catalogStatus['label']); ?></span>
                                        <?php if ((int) ($course->open_tickets ?? 0) > 0): ?>
                                            <span><i class="bi bi-life-preserver"></i> <?php echo (int) ($course->open_tickets ?? 0); ?> tickets</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted mt-2"><?php echo htmlspecialchars($catalogStatus['hint']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($course->admin_focus_hint ?? ($editorialState['hint'] ?? '')); ?></div>
                                    <div class="responsive-actions stack-item-actions mt-2">
                                        <a href="<?php echo url('/admin/cursos/edit/' . $course->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <a href="<?php echo url('/admin/cursos/estructura/' . $course->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-diagram-3"></i> Estructura
                                        </a>
                                        <?php if ((int) ($course->open_tickets ?? 0) > 0): ?>
                                            <a href="<?php echo url('/admin/tickets?course_id=' . (int) $course->id); ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-life-preserver"></i> Tickets
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Soporte en foco</h2>
                        <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-primary btn-sm">Abrir soporte</a>
                    </div>
                    <?php if (empty($supportHotspots)): ?>
                        <div class="empty-state">No hay cursos con soporte abierto para destacar ahora mismo.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($supportHotspots as $supportCourse): ?>
                                <article class="stack-item">
                                    <div class="split-head">
                                        <div>
                                            <p class="stack-item-title"><?php echo htmlspecialchars($supportCourse->titulo); ?></p>
                                            <div class="stack-item-subtitle"><?php echo htmlspecialchars($supportCourse->focus_hint ?? ''); ?></div>
                                        </div>
                                        <div class="badge-row badge-row-end">
                                            <span class="soft-badge <?php echo htmlspecialchars($supportCourse->focus_tone ?? 'info'); ?>">
                                                <?php echo htmlspecialchars($supportCourse->focus_label ?? 'Con seguimiento'); ?>
                                            </span>
                                            <span class="soft-badge warning"><?php echo (int) ($supportCourse->open_total ?? 0); ?> abiertos</span>
                                        </div>
                                    </div>
                                    <div class="course-meta mt-2">
                                        <span><i class="bi bi-bell"></i> <?php echo (int) ($supportCourse->nuevos ?? 0); ?> nuevos</span>
                                    </div>
                                    <div class="responsive-actions stack-item-actions mt-2">
                                        <a href="<?php echo url('/admin/tickets?course_id=' . (int) $supportCourse->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-life-preserver"></i> Tickets
                                        </a>
                                        <a href="<?php echo url('/admin/cursos/estructura/' . (int) $supportCourse->id); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-diagram-3"></i> Estructura
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
