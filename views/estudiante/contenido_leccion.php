<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante'); ?>">Mis Cursos</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-journal-richtext"></i> Leccion activa</span>
        <h1 class="page-title"><?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle"><?php echo nl2br(htmlspecialchars($leccion->descripcion)); ?></p>
        <?php if (isset($resumenProgreso)): ?>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Progreso</div>
                    <div class="metric-value"><?php echo (int) $resumenProgreso->porcentaje; ?>%</div>
                    <div class="metric-note">Avance total de esta leccion.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Teoria</div>
                    <div class="metric-value"><?php echo (int) $resumenProgreso->teorias_completadas; ?>/<?php echo (int) $resumenProgreso->total_teorias; ?></div>
                    <div class="metric-note">Bloques conceptuales ya leidos.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Actividades</div>
                    <div class="metric-value"><?php echo (int) $resumenProgreso->actividades_completadas; ?>/<?php echo (int) $resumenProgreso->total_actividades; ?></div>
                    <div class="metric-note">Practicas respondidas hasta ahora.</div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <?php if (isset($siguienteItem)): ?>
        <div class="panel mb-4">
            <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="metric-label">Siguiente paso sugerido</div>
                    <div class="fw-semibold mt-1"><?php echo htmlspecialchars($siguienteItem['mensaje']); ?>: <?php echo htmlspecialchars($siguienteItem['titulo']); ?></div>
                </div>
                <div>
                    <?php if ($siguienteItem['tipo'] === 'teoria'): ?>
                        <a href="#teoria-<?php echo $siguienteItem['id']; ?>" class="btn btn-primary">Ir a teoria</a>
                    <?php elseif ($siguienteItem['tipo'] === 'actividad'): ?>
                        <a href="<?php echo url('/estudiante/actividades/' . $siguienteItem['id']); ?>" class="btn btn-primary">Realizar actividad</a>
                    <?php elseif ($siguienteItem['tipo'] === 'leccion'): ?>
                        <a href="<?php echo url('/estudiante/lecciones/' . $siguienteItem['id'] . '/contenido'); ?>" class="btn btn-success">Ir a la siguiente leccion</a>
                    <?php else: ?>
                        <a href="<?php echo url('/estudiante/cursos/' . $siguienteItem['id'] . '/lecciones'); ?>" class="btn btn-success">Ver curso completo</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section class="mb-4">
        <div class="section-title">
            <h2>Teoria</h2>
            <span class="soft-badge"><i class="bi bi-book-half"></i> Estudio guiado</span>
        </div>
        <?php if (empty($teorias)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-book"></i></span>
                    <div class="empty-state-copy">No hay contenido teorico para esta leccion.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teorias as $teoria): ?>
                <div class="content-block" id="teoria-<?php echo $teoria->id; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h5 class="mb-0"><?php echo htmlspecialchars($teoria->titulo); ?></h5>
                            <?php if (!empty($teoria->leido)): ?>
                                <span class="soft-badge"><i class="bi bi-check-circle-fill"></i> Leido</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-text">
                            <?php echo $teoria->contenido; ?>
                        </div>
                        <?php if (empty($teoria->leido)): ?>
                            <form action="<?php echo url('/estudiante/teoria/' . $teoria->id . '/leer'); ?>" method="POST" class="mt-3">
                                <?php echo csrf_input(); ?>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-check2-circle"></i> Marcar como leido
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section>
        <div class="section-title">
            <h2>Actividades</h2>
            <span class="soft-badge"><i class="bi bi-lightning"></i> Practica</span>
        </div>
        <?php if (empty($actividades)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-lightning"></i></span>
                    <div class="empty-state-copy">No hay actividades para esta leccion.</div>
                </div>
            </div>
        <?php else: ?>
            <ul class="list-group lesson-stack">
                <?php foreach ($actividades as $actividad): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($actividad->titulo); ?></div>
                            <?php if (!empty($actividad->completada)): ?>
                                <div class="small text-muted mt-1">
                                    Completada<?php if (isset($actividad->calificacion)): ?> - <?php echo $actividad->calificacion; ?> pts<?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="small text-muted mt-1">Pendiente</div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo url('/estudiante/actividades/' . $actividad->id); ?>" class="btn btn-primary">
                            <?php echo !empty($actividad->completada) ? 'Ver resultados' : 'Realizar actividad'; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <div class="mt-4 mb-2">
        <a href="<?php echo url('/estudiante/cursos/' . $leccion->curso_id . '/lecciones'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a lecciones
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
