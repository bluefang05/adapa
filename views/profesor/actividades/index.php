<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<?php
$tieneActividades = !empty($actividades);
$activitySummary = [
    'total_puntos' => array_reduce($actividades, fn($carry, $item) => $carry + (int) ($item->puntos_maximos ?? 0), 0),
    'total_tiempo' => array_reduce($actividades, fn($carry, $item) => $carry + (int) ($item->tiempo_limite_minutos ?? 0), 0),
];
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
            <?php if (!empty($puedeCrearActividad)): ?>
                <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create'); ?>" class="btn btn-primary">
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
                    <div class="fw-semibold mt-1">Prueba una actividad como alumno y detecta friccion antes de publicarla.</div>
                    <div class="small text-muted mt-1">La vista de estudiante sirve para revisar copy, tiempos y claridad de respuesta.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create'); ?>" class="btn btn-primary">Nueva actividad</a>
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-primary">Volver a teoria</a>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <?php foreach ($actividades as $actividad): ?>
                <?php
                $activityReady = !empty(trim((string) ($actividad->descripcion ?? ''))) && (int) ($actividad->puntos_maximos ?? 0) > 0;
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
                                <span><i class="bi bi-check2-circle"></i> <?php echo $activityReady ? 'Lista para probar' : 'Revisar ficha'; ?></span>
                            </div>

                            <div class="responsive-actions mt-4">
                                <form method="POST" action="<?php echo url('/profesor/actividad/move-up/' . $actividad->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary" title="Subir actividad">
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/actividad/move-down/' . $actividad->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary" title="Bajar actividad">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="<?php echo url('/profesor/actividad/edit/' . $actividad->id); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/actividad/duplicate/' . $actividad->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-copy"></i> Duplicar
                                    </button>
                                </form>
                                <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/preview'); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Vista estudiante
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/actividad/delete/' . $actividad->id); ?>" onsubmit="return confirm('Esta seguro de eliminar esta actividad?');">
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
