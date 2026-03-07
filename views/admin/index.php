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
?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-speedometer2"></i> Control institucional</span>
        <h1 class="page-title">Administra usuarios, cursos y crecimiento desde una sola vista.</h1>
        <p class="page-subtitle">
            Este panel resume la salud operativa de tu instancia para que puedas detectar actividad reciente sin entrar a cada modulo.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-primary">
                <i class="bi bi-people"></i> Gestionar usuarios
            </a>
            <a href="<?php echo url('/admin/profesores'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-person-workspace"></i> Supervisar profesores
            </a>
            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-journal-bookmark"></i> Revisar cursos
            </a>
            <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-life-preserver"></i> Bandeja de tickets
            </a>
            <a href="<?php echo url('/admin/actividad'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-activity"></i> Bitacora
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Usuarios</div>
                <div class="metric-value"><?php echo (int) $totalUsers; ?></div>
                <div class="metric-note">Personas registradas en la instancia.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Profesores</div>
                <div class="metric-value"><?php echo (int) $totalProfessors; ?></div>
                <div class="metric-note">Docentes con acceso activo al panel.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Estudiantes</div>
                <div class="metric-value"><?php echo (int) $totalStudents; ?></div>
                <div class="metric-note">Base activa de aprendizaje.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Cursos</div>
                <div class="metric-value"><?php echo (int) $totalCourses; ?></div>
                <div class="metric-note">Oferta creada dentro de la plataforma.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Usuarios inactivos</div>
                <div class="metric-value"><?php echo (int) $inactiveUsers; ?></div>
                <div class="metric-note">Cuentas bloqueadas o desactivadas.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Cursos publicos</div>
                <div class="metric-value"><?php echo (int) $publicCourses; ?></div>
                <div class="metric-note">Oferta visible para exploracion o acceso abierto.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Tickets abiertos</div>
                <div class="metric-value"><?php echo (int) $openTickets; ?></div>
                <div class="metric-note">Incidencias sin cerrar en la instancia.</div>
            </div>
        </div>
    </section>

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
                                    <div class="mt-2">
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
                                <article class="stack-item">
                                    <div>
                                        <p class="stack-item-title"><?php echo htmlspecialchars($course->titulo); ?></p>
                                        <div class="stack-item-subtitle">
                                            Creado el <?php echo date('d/m/Y', strtotime($course->fecha_creacion)); ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="<?php echo url('/admin/cursos/edit/' . $course->id); ?>" class="btn btn-outline-secondary btn-sm" aria-label="Editar curso <?php echo htmlspecialchars($course->titulo); ?>">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <a href="<?php echo url('/estudiante/cursos/' . $course->id . '/lecciones'); ?>" class="btn btn-outline-secondary btn-sm" aria-label="Vista alumno de <?php echo htmlspecialchars($course->titulo); ?>">
                                            <i class="bi bi-eye"></i> Vista
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
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="avatar-token"><?php echo strtoupper(substr($teacher->nombre, 0, 1)); ?></span>
                                            <div>
                                                <p class="stack-item-title"><?php echo htmlspecialchars(trim($teacher->nombre . ' ' . $teacher->apellido)); ?></p>
                                                <div class="stack-item-subtitle"><?php echo htmlspecialchars($teacher->email); ?></div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="soft-badge <?php echo ((int) ($teacher->tickets_abiertos ?? 0) > 0) ? 'warning' : 'success'; ?>">
                                                <?php echo (int) ($teacher->tickets_abiertos ?? 0); ?> tickets abiertos
                                            </span>
                                            <span class="soft-badge info"><?php echo (int) ($teacher->total_estudiantes ?? 0); ?> alumnos</span>
                                        </div>
                                    </div>
                                    <div class="course-meta mt-2">
                                        <span><i class="bi bi-book"></i> <?php echo (int) ($teacher->total_cursos ?? 0); ?> cursos</span>
                                        <span><i class="bi bi-people"></i> <?php echo (int) ($teacher->total_estudiantes ?? 0); ?> estudiantes</span>
                                    </div>
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
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
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <p class="stack-item-title"><?php echo htmlspecialchars($ticket->issue_type); ?></p>
                                            <div class="stack-item-subtitle">
                                                <?php echo htmlspecialchars(trim(($ticket->nombre ?? '') . ' ' . ($ticket->apellido ?? ''))); ?>
                                                · <?php echo htmlspecialchars(adminDashboardTicketRole($ticket)); ?>
                                            </div>
                                        </div>
                                        <span class="soft-badge <?php echo adminDashboardTicketTone($ticket->status); ?>">
                                            <?php echo htmlspecialchars($ticket->status); ?>
                                        </span>
                                    </div>
                                    <div class="course-meta mt-2">
                                        <span><i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($ticket->created_at)); ?></span>
                                        <span><i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($ticket->context_type); ?></span>
                                        <?php if (!empty($ticket->curso_titulo)): ?>
                                            <span><i class="bi bi-book"></i> <?php echo htmlspecialchars($ticket->curso_titulo); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i> Ver ticket
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

    <div class="row g-4 mt-1">
        <div class="col-12">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Bitacora admin reciente</h2>
                        <a href="<?php echo url('/admin/actividad'); ?>" class="btn btn-outline-primary btn-sm">Abrir bitacora</a>
                    </div>

                    <?php if (empty($recentAdminActivity)): ?>
                        <div class="empty-state">Todavia no hay actividad administrativa registrada.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($recentAdminActivity as $entry): ?>
                                <article class="stack-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <p class="stack-item-title"><?php echo htmlspecialchars($entry->description); ?></p>
                                            <div class="stack-item-subtitle">
                                                <?php echo htmlspecialchars(trim(($entry->nombre ?? '') . ' ' . ($entry->apellido ?? ''))); ?>
                                                · <?php echo htmlspecialchars($entry->action_type); ?>
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
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
