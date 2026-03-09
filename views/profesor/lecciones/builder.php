<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../../models/Teoria.php'; ?>
<?php require_once __DIR__ . '/../../../models/Actividad.php'; ?>

<?php
$theoryCount = count($teorias);
$activityCount = count($actividades);
$resourceCount = count($recursos);
$lessonSupportTone = $lessonSnapshot['tone'] ?? 'info';
$lessonSupportProgress = (int) ($lessonSnapshot['progress'] ?? 0);
$lessonSupportHint = $lessonSnapshot['hint'] ?? '';
$lessonSupportLabel = $lessonSnapshot['label'] ?? 'En progreso';
$builderReturnTo = url('/profesor/lecciones/' . $leccion->id . '/builder');
$theorySummaries = [];
$activitySummaries = [];
$readyTheoryCount = 0;
$readyActivityCount = 0;
$supportActivityCount = 0;

foreach ($teorias as $teoria) {
    $summary = Teoria::resumenDocente($teoria);
    $theorySummaries[$teoria->id] = $summary;
    if (!empty($summary['ready_for_practice'])) {
        $readyTheoryCount++;
    }
}

foreach ($actividades as $actividad) {
    $summary = Actividad::resumenDocente($actividad);
    $activitySummaries[$actividad->id] = $summary;
    if (!empty($summary['config_ready'])) {
        $readyActivityCount++;
    }
    if (!empty($summary['has_support_resource'])) {
        $supportActivityCount++;
    }
}

$builderLanes = [
    [
        'label' => 'Ficha',
        'status' => !empty(trim((string) ($leccion->descripcion ?? ''))) ? 'Con contexto' : 'Falta contexto',
        'hint' => !empty(trim((string) ($leccion->descripcion ?? '')))
            ? 'La leccion ya explica para que existe y que debe lograr.'
            : 'Una descripcion clara baja dudas al construir teoria y practica.',
        'action_label' => !empty(trim((string) ($leccion->descripcion ?? ''))) ? 'Ajustar ficha' : 'Completar ficha',
        'action_url' => url('/profesor/lecciones/edit/' . $leccion->id),
    ],
    [
        'label' => 'Teoria',
        'status' => $theoryCount === 0 ? 'Sin base' : ($readyTheoryCount === $theoryCount ? 'Base util' : 'Piezas por pulir'),
        'hint' => $theoryCount === 0
            ? 'Empieza por una pieza corta con objetivo, ejemplo y cierre.'
            : ($readyTheoryCount === $theoryCount
                ? 'La teoria ya sostiene la practica. Puedes iterar con menos riesgo.'
                : 'Hay piezas creadas, pero alguna todavia necesita ejemplo, cierre o apoyo.'),
        'action_label' => $theoryCount === 0 ? 'Crear teoria' : 'Revisar teoria',
        'action_url' => $theoryCount === 0
            ? url('/profesor/lecciones/' . $leccion->id . '/teoria/create?return_to=' . rawurlencode($builderReturnTo))
            : url('/profesor/lecciones/' . $leccion->id . '/teoria'),
    ],
    [
        'label' => 'Practica',
        'status' => $activityCount === 0 ? 'Sin practica' : ($readyActivityCount === $activityCount ? 'Lista para probar' : 'Config por cerrar'),
        'hint' => $activityCount === 0
            ? 'Convierte la teoria en una actividad medible antes de pensar en publicar.'
            : ($readyActivityCount === $activityCount
                ? 'La practica ya tiene estructura. Conviene probarla como alumno.'
                : 'Hay actividades creadas, pero alguna todavia necesita configuracion interna.'),
        'action_label' => $activityCount === 0 ? 'Crear actividad' : 'Revisar practica',
        'action_url' => $activityCount === 0
            ? url('/profesor/lecciones/' . $leccion->id . '/actividades/create?return_to=' . rawurlencode($builderReturnTo))
            : url('/profesor/lecciones/' . $leccion->id . '/actividades'),
    ],
    [
        'label' => 'Apoyo',
        'status' => $resourceCount === 0 ? 'Sin biblioteca' : ($supportActivityCount > 0 ? 'Ya en uso' : 'Disponible'),
        'hint' => $resourceCount === 0
            ? 'Sube un recurso reutilizable para no depender de texto puro.'
            : ($supportActivityCount > 0
                ? 'Ya hay apoyo vinculado en actividades. Puedes repetir ese patron.'
                : 'La biblioteca ya existe; ahora toca vincularla donde aporte claridad.'),
        'action_label' => $resourceCount === 0 ? 'Abrir biblioteca' : 'Reutilizar recurso',
        'action_url' => url('/profesor/recursos?return_to=' . rawurlencode($builderReturnTo) . '&context=lesson_builder&lesson_id=' . $leccion->id),
    ],
];
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?> - Constructor</li>
        </ol>
    </nav>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-diagram-3"></i> Constructor de leccion</span>
        <h1 class="page-title"><?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">
            Construye teoria, practica y recursos desde una sola vista. El objetivo es cerrar la leccion sin navegar a ciegas.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <a href="<?php echo url('/profesor/lecciones/edit/' . $leccion->id); ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Editar ficha
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/preview'); ?>" class="btn btn-primary">
                <i class="bi bi-eye"></i> Vista completa
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge <?php echo htmlspecialchars($lessonSupportTone); ?>"><i class="bi bi-check2-circle"></i> <?php echo (int) ($lessonPublishSummary['percentage'] ?? 0); ?>% preparacion</span>
            <span class="soft-badge"><i class="bi bi-book"></i> <?php echo $theoryCount; ?> piezas de teoria</span>
            <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo $activityCount; ?> actividades</span>
            <span class="soft-badge"><i class="bi bi-images"></i> <?php echo $resourceCount; ?> recursos recientes</span>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <div class="row g-4">
        <div class="col-xl-8">
            <details class="panel page-assist-card mb-4">
                <summary class="page-assist-summary">
                    <div>
                        <div class="metric-label">Estado editorial</div>
                        <div class="fw-semibold mt-1"><?php echo htmlspecialchars($lessonSupportLabel); ?> - <?php echo $lessonSupportProgress; ?>%</div>
                        <div class="small text-muted mt-1"><?php echo htmlspecialchars($lessonSupportHint); ?></div>
                    </div>
                    <span class="soft-badge badge-<?php echo htmlspecialchars($lessonSupportTone); ?>">Checklist</span>
                </summary>
                <div class="panel-body pt-0 page-assist-body">
                    <div class="alert context-note mb-0">
                        <div class="split-head mb-2">
                            <div class="fw-semibold"><?php echo htmlspecialchars($lessonSupportLabel); ?></div>
                            <span class="soft-badge"><?php echo $lessonSupportProgress; ?>%</span>
                        </div>
                        <div class="readiness-meter mb-2">
                            <span style="width: <?php echo $lessonSupportProgress; ?>%"></span>
                        </div>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($lessonSupportHint); ?></p>
                    </div>

                    <div class="publish-checklist-grid">
                        <?php foreach (($lessonPublishChecklist ?? []) as $item): ?>
                            <article class="publish-check-card <?php echo !empty($item['ok']) ? 'is-ready' : 'is-missing'; ?>">
                                <div class="publish-check-head">
                                    <div class="publish-check-title"><?php echo htmlspecialchars($item['label']); ?></div>
                                    <span class="soft-badge"><?php echo !empty($item['ok']) ? 'OK' : 'Falta'; ?></span>
                                </div>
                                <div class="publish-check-copy"><?php echo htmlspecialchars($item['hint']); ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>

            <section class="surface-card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <h2>Flujo de construccion</h2>
                        <span class="soft-badge"><i class="bi bi-signpost-split"></i> Siguiente paso sugerido</span>
                    </div>
                    <div class="builder-stage-grid">
                        <?php foreach ($quickActions as $action): ?>
                            <a href="<?php echo $action['url']; ?>" class="builder-stage-card<?php echo !empty($action['is_priority']) ? ' is-priority' : ''; ?>">
                                <div class="builder-stage-icon"><i class="<?php echo htmlspecialchars($action['icon']); ?>"></i></div>
                                <div class="builder-stage-body">
                                    <div class="builder-stage-title"><?php echo htmlspecialchars($action['label']); ?></div>
                                    <div class="builder-stage-copy"><?php echo htmlspecialchars($action['copy']); ?></div>
                                </div>
                                <span class="soft-badge"><?php echo !empty($action['is_priority']) ? 'Ahora' : 'Abrir'; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <details class="panel page-assist-card mb-4">
                <summary class="page-assist-summary">
                    <div>
                        <div class="metric-label">Mapa rapido</div>
                        <div class="fw-semibold mt-1">Donde invertir tiempo antes de publicar</div>
                        <div class="small text-muted mt-1">Abre esta seccion si quieres una lectura compacta de ficha, teoria, practica y apoyo.</div>
                    </div>
                    <span class="soft-badge"><?php echo count($builderLanes); ?> focos</span>
                </summary>
                <div class="panel-body pt-0 page-assist-body">
                    <div class="publish-checklist-grid">
                        <?php foreach ($builderLanes as $lane): ?>
                            <article class="publish-check-card">
                                <div class="publish-check-head">
                                    <div class="publish-check-title"><?php echo htmlspecialchars($lane['label']); ?></div>
                                    <span class="soft-badge"><?php echo htmlspecialchars($lane['status']); ?></span>
                                </div>
                                <div class="publish-check-copy"><?php echo htmlspecialchars($lane['hint']); ?></div>
                                <div class="mt-3">
                                    <a href="<?php echo $lane['action_url']; ?>" class="btn btn-sm btn-outline-primary"><?php echo htmlspecialchars($lane['action_label']); ?></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>

            <section class="surface-card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <h2>Teoria de la leccion</h2>
                        <span class="soft-badge"><i class="bi bi-book"></i> <?php echo $theoryCount; ?> piezas</span>
                    </div>
                    <div class="responsive-actions mb-3">
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria/create?return_to=' . rawurlencode($builderReturnTo)); ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Anadir teoria
                        </a>
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-layout-text-window"></i> Gestionar teoria
                        </a>
                    </div>

                    <?php if (empty($teorias)): ?>
                        <div class="builder-empty-state">
                            <div class="builder-empty-title">Todavia no hay teoria</div>
                            <p class="text-muted mb-0">Empieza con una pieza corta: objetivo, explicacion y un ejemplo claro.</p>
                        </div>
                    <?php else: ?>
                        <div class="builder-item-list">
                            <?php foreach ($teorias as $teoria): ?>
                                <?php
                                $summary = $theorySummaries[$teoria->id] ?? Teoria::resumenDocente($teoria);
                                ?>
                                <article class="builder-item-row builder-item-row--theory">
                                    <div class="builder-item-main">
                                        <div class="builder-item-title"><?php echo htmlspecialchars($teoria->titulo); ?></div>
                                        <div class="course-meta builder-inline-meta">
                                            <span><i class="bi bi-clock"></i> <?php echo (int) ($teoria->duracion_minutos ?? 0); ?> min</span>
                                            <span><i class="bi bi-layers"></i> <?php echo (int) $summary['block_count']; ?> bloques</span>
                                            <span><i class="bi bi-image"></i> <?php echo (int) $summary['media_count']; ?> con media</span>
                                        </div>
                                        <div class="small text-muted builder-item-status"><?php echo htmlspecialchars($summary['label']); ?>: <?php echo htmlspecialchars($summary['message']); ?></div>
                                    </div>
                                    <div class="builder-item-actions builder-item-actions-rail">
                                        <a href="<?php echo url('/profesor/teoria/edit/' . $teoria->id . '?return_to=' . rawurlencode($builderReturnTo)); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="<?php echo url('/profesor/teoria/duplicate/' . $teoria->id) . '?continue_to=edit&return_to=' . rawurlencode($builderReturnTo); ?>">
                                            <?php echo csrf_input(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Duplicar y ajustar</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="surface-card">
                <div class="card-body">
                    <div class="section-title">
                        <h2>Practica de la leccion</h2>
                        <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo $activityCount; ?> actividades</span>
                    </div>
                    <div class="responsive-actions mb-3">
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create?return_to=' . rawurlencode($builderReturnTo)); ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nueva actividad
                        </a>
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-grid"></i> Gestionar actividades
                        </a>
                    </div>

                    <?php if (empty($actividades)): ?>
                        <div class="builder-empty-state">
                            <div class="builder-empty-title">Todavia no hay practica</div>
                            <p class="text-muted mb-0">Convierte la teoria en una actividad corta antes de pensar en publicar.</p>
                        </div>
                    <?php else: ?>
                        <div class="builder-item-list">
                            <?php foreach ($actividades as $actividad): ?>
                                <?php $summary = $activitySummaries[$actividad->id] ?? Actividad::resumenDocente($actividad); ?>
                                <article class="builder-item-row builder-item-row--activity">
                                    <div class="builder-item-main">
                                        <div class="builder-item-title"><?php echo htmlspecialchars($actividad->titulo); ?></div>
                                        <div class="course-meta builder-inline-meta">
                                            <span><i class="bi bi-ui-checks-grid"></i> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $actividad->tipo_actividad))); ?></span>
                                            <span><i class="bi bi-award"></i> <?php echo (int) ($actividad->puntos_maximos ?? 0); ?> pts</span>
                                            <span><i class="bi bi-clock-history"></i> <?php echo (int) ($actividad->tiempo_limite_minutos ?? 0); ?> min</span>
                                            <?php if (!empty($summary['has_support_resource'])): ?>
                                                <span><i class="bi bi-paperclip"></i> Con recurso de apoyo</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted builder-item-status"><?php echo htmlspecialchars($summary['label']); ?>: <?php echo htmlspecialchars($summary['message']); ?></div>
                                    </div>
                                    <div class="builder-item-actions builder-item-actions-rail">
                                        <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/configurar?return_to=' . rawurlencode($builderReturnTo)); ?>" class="btn btn-sm btn-outline-secondary">Configurar</a>
                                        <a href="<?php echo url('/profesor/actividad/edit/' . $actividad->id . '?return_to=' . rawurlencode($builderReturnTo)); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/preview'); ?>" class="btn btn-sm btn-outline-secondary">Probar</a>
                                        <form method="POST" action="<?php echo url('/profesor/actividad/duplicate/' . $actividad->id) . '?continue_to=edit&return_to=' . rawurlencode($builderReturnTo); ?>">
                                            <?php echo csrf_input(); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Duplicar y ajustar</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="surface-card builder-sidebar-card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <h2>Biblioteca reciente</h2>
                        <span class="soft-badge"><i class="bi bi-collection-play"></i> Biblioteca</span>
                    </div>
                    <p class="text-muted">Abre la biblioteca desde contexto o reutiliza uno de los recursos recientes para acelerar la leccion.</p>
                    <div class="responsive-actions mb-3">
                        <a href="<?php echo url('/profesor/recursos?return_to=' . urlencode(url('/profesor/lecciones/' . $leccion->id . '/builder')) . '&context=lesson_builder&lesson_id=' . $leccion->id); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-folder2-open"></i> Abrir biblioteca
                        </a>
                    </div>

                    <?php if (empty($recursos)): ?>
                        <div class="builder-empty-state">
                            <div class="builder-empty-title">Todavia no hay recursos</div>
                            <p class="text-muted mb-0">Sube imagenes, audio o pega un video de YouTube para usarlos despues en teoria y actividades.</p>
                        </div>
                    <?php else: ?>
                        <div class="builder-resource-list">
                            <?php foreach ($recursos as $recurso): ?>
                                <article class="builder-resource-card">
                                    <div class="builder-resource-head">
                                        <div class="builder-item-title"><?php echo htmlspecialchars($recurso->titulo); ?></div>
                                        <span class="soft-badge"><?php echo htmlspecialchars(ucfirst($recurso->tipo_media)); ?></span>
                                    </div>
                                    <div class="builder-item-copy">
                                        <?php echo htmlspecialchars($recurso->descripcion ?: 'Disponible para teoria y actividades.'); ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="surface-card builder-sidebar-card">
                <div class="card-body">
                    <div class="section-title">
                        <h2>Publicacion</h2>
                        <span class="soft-badge"><i class="bi bi-send-check"></i> Cierre</span>
                    </div>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($lessonPublishHint ?? ''); ?></p>
                    <div class="responsive-actions">
                        <a href="<?php echo url('/profesor/lecciones/edit/' . $leccion->id); ?>" class="btn btn-primary">
                            <i class="bi bi-check2-square"></i> Revisar ficha final
                        </a>
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/preview'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Probar leccion completa
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php
$issueReportTitle = 'Reportar un problema del constructor de leccion';
$issueReportAction = url('/reportar-fallo');
$issueReportContextType = 'leccion';
$issueReportContextId = 'profesor_builder_' . (int) $leccion->id;
$issueReportReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/lecciones/' . $leccion->id . '/builder');
$issueReportCourseId = (int) $curso->id;
$issueReportLessonId = (int) $leccion->id;
$issueReportDescriptionPlaceholder = 'Describe el problema del constructor, del flujo o de algun recurso dentro de esta leccion.';
require __DIR__ . '/../../partials/issue_report_panel.php';
?>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
