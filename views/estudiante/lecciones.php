<?php 
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container">
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/estudiante'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($curso->titulo); ?> - Lecciones</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-journal-bookmark"></i> Recorrido del curso</span>
        <h1 class="page-title">Lecciones de <?php echo htmlspecialchars($curso->titulo); ?></h1>
        <p class="page-subtitle">
            Entra al siguiente bloque, revisa tu avance y detecta rapido que partes del curso ya estan cerradas o siguen en progreso.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/estudiante'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al dashboard
            </a>
        </div>

        <?php if (isset($resumenCurso) && $resumenCurso): ?>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Progreso del curso</div>
                    <div class="metric-value"><?php echo (int) $resumenCurso->porcentaje; ?>%</div>
                    <div class="metric-note"><?php echo (int) $resumenCurso->completados; ?>/<?php echo (int) $resumenCurso->total_items; ?> items completados.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Lecciones</div>
                    <div class="metric-value"><?php echo count($lecciones); ?></div>
                    <div class="metric-note">Bloques disponibles dentro del curso.</div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <div class="section-title">
            <h2>Mapa de lecciones</h2>
            <span class="soft-badge"><i class="bi bi-compass"></i> Continua donde lo dejaste</span>
        </div>

        <?php if (empty($lecciones)): ?>
            <div class="panel">
                <div class="panel-body">No hay lecciones en este curso todavia.</div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($lecciones as $leccion): ?>
                    <div class="col-xl-6">
                        <article class="surface-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                    <div>
                                        <div class="small text-muted mb-1">Leccion</div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($leccion->titulo); ?></h3>
                                    </div>
                                    <?php if (isset($leccion->completada) && $leccion->completada): ?>
                                        <span class="soft-badge"><i class="bi bi-check-circle"></i> Completada</span>
                                    <?php elseif (($leccion->porcentaje_completado ?? 0) > 0): ?>
                                        <span class="soft-badge"><i class="bi bi-arrow-repeat"></i> En progreso</span>
                                    <?php else: ?>
                                        <span class="soft-badge"><i class="bi bi-circle"></i> Pendiente</span>
                                    <?php endif; ?>
                                </div>

                                <div class="course-progress">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo (int) ($leccion->porcentaje_completado ?? 0); ?>%" aria-valuenow="<?php echo (int) ($leccion->porcentaje_completado ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="course-meta">
                                    <span><?php echo (int) ($leccion->porcentaje_completado ?? 0); ?>% completado</span>
                                    <?php if (isset($leccion->completados, $leccion->total_items) && (int) $leccion->total_items > 0): ?>
                                        <span><?php echo (int) $leccion->completados; ?>/<?php echo (int) $leccion->total_items; ?> items</span>
                                    <?php endif; ?>
                                </div>

                                <div class="responsive-actions mt-4">
                                    <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-primary">
                                        Ver contenido
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
