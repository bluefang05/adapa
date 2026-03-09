<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-collection"></i> Estructura del curso</span>
        <h1 class="page-title"><?php echo htmlspecialchars($curso->titulo); ?></h1>
        <p class="page-subtitle"><?php echo nl2br(htmlspecialchars($curso->descripcion)); ?></p>
        <div class="hero-actions">
            <?php if (!empty($puedeCrearLeccion)): ?>
                <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones/create'); ?>" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Nueva leccion
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-lock"></i> Limite de lecciones alcanzado
                </button>
            <?php endif; ?>
            <a href="<?php echo url('/profesor/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a cursos
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-collection"></i> <?php echo count($lecciones); ?> lecciones</span>
            <span class="soft-badge"><i class="bi bi-book"></i> <?php echo array_reduce($lecciones, fn($carry, $item) => $carry + (int) $item->total_teorias, 0); ?> piezas de teoria</span>
            <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo array_reduce($lecciones, fn($carry, $item) => $carry + (int) $item->total_actividades, 0); ?> actividades</span>
            <?php if (empty($puedeCrearLeccion)): ?>
                <span class="soft-badge warning"><i class="bi bi-lock"></i> Limite de lecciones alcanzado</span>
            <?php endif; ?>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert context-note mb-4">
            <strong>Plan gratuito:</strong> este curso admite hasta 3 lecciones. <?php echo !empty($mensajeLimiteLeccion) ? htmlspecialchars($mensajeLimiteLeccion) : 'Aun tienes espacio para seguir construyendo.'; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($lecciones)): ?>
        <div class="panel">
            <div class="panel-body">
                <h2 class="h4">Todavia no hay lecciones</h2>
                <p class="mb-3">Crea la primera leccion para empezar a construir el recorrido del curso.</p>
                <?php if (!empty($puedeCrearLeccion)): ?>
                    <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones/create'); ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Crear primera leccion
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <section>
            <div class="section-title">
                <h2>Lecciones del curso</h2>
                <span class="soft-badge"><i class="bi bi-grid"></i> Vista docente</span>
            </div>

            <div class="row g-4">
                <?php foreach ($lecciones as $leccion): ?>
                    <?php
                    $readiness = app_lesson_readiness_summary($leccion);
                    $editorialState = app_lesson_editorial_snapshot($leccion);
                    ?>
                    <div class="col-xl-6">
                        <article class="surface-card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <div>
                                        <div class="small text-muted mb-1">Leccion <?php echo (int) $leccion->orden; ?></div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($leccion->titulo); ?></h3>
                                        <div class="badge-row">
                                            <?php if ($leccion->es_obligatoria): ?>
                                                <span class="soft-badge">Obligatoria</span>
                                            <?php endif; ?>
                                            <span class="soft-badge badge-<?php echo htmlspecialchars($editorialState['tone']); ?>"><?php echo htmlspecialchars($editorialState['label']); ?></span>
                                        </div>
                                        <div class="small text-muted mt-2">Tecnico: <?php echo htmlspecialchars($leccion->estado ?? 'borrador'); ?></div>
                                    </div>
                                </div>

                                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($leccion->descripcion)); ?></p>

                                <div class="course-meta">
                                    <span><i class="bi bi-book"></i> <?php echo (int) $leccion->total_teorias; ?> teorias</span>
                                    <span><i class="bi bi-pencil-square"></i> <?php echo (int) $leccion->total_actividades; ?> actividades</span>
                                    <?php if ($leccion->duracion_minutos): ?>
                                        <span><i class="bi bi-clock"></i> <?php echo (int) $leccion->duracion_minutos; ?> min</span>
                                    <?php endif; ?>
                                </div>

                                <section class="production-hint-card mt-4 tone-<?php echo htmlspecialchars($readiness['tone']); ?>">
                                    <div class="split-head mb-2">
                                        <div class="production-hint-title"><?php echo htmlspecialchars($readiness['label']); ?></div>
                                        <span class="soft-badge"><?php echo (int) $readiness['progress']; ?>%</span>
                                    </div>
                                    <div class="readiness-meter mb-2">
                                        <span style="width: <?php echo (int) $readiness['progress']; ?>%"></span>
                                    </div>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($readiness['message']); ?></p>
                                    <a href="<?php echo $readiness['action_url']; ?>" class="btn btn-sm btn-primary">
                                        <?php echo htmlspecialchars($readiness['action_label']); ?>
                                    </a>
                                </section>

                                <div class="responsive-actions mt-4">
                                    <form method="POST" action="<?php echo url('/profesor/lecciones/move-up/' . $leccion->id); ?>">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-outline-secondary" title="Subir leccion">
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo url('/profesor/lecciones/move-down/' . $leccion->id); ?>">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-outline-secondary" title="Bajar leccion">
                                            <i class="bi bi-arrow-down"></i>
                                        </button>
                                    </form>
                                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/builder'); ?>" class="btn btn-primary">
                                        <i class="bi bi-diagram-3"></i> Constructor
                                    </a>
                                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-book"></i> Gestionar teoria
                                    </a>
                                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Gestionar actividades
                                    </a>
                                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/preview'); ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Vista completa
                                    </a>
                                    <form method="POST" action="<?php echo url('/profesor/lecciones/duplicate/' . $leccion->id); ?>">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="bi bi-copy"></i> Duplicar
                                        </button>
                                    </form>
                                    <a href="<?php echo url('/profesor/lecciones/edit/' . $leccion->id); ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <form method="POST" action="<?php echo url('/profesor/lecciones/delete/' . $leccion->id); ?>" onsubmit="return confirm('Estas seguro de eliminar esta leccion? Se eliminaran tambien las teorias y actividades asociadas.');">
                                        <?php echo csrf_input(); ?>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php
$issueReportTitle = 'Reportar un problema en la construccion de esta ruta';
$issueReportAction = url('/reportar-fallo');
$issueReportContextType = 'leccion';
$issueReportContextId = 'profesor_lecciones_' . (int) $curso->id;
$issueReportReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/cursos/' . $curso->id . '/lecciones');
$issueReportCourseId = (int) $curso->id;
$issueReportDescriptionPlaceholder = 'Describe el fallo del editor, del orden, de la vista o del flujo de lecciones.';
require __DIR__ . '/../../partials/issue_report_panel.php';
?>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
