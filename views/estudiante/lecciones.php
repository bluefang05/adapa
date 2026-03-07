<?php 
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../models/Curso.php';
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
        <p class="page-subtitle">Abre la siguiente leccion y continua.</p>
        <?php if (!empty($curso->descripcion)): ?>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($curso->descripcion); ?></p>
        <?php endif; ?>
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

    <?php if (!empty($courseResources)): ?>
        <section class="panel mb-4">
            <div class="panel-body">
                <div class="section-title mb-3">
                    <h2>Recursos utiles para este curso</h2>
                    <a href="<?php echo url('/estudiante/recursos?idioma=' . urlencode(Curso::obtenerIdiomaObjetivo($curso))); ?>" class="btn btn-outline-secondary btn-sm">
                        Ver caja completa
                    </a>
                </div>
                <div class="row g-3">
                    <?php foreach ($courseResources as $resource): ?>
                        <div class="col-lg-3 col-md-6">
                            <article class="surface-card useful-resource-card h-100">
                                <div class="card-body d-flex flex-column gap-2">
                                    <div class="small text-muted"><?php echo htmlspecialchars($resource['badge'] ?? 'Recurso'); ?></div>
                                    <h3 class="h6 mb-0"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <div class="mt-auto">
                                        <a href="<?php echo htmlspecialchars($resource['url']); ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">Abrir</a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section>
        <div class="section-title">
            <h2>Lecciones</h2>
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
                                    <div class="small text-muted"><?php echo (int) ($leccion->porcentaje_completado ?? 0); ?>%</div>
                                </div>

                                <div class="course-progress">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo (int) ($leccion->porcentaje_completado ?? 0); ?>%" aria-valuenow="<?php echo (int) ($leccion->porcentaje_completado ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="course-meta">
                                    <?php if (isset($leccion->completados, $leccion->total_items) && (int) $leccion->total_items > 0): ?>
                                        <span><?php echo (int) $leccion->completados; ?>/<?php echo (int) $leccion->total_items; ?> items</span>
                                    <?php endif; ?>
                                    <?php if (!empty($leccion->estado)): ?>
                                        <span class="soft-badge">
                                            <?php echo $leccion->estado === 'completada' ? 'Completada' : ($leccion->estado === 'en_progreso' ? 'En progreso' : 'Pendiente'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($leccion->descripcion)): ?>
                                    <p class="text-muted mt-3 mb-0"><?php echo htmlspecialchars($leccion->descripcion); ?></p>
                                <?php endif; ?>

                                <div class="responsive-actions mt-4">
                                    <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-primary">
                                        <?php echo !empty($leccion->completados) ? 'Continuar leccion' : 'Abrir leccion'; ?>
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
