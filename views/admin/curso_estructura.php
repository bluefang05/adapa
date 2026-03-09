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
$course->published_lessons = (int) ($structureSummary['published_lessons'] ?? 0);
$catalogStatus = app_course_catalog_status($course);
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

    <section class="page-hero content-hero mb-4">
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
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-signpost-split"></i> <?php echo htmlspecialchars(Curso::formatearRangoNivel($course)); ?></span>
            <span class="soft-badge"><i class="bi bi-collection"></i> <?php echo count($lecciones); ?> lecciones</span>
            <span class="soft-badge"><i class="bi bi-book"></i> <?php echo (int) ($structureSummary['total_theories'] ?? 0); ?> teorias</span>
            <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo (int) ($structureSummary['total_activities'] ?? 0); ?> actividades</span>
            <span class="soft-badge"><i class="bi bi-broadcast"></i> <?php echo htmlspecialchars($catalogStatus['short_label']); ?></span>
            <span class="soft-badge <?php echo ((int) ($courseTicketSummary->open_total ?? 0) > 0) ? 'warning' : 'success'; ?>"><i class="bi bi-life-preserver"></i> <?php echo (int) ($courseTicketSummary->open_total ?? 0); ?> tickets abiertos</span>
            <span class="soft-badge <?php echo $lessonsWithSupport > 0 ? 'warning' : 'success'; ?>"><i class="bi bi-bell"></i> <?php echo (int) $lessonsWithSupport; ?> lecciones con soporte</span>
            <span class="soft-badge"><i class="bi bi-person-workspace"></i> <?php echo !empty($course->profesor_nombre) || !empty($course->profesor_apellido) ? htmlspecialchars(trim(($course->profesor_nombre ?? '') . ' ' . ($course->profesor_apellido ?? ''))) : 'Sin asignar'; ?></span>
        </div>
    </section>

    <details class="panel page-assist-card mb-4">
        <summary class="page-assist-summary">
            <div>
                <div class="metric-label">Lectura editorial</div>
                <div class="fw-semibold mt-1"><?php echo htmlspecialchars($courseEditorialState['label'] ?? 'En progreso'); ?> - <?php echo (int) ($courseEditorialState['progress'] ?? 0); ?>%</div>
                <div class="small text-muted mt-1"><?php echo htmlspecialchars($courseEditorialState['hint'] ?? ''); ?></div>
            </div>
            <span class="soft-badge badge-<?php echo htmlspecialchars($courseEditorialState['tone'] ?? 'info'); ?>">4 focos</span>
        </summary>
        <div class="panel-body pt-0 page-assist-body">
            <div class="alert context-note mb-0">
                <div class="split-head mb-2">
                    <div class="fw-semibold"><?php echo htmlspecialchars($courseEditorialState['label'] ?? 'En progreso'); ?></div>
                    <span class="soft-badge"><?php echo (int) ($courseEditorialState['progress'] ?? 0); ?>%</span>
                </div>
                <div class="readiness-meter mb-2">
                    <span style="width: <?php echo (int) ($courseEditorialState['progress'] ?? 0); ?>%"></span>
                </div>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($courseEditorialState['hint'] ?? ''); ?></p>
            </div>

            <div class="builder-stage-grid">
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
                <div class="course-meta">
                    <span><i class="bi bi-chat-dots"></i> <?php echo (int) ($courseTicketSummary->total ?? 0); ?> tickets historicos</span>
                    <span><i class="bi bi-journal-richtext"></i> <?php echo (int) ($courseTicketSummary->context_leccion ?? 0); ?> de leccion</span>
                    <span><i class="bi bi-lightning-charge"></i> <?php echo (int) ($courseTicketSummary->context_actividad ?? 0); ?> de actividad</span>
                </div>
            <?php endif; ?>
        </div>
    </details>

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
                                <div class="split-head mb-3">
                                    <div>
                                        <div class="small text-muted mb-1">Leccion <?php echo (int) $leccion->orden; ?></div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($leccion->titulo); ?></h3>
                                        <div class="small text-muted"><?php echo htmlspecialchars($leccion->descripcion ?: 'Sin descripcion.'); ?></div>
                                    </div>
                                    <div class="badge-row badge-row-end">
                                        <span class="soft-badge badge-<?php echo htmlspecialchars($lessonEditorialState['tone'] ?? 'info'); ?>">
                                            <?php echo htmlspecialchars($lessonEditorialState['label'] ?? 'En progreso'); ?>
                                        </span>
                                        <span class="soft-badge <?php echo htmlspecialchars($leccion->support_meta->tone ?? 'success'); ?>">
                                            <?php echo htmlspecialchars($leccion->support_meta->label ?? 'Sin tickets'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="small text-muted mb-3">Tecnico: <?php echo htmlspecialchars($leccion->estado ?? 'borrador'); ?></div>

                                <div class="alert context-note mb-3">
                                    <div class="split-head">
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($lessonEditorialState['label'] ?? 'En progreso'); ?></div>
                                            <div class="small text-muted mt-1"><?php echo htmlspecialchars($lessonEditorialState['hint'] ?? ''); ?></div>
                                            <?php if (!empty($leccion->support_meta->hint)): ?>
                                                <div class="small text-muted mt-2"><strong>Soporte:</strong> <?php echo htmlspecialchars($leccion->support_meta->hint); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="soft-badge"><?php echo (int) ($lessonEditorialState['progress'] ?? 0); ?>%</span>
                                    </div>
                                </div>

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
                                <div class="alert context-note mb-3">
                                    <div class="fw-semibold">Siguiente paso: <?php echo htmlspecialchars($lessonEditorialState['action_label'] ?? 'Revisar leccion'); ?></div>
                                    <div class="small text-muted mt-1">
                                        <?php if (($lessonEditorialState['label'] ?? '') === 'Sin contexto'): ?>
                                            Falta una teoria que abra la leccion.
                                        <?php elseif (($lessonEditorialState['label'] ?? '') === 'Sin practica'): ?>
                                            La teoria existe, pero falta convertirla en practica.
                                        <?php else: ?>
                                            La estructura base ya esta montada.
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ((int) ($leccion->support_meta->open_total ?? 0) > 0): ?>
                                    <div class="responsive-actions mb-3">
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

                                <details class="panel page-assist-card">
                                    <summary class="page-assist-summary">
                                        <div>
                                            <div class="metric-label">Vista interna</div>
                                            <div class="fw-semibold mt-1">Teoria y actividades de esta leccion</div>
                                            <div class="small text-muted mt-1">Abre esta seccion solo si necesitas leer el contenido puntual antes de seguir con tickets o estructura.</div>
                                        </div>
                                        <span class="soft-badge"><?php echo count($leccion->teorias_detalle ?? []); ?> + <?php echo count($leccion->actividades_detalle ?? []); ?></span>
                                    </summary>
                                    <div class="panel-body pt-0 page-assist-body">
                                        <div class="stack-list">
                                            <article class="stack-item">
                                                <div class="stack-item-title mb-2">Teoria</div>
                                                <?php if (empty($leccion->teorias_detalle)): ?>
                                                    <div class="small text-muted">No tiene teoria.</div>
                                                <?php else: ?>
                                                    <ul class="quality-checklist-list mb-0">
                                                        <?php foreach (array_slice($leccion->teorias_detalle, 0, 4) as $teoria): ?>
                                                            <li><?php echo htmlspecialchars($teoria->titulo); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </article>

                                            <article class="stack-item">
                                                <div class="stack-item-title mb-2">Actividades</div>
                                                <?php if (empty($leccion->actividades_detalle)): ?>
                                                    <div class="small text-muted">No tiene actividades.</div>
                                                <?php else: ?>
                                                    <ul class="quality-checklist-list mb-0">
                                                        <?php foreach (array_slice($leccion->actividades_detalle, 0, 4) as $actividad): ?>
                                                            <li><?php echo htmlspecialchars($actividad->titulo); ?> <span class="text-muted">(<?php echo htmlspecialchars($actividad->tipo_actividad); ?>)</span></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </article>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
