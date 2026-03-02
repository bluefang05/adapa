<?php
function getEstadoColor($estado) {
    switch ($estado) {
        case 'borrador': return 'warning';
        case 'publicada': return 'success';
        case 'archivada': return 'secondary';
        default: return 'secondary';
    }
}

function getEstadoTexto($estado) {
    switch ($estado) {
        case 'borrador': return 'Borrador';
        case 'publicada': return 'Publicada';
        case 'archivada': return 'Archivada';
        default: return $estado;
    }
}
?>

<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
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
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Lecciones</div>
                <div class="metric-value"><?php echo count($lecciones); ?></div>
                <div class="metric-note">Bloques creados dentro del curso.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Teoria</div>
                <div class="metric-value"><?php echo array_reduce($lecciones, fn($carry, $item) => $carry + (int) $item->total_teorias, 0); ?></div>
                <div class="metric-note">Piezas teoricas agregadas.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Actividades</div>
                <div class="metric-value"><?php echo array_reduce($lecciones, fn($carry, $item) => $carry + (int) $item->total_actividades, 0); ?></div>
                <div class="metric-note">Practicas listas para editar.</div>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-lightbulb"></i>
            Plan gratuito: este curso admite hasta 3 lecciones. <?php echo !empty($mensajeLimiteLeccion) ? htmlspecialchars($mensajeLimiteLeccion) : 'Aun tienes espacio para seguir construyendo.'; ?>
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
                    <div class="col-xl-6">
                        <article class="surface-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                    <div>
                                        <div class="small text-muted mb-1">Leccion <?php echo (int) $leccion->orden; ?></div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($leccion->titulo); ?></h3>
                                        <?php if ($leccion->es_obligatoria): ?>
                                            <span class="soft-badge">Obligatoria</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-<?php echo getEstadoColor($leccion->estado); ?>"><?php echo htmlspecialchars(getEstadoTexto($leccion->estado)); ?></span>
                                </div>

                                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($leccion->descripcion)); ?></p>

                                <div class="course-meta">
                                    <span><i class="bi bi-book"></i> <?php echo (int) $leccion->total_teorias; ?> teorias</span>
                                    <span><i class="bi bi-pencil-square"></i> <?php echo (int) $leccion->total_actividades; ?> actividades</span>
                                    <?php if ($leccion->duracion_minutos): ?>
                                        <span><i class="bi bi-clock"></i> <?php echo (int) $leccion->duracion_minutos; ?> min</span>
                                    <?php endif; ?>
                                </div>

                                <div class="responsive-actions mt-4">
                                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-book"></i> Gestionar teoria
                                    </a>
                                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Gestionar actividades
                                    </a>
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

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
