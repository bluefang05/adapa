<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../models/Curso.php'; ?>

<?php
$courseEditorialState = $courseEditorialState ?? app_course_editorial_snapshot($course);
$structureSummary = $structureSummary ?? [
    'total_theories' => 0,
    'total_activities' => 0,
    'published_lessons' => 0,
    'ready_lessons' => 0,
    'gap_lessons' => 0,
    'next_focus' => null,
];
$nextFocusLesson = $structureSummary['next_focus'] ?? null;
$courseTicketSummary = $courseTicketSummary ?? (object) [
    'total' => 0,
    'open_total' => 0,
    'nuevos' => 0,
    'en_revision' => 0,
    'context_leccion' => 0,
    'context_actividad' => 0,
];
$lessonsWithSupport = 0;
$hotLessons = 0;
foreach ($lecciones as $leccionMeta) {
    if ((int) (($leccionMeta->support_meta->open_total ?? 0)) > 0) {
        $lessonsWithSupport++;
    }
    if ((int) (($leccionMeta->support_meta->open_total ?? 0)) >= 3) {
        $hotLessons++;
    }
}
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/admin'); ?>">Admin</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/admin/cursos'); ?>">Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Estructura</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-diagram-3"></i> Supervision de estructura</span>
        <h1 class="page-title"><?php echo htmlspecialchars($course->titulo); ?></h1>
        <p class="page-subtitle">Lectura administrativa del recorrido, la salud editorial y los huecos que todavia necesitan atencion.</p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a cursos
            </a>
            <a href="<?php echo url('/admin/cursos/edit/' . (int) $course->id); ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Editar curso
            </a>
            <a href="<?php echo url('/admin/tickets?course_id=' . (int) $course->id); ?>" class="btn btn-outline-primary">
                <i class="bi bi-life-preserver"></i> Ver tickets del curso
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
                <div class="metric-note">Recorrido actual del curso.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Teorias</div>
                <div class="metric-value"><?php echo (int) ($structureSummary['total_theories'] ?? 0); ?></div>
                <div class="metric-note">Piezas conceptuales disponibles.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Actividades</div>
                <div class="metric-value"><?php echo (int) ($structureSummary['total_activities'] ?? 0); ?></div>
                <div class="metric-note">Practica creada para estudiantes.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Responsable</div>
                <div class="metric-value">
                    <?php if (!empty($course->profesor_nombre) || !empty($course->profesor_apellido)): ?>
                        <?php echo htmlspecialchars(trim(($course->profesor_nombre ?? '') . ' ' . ($course->profesor_apellido ?? ''))); ?>
                    <?php else: ?>
                        Sin asignar
                    <?php endif; ?>
                </div>
                <div class="metric-note">Persona a cargo del curso.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Visibilidad</div>
                <div class="metric-value"><?php echo !empty($course->es_publico) ? 'Publico' : 'Privado'; ?></div>
                <div class="metric-note"><?php echo !empty($course->inscripcion_abierta) ? 'Inscripcion abierta' : 'Inscripcion cerrada'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Tickets abiertos</div>
                <div class="metric-value"><?php echo (int) ($courseTicketSummary->open_total ?? 0); ?></div>
                <div class="metric-note">Incidencias activas ligadas a este curso.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Lecciones con soporte</div>
                <div class="metric-value"><?php echo (int) $lessonsWithSupport; ?></div>
                <div class="metric-note"><?php echo (int) $hotLessons; ?> estan realmente calientes.</div>
            </div>
        </div>
    </section>

    <section class="surface-card mb-4">
        <div class="card-body">
            <div class="section-title">
                <h2>Lectura editorial del curso</h2>
                <span class="soft-badge badge-<?php echo htmlspecialchars($courseEditorialState['tone'] ?? 'info'); ?>">
                    <?php echo htmlspecialchars($courseEditorialState['label'] ?? 'En progreso'); ?>
                </span>
            </div>
            <section class="production-hint-card tone-<?php echo htmlspecialchars($courseEditorialState['tone'] ?? 'info'); ?>">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                    <div class="production-hint-title"><?php echo htmlspecialchars($courseEditorialState['label'] ?? 'En progreso'); ?></div>
                    <span class="soft-badge"><?php echo (int) ($courseEditorialState['progress'] ?? 0); ?>%</span>
                </div>
                <div class="readiness-meter mb-2">
                    <span style="width: <?php echo (int) ($courseEditorialState['progress'] ?? 0); ?>%"></span>
                </div>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($courseEditorialState['hint'] ?? ''); ?></p>
            </section>

            <div class="builder-stage-grid mt-4">
                <article class="builder-stage-card">
                    <div class="builder-stage-icon"><i class="bi bi-check2-square"></i></div>
                    <div class="builder-stage-body">
                        <div class="builder-stage-title">Lecciones publicadas</div>
                        <div class="builder-stage-copy"><?php echo (int) ($structureSummary['published_lessons'] ?? 0); ?> de <?php echo count($lecciones); ?> ya estan visibles.</div>
                    </div>
                </article>
                <article class="builder-stage-card">
                    <div class="builder-stage-icon"><i class="bi bi-patch-check"></i></div>
                    <div class="builder-stage-body">
                        <div class="builder-stage-title">Listas para revisar</div>
                        <div class="builder-stage-copy"><?php echo (int) ($structureSummary['ready_lessons'] ?? 0); ?> lecciones ya tienen base suficiente para revision editorial.</div>
                    </div>
                </article>
                <article class="builder-stage-card">
                    <div class="builder-stage-icon"><i class="bi bi-life-preserver"></i></div>
                    <div class="builder-stage-body">
                        <div class="builder-stage-title">Soporte del curso</div>
                        <div class="builder-stage-copy">
                            <?php echo (int) ($courseTicketSummary->nuevos ?? 0); ?> nuevos y
                            <?php echo (int) ($courseTicketSummary->en_revision ?? 0); ?> en revision.
                        </div>
                    </div>
                </article>
                <article class="builder-stage-card<?php echo $nextFocusLesson ? ' is-priority' : ''; ?>">
                    <div class="builder-stage-icon"><i class="bi bi-signpost-split"></i></div>
                    <div class="builder-stage-body">
                        <div class="builder-stage-title">Siguiente foco</div>
                        <div class="builder-stage-copy">
                            <?php if ($nextFocusLesson): ?>
                                Leccion <?php echo (int) $nextFocusLesson->orden; ?>: <?php echo htmlspecialchars($nextFocusLesson->titulo); ?>.
                            <?php else: ?>
                                No hay huecos criticos de estructura en este curso.
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
            <?php if ((int) ($courseTicketSummary->total ?? 0) > 0): ?>
                <div class="course-meta mt-4">
                    <span><i class="bi bi-chat-dots"></i> <?php echo (int) ($courseTicketSummary->total ?? 0); ?> tickets historicos</span>
                    <span><i class="bi bi-journal-richtext"></i> <?php echo (int) ($courseTicketSummary->context_leccion ?? 0); ?> de leccion</span>
                    <span><i class="bi bi-lightning-charge"></i> <?php echo (int) ($courseTicketSummary->context_actividad ?? 0); ?> de actividad</span>
                </div>
            <?php endif; ?>
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
                    <?php $lessonEditorialState = $leccion->editorial_snapshot ?? app_lesson_editorial_snapshot($leccion); ?>
                    <div class="col-xl-6">
                        <article class="surface-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                                    <div>
                                        <div class="small text-muted mb-1">Leccion <?php echo (int) $leccion->orden; ?></div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($leccion->titulo); ?></h3>
                                        <div class="small text-muted"><?php echo htmlspecialchars($leccion->descripcion ?: 'Sin descripcion.'); ?></div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="soft-badge"><?php echo htmlspecialchars($leccion->estado ?? 'borrador'); ?></span>
                                        <span class="soft-badge badge-<?php echo htmlspecialchars($lessonEditorialState['tone'] ?? 'info'); ?>">
                                            <?php echo htmlspecialchars($lessonEditorialState['label'] ?? 'En progreso'); ?>
                                        </span>
                                        <span class="soft-badge <?php echo htmlspecialchars($leccion->support_meta->tone ?? 'success'); ?>">
                                            <?php echo htmlspecialchars($leccion->support_meta->label ?? 'Sin tickets'); ?>
                                        </span>
                                    </div>
                                </div>

                                <section class="production-hint-card tone-<?php echo htmlspecialchars($lessonEditorialState['tone'] ?? 'info'); ?> mb-3">
                                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                                        <div class="production-hint-title"><?php echo htmlspecialchars($lessonEditorialState['label'] ?? 'En progreso'); ?></div>
                                        <span class="soft-badge"><?php echo (int) ($lessonEditorialState['progress'] ?? 0); ?>%</span>
                                    </div>
                                    <div class="readiness-meter mb-2">
                                        <span style="width: <?php echo (int) ($lessonEditorialState['progress'] ?? 0); ?>%"></span>
                                    </div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($lessonEditorialState['hint'] ?? ''); ?></div>
                                </section>

                                <div class="course-meta mb-3">
                                    <span><i class="bi bi-book"></i> <?php echo count($leccion->teorias_detalle ?? []); ?> teorias</span>
                                    <span><i class="bi bi-lightning"></i> <?php echo count($leccion->actividades_detalle ?? []); ?> actividades</span>
                                    <span><i class="bi bi-life-preserver"></i> <?php echo (int) ($leccion->support_meta->open_total ?? 0); ?> abiertas</span>
                                    <?php if ((int) ($leccion->support_meta->nuevos ?? 0) > 0): ?>
                                        <span><i class="bi bi-bell"></i> <?php echo (int) ($leccion->support_meta->nuevos ?? 0); ?> nuevas</span>
                                    <?php endif; ?>
                                    <?php if (!empty($leccion->duracion_minutos)): ?>
                                        <span><i class="bi bi-clock"></i> <?php echo (int) $leccion->duracion_minutos; ?> min</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted mb-3"><?php echo htmlspecialchars($leccion->support_meta->hint ?? ''); ?></div>

                                <div class="builder-stage-grid mb-3">
                                    <article class="builder-stage-card">
                                        <div class="builder-stage-icon"><i class="bi bi-lightbulb"></i></div>
                                        <div class="builder-stage-body">
                                            <div class="builder-stage-title">Siguiente paso</div>
                                            <div class="builder-stage-copy"><?php echo htmlspecialchars($lessonEditorialState['action_label'] ?? 'Revisar leccion'); ?></div>
                                        </div>
                                    </article>
                                    <article class="builder-stage-card">
                                        <div class="builder-stage-icon"><i class="bi bi-hourglass-split"></i></div>
                                        <div class="builder-stage-body">
                                            <div class="builder-stage-title">Hueco principal</div>
                                            <div class="builder-stage-copy">
                                                <?php if (($lessonEditorialState['label'] ?? '') === 'Sin contexto'): ?>
                                                    Falta una teoria que abra la leccion.
                                                <?php elseif (($lessonEditorialState['label'] ?? '') === 'Sin practica'): ?>
                                                    La teoria existe, pero falta convertirla en practica.
                                                <?php else: ?>
                                                    La estructura base ya esta montada.
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <?php if ((int) ($leccion->support_meta->open_total ?? 0) > 0): ?>
                                    <div class="mb-3 d-flex gap-2 flex-wrap">
                                        <a href="<?php echo url('/admin/tickets?course_id=' . (int) $course->id . '&context=leccion'); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-life-preserver"></i> Ver tickets de leccion
                                        </a>
                                        <?php if ((int) ($leccion->support_meta->actividad ?? 0) > 0): ?>
                                            <a href="<?php echo url('/admin/tickets?course_id=' . (int) $course->id . '&context=actividad'); ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-lightning"></i> Tickets de actividad
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

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
