<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../../models/Actividad.php'; ?>

<?php
$tieneActividades = !empty($actividades);
$activitySummary = [
    'total_puntos' => array_reduce($actividades, fn($carry, $item) => $carry + (int) ($item->puntos_maximos ?? 0), 0),
    'total_tiempo' => array_reduce($actividades, fn($carry, $item) => $carry + (int) ($item->tiempo_limite_minutos ?? 0), 0),
];
$currentReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/lecciones/' . $leccion->id . '/actividades');
$activitySummaries = [];
$actividadPendiente = null;
$supportCount = 0;

foreach ($actividades as $actividad) {
    $summary = Actividad::resumenDocente($actividad);
    $activitySummaries[$actividad->id] = $summary;
    if (!empty($summary['has_support_resource'])) {
        $supportCount++;
    }
    if ($actividadPendiente === null && empty($summary['config_ready'])) {
        $actividadPendiente = [
            'actividad' => $actividad,
            'summary' => $summary,
        ];
    }
}
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?> - Actividades</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Practica de la leccion</span>
        <h1 class="page-title">Actividades de <?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">
            Revisa la practica creada, ajusta el orden y entra rapido a edicion o vista de estudiante.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/builder'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-diagram-3"></i> Constructor
            </a>
            <?php if (!empty($puedeCrearActividad)): ?>
                <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva actividad
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-lock"></i> Limite de actividades alcanzado
                </button>
            <?php endif; ?>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/preview'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Vista completa
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-book"></i> Revisar teoria
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Actividades</div>
                <div class="metric-value"><?php echo count($actividades); ?></div>
                <div class="metric-note">Practicas dentro de esta leccion.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Puntos totales</div>
                <div class="metric-value"><?php echo $activitySummary['total_puntos']; ?></div>
                <div class="metric-note">Suma de puntuacion maxima disponible.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Tiempo total</div>
                <div class="metric-value"><?php echo $activitySummary['total_tiempo']; ?></div>
                <div class="metric-note">Minutos estimados si el alumno completa toda la practica.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Con apoyo</div>
                <div class="metric-value"><?php echo $supportCount; ?></div>
                <div class="metric-note">Actividades con recurso vinculado.</div>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-lightbulb"></i>
            Plan gratuito: cada leccion admite hasta 3 actividades. <?php echo !empty($mensajeLimiteActividad) ? htmlspecialchars($mensajeLimiteActividad) : 'Aun tienes espacio para una practica mas.'; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($actividades)): ?>
        <div class="panel">
            <div class="panel-body">
                Todavia no hay actividades para esta leccion.
                <?php if (!empty($puedeCrearActividad)): ?>
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create'); ?>" class="btn btn-primary ms-2">Crear la primera</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <section class="panel mb-4">
            <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="metric-label">Siguiente paso recomendado</div>
                    <div class="fw-semibold mt-1">
                        <?php if ($actividadPendiente): ?>
                            La actividad "<?php echo htmlspecialchars($actividadPendiente['actividad']->titulo); ?>" necesita una configuracion interna mas clara.
                        <?php else: ?>
                            Prueba una actividad como alumno y detecta friccion antes de publicarla.
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1">
                        <?php if ($actividadPendiente): ?>
                            <?php echo htmlspecialchars($actividadPendiente['summary']['message']); ?>
                        <?php else: ?>
                            La vista de estudiante sirve para revisar copy, tiempos y claridad de respuesta.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-primary">Nueva actividad</a>
                    <?php if ($actividadPendiente): ?>
                        <a href="<?php echo url('/profesor/actividad/' . $actividadPendiente['actividad']->id . '/configurar?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-outline-primary">Configurar actividad detectada</a>
                    <?php endif; ?>
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-primary">Volver a teoria</a>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <?php foreach ($actividades as $actividad): ?>
                <?php
                $summary = $activitySummaries[$actividad->id] ?? Actividad::resumenDocente($actividad);
                $supportResource = $summary['support_resource'] ?? null;
                ?>
                <div class="col-xl-6">
                    <article class="surface-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                <div>
                                    <h3 class="h4 mb-1"><?php echo htmlspecialchars($actividad->titulo); ?></h3>
                                    <div class="small text-muted text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $actividad->tipo_actividad)); ?></div>
                                </div>
                                <span class="soft-badge">Orden <?php echo (int) $actividad->orden; ?></span>
                            </div>

                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($actividad->descripcion)); ?></p>

                            <div class="course-meta">
                                <span><i class="bi bi-clock"></i> <?php echo (int) $actividad->tiempo_limite_minutos; ?> min</span>
                                <span><i class="bi bi-award"></i> <?php echo (int) $actividad->puntos_maximos; ?> puntos</span>
                                <span><i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($summary['label']); ?></span>
                                <?php if (!empty($summary['question_count'])): ?>
                                    <span><i class="bi bi-list-check"></i> <?php echo (int) $summary['question_count']; ?> prompts</span>
                                <?php elseif (!empty($summary['item_count'])): ?>
                                    <span><i class="bi bi-ui-checks"></i> <?php echo (int) $summary['item_count']; ?> items</span>
                                <?php endif; ?>
                                <?php if ($supportResource): ?>
                                    <span><i class="bi bi-paperclip"></i> Con apoyo</span>
                                <?php endif; ?>
                            </div>

                            <div class="small text-muted mt-2"><?php echo htmlspecialchars($summary['message']); ?></div>

                            <?php if ($supportResource): ?>
                                <div class="small text-muted mt-2">
                                    Recurso vinculado: <?php echo htmlspecialchars($supportResource['title'] ?? 'Recurso de apoyo'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="responsive-actions mt-4">
                                <form method="POST" action="<?php echo url('/profesor/actividad/move-up/' . $actividad->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentReturnTo); ?>">
                                    <button type="submit" class="btn btn-outline-secondary" title="Subir actividad">
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/actividad/move-down/' . $actividad->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentReturnTo); ?>">
                                    <button type="submit" class="btn btn-outline-secondary" title="Bajar actividad">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/configurar?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-sliders"></i> Configurar
                                </a>
                                <a href="<?php echo url('/profesor/actividad/edit/' . $actividad->id . '?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/actividad/duplicate/' . $actividad->id) . '?return_to=' . rawurlencode($currentReturnTo); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-copy"></i> Duplicar
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/actividad/duplicate/' . $actividad->id) . '?continue_to=edit&return_to=' . rawurlencode($currentReturnTo); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-copy"></i> Duplicar y ajustar
                                    </button>
                                </form>
                                <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/preview'); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Vista estudiante
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/actividad/delete/' . $actividad->id); ?>" onsubmit="return confirm('Esta seguro de eliminar esta actividad?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentReturnTo); ?>">
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
    <?php endif; ?>
</div>

<?php
$issueReportTitle = 'Reportar un problema en el modulo de actividades';
$issueReportAction = url('/reportar-fallo');
$issueReportContextType = 'actividad';
$issueReportContextId = 'profesor_actividades_' . (int) $leccion->id;
$issueReportReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/lecciones/' . $leccion->id . '/actividades');
$issueReportCourseId = (int) $leccion->curso_id;
$issueReportLessonId = (int) $leccion->id;
$issueReportDescriptionPlaceholder = 'Describe el fallo del configurador, preview o guardado de actividades.';
require __DIR__ . '/../../partials/issue_report_panel.php';
?>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
