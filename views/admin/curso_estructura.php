<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../models/Curso.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/admin'); ?>">Admin</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/admin/cursos'); ?>">Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Estructura</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-diagram-3"></i> Supervisión de estructura</span>
        <h1 class="page-title"><?php echo htmlspecialchars($course->titulo); ?></h1>
        <p class="page-subtitle">Vista administrativa completa del recorrido, sus piezas de teoria y sus actividades.</p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a cursos
            </a>
            <a href="<?php echo url('/admin/cursos/edit/' . (int) $course->id); ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Editar curso
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Nivel</div>
                <div class="metric-value"><?php echo htmlspecialchars(Curso::formatearRangoNivel($course)); ?></div>
                <div class="metric-note"><?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($course)); ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Lecciones</div>
                <div class="metric-value"><?php echo count($lecciones); ?></div>
                <div class="metric-note">Estructura actual del curso.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Visibilidad</div>
                <div class="metric-value"><?php echo !empty($course->es_publico) ? 'Publico' : 'Privado'; ?></div>
                <div class="metric-note"><?php echo !empty($course->inscripcion_abierta) ? 'Inscripcion abierta' : 'Inscripcion cerrada'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Estado</div>
                <div class="metric-value"><?php echo htmlspecialchars(ucfirst($course->estado ?? 'activo')); ?></div>
                <div class="metric-note">Control operativo del curso.</div>
            </div>
        </div>
    </section>

    <?php if (empty($lecciones)): ?>
        <div class="panel empty-state-card">
            <div class="panel-body">
                <span class="empty-state-icon"><i class="bi bi-journal-x"></i></span>
                <div class="empty-state-copy">Este curso todavia no tiene lecciones.</div>
            </div>
        </div>
    <?php else: ?>
        <section>
            <div class="section-title">
                <h2>Lecciones del curso</h2>
                <span class="soft-badge"><i class="bi bi-collection"></i> Vista de control</span>
            </div>

            <div class="row g-4">
                <?php foreach ($lecciones as $leccion): ?>
                    <div class="col-xl-6">
                        <article class="surface-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                                    <div>
                                        <div class="small text-muted mb-1">Leccion <?php echo (int) $leccion->orden; ?></div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($leccion->titulo); ?></h3>
                                        <div class="small text-muted"><?php echo htmlspecialchars($leccion->descripcion ?: 'Sin descripcion.'); ?></div>
                                    </div>
                                    <span class="soft-badge"><?php echo htmlspecialchars($leccion->estado ?? 'borrador'); ?></span>
                                </div>

                                <div class="course-meta mb-3">
                                    <span><i class="bi bi-book"></i> <?php echo count($leccion->teorias_detalle ?? []); ?> teorias</span>
                                    <span><i class="bi bi-lightning"></i> <?php echo count($leccion->actividades_detalle ?? []); ?> actividades</span>
                                    <?php if (!empty($leccion->duracion_minutos)): ?>
                                        <span><i class="bi bi-clock"></i> <?php echo (int) $leccion->duracion_minutos; ?> min</span>
                                    <?php endif; ?>
                                </div>

                                <div class="activity-preview-stack">
                                    <div class="activity-preview-card">
                                        <div class="fw-semibold mb-2">Teoria</div>
                                        <?php if (empty($leccion->teorias_detalle)): ?>
                                            <div class="small text-muted">No tiene teoria.</div>
                                        <?php else: ?>
                                            <ul class="quality-checklist-list mb-0">
                                                <?php foreach (array_slice($leccion->teorias_detalle, 0, 4) as $teoria): ?>
                                                    <li><?php echo htmlspecialchars($teoria->titulo); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <div class="activity-preview-card">
                                        <div class="fw-semibold mb-2">Actividades</div>
                                        <?php if (empty($leccion->actividades_detalle)): ?>
                                            <div class="small text-muted">No tiene actividades.</div>
                                        <?php else: ?>
                                            <ul class="quality-checklist-list mb-0">
                                                <?php foreach (array_slice($leccion->actividades_detalle, 0, 4) as $actividad): ?>
                                                    <li><?php echo htmlspecialchars($actividad->titulo); ?> <span class="text-muted">(<?php echo htmlspecialchars($actividad->tipo_actividad); ?>)</span></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
