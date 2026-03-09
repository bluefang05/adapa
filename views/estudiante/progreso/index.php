<?php
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../../models/Curso.php';
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-graph-up-arrow"></i> Seguimiento</span>
        <h1 class="page-title">Progreso por curso, no solo por sensacion.</h1>
        <p class="page-subtitle">
            Revisa tu avance con una lectura clara de teoria, actividades y ritmo general en cada curso inscrito.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/estudiante'); ?>" class="btn btn-primary">
                <i class="bi bi-journal-text"></i> Volver al dashboard
            </a>
            <a href="<?php echo url('/estudiante/calificaciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-award"></i> Ver calificaciones
            </a>
        </div>
        <?php
        $avg = 0;
        if (!empty($resumenCursos)) {
            $avg = (int) round(array_reduce($resumenCursos, fn($carry, $curso) => $carry + (int) $curso->porcentaje, 0) / count($resumenCursos));
        }
        ?>
        <?php if (!empty($progressScopeHint)): ?>
            <div class="alert context-note mt-3 mb-0"><?php echo htmlspecialchars($progressScopeHint); ?></div>
        <?php endif; ?>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-journal-bookmark"></i> <?php echo count($resumenCursos); ?> cursos medidos</span>
            <span class="soft-badge"><i class="bi bi-graph-up-arrow"></i> <?php echo $avg; ?>% promedio general</span>
            <span class="soft-badge"><i class="bi bi-check2-circle"></i> <?php echo array_reduce($resumenCursos, fn($carry, $curso) => $carry + (int) $curso->completados, 0); ?> items completados</span>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Vista por curso</h2>
            <span class="soft-badge"><i class="bi bi-bar-chart"></i> Lectura rapida</span>
        </div>

        <?php if (empty($resumenCursos)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-graph-down"></i></span>
                    <div class="empty-state-copy">Todavia no tienes cursos inscritos para mostrar progreso.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($resumenCursos as $curso): ?>
                    <?php
                    $idiomaObjetivo = Curso::obtenerIdiomaObjetivo($curso);
                    $idiomaBase = Curso::obtenerIdiomaBase($curso);
                    ?>
                    <div class="col-xl-6">
                        <div class="course-card">
                            <div class="card-body">
                                <div class="split-head mb-3">
                                    <div>
                                        <h3 class="h4 mb-1"><?php echo htmlspecialchars($curso->titulo); ?></h3>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars(app_language_label($idiomaObjetivo, strtoupper($idiomaObjetivo))); ?> |
                                            <?php echo htmlspecialchars(Curso::formatearRangoNivel($curso)); ?> |
                                            Desde <?php echo htmlspecialchars(app_language_label($idiomaBase, ucfirst($idiomaBase))); ?>
                                        </div>
                                    </div>
                                    <span class="soft-badge"><?php echo (int) $curso->porcentaje; ?>%</span>
                                </div>

                                <div class="badge-row mb-3">
                                    <span class="soft-badge <?php echo Curso::esRutaCompleta($curso) ? 'badge-accent' : ''; ?>">
                                        <i class="bi bi-signpost-split"></i> <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($curso)); ?>
                                    </span>
                                    <?php if (($curso->estado_progreso ?? '') === 'completado'): ?>
                                        <span class="soft-badge">
                                            <i class="bi bi-check-circle-fill"></i> Completado
                                        </span>
                                    <?php elseif (($curso->estado_progreso ?? '') === 'en_progreso'): ?>
                                        <span class="soft-badge">
                                            <i class="bi bi-arrow-repeat"></i> En progreso
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="course-progress">
                                    <div class="progress progress-slim" role="progressbar" aria-valuenow="<?php echo (int) $curso->porcentaje; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: <?php echo (int) $curso->porcentaje; ?>%"></div>
                                    </div>
                                </div>

                                <div class="compact-meta-row mt-3">
                                    <span class="soft-badge"><i class="bi bi-collection"></i> <?php echo (int) $curso->total_lecciones; ?> lecciones</span>
                                    <span class="soft-badge"><i class="bi bi-book"></i> <?php echo (int) $curso->teorias_leidas; ?>/<?php echo (int) $curso->total_teorias; ?> teoria</span>
                                    <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo (int) $curso->actividades_respondidas; ?>/<?php echo (int) $curso->total_actividades; ?> practica</span>
                                </div>

                                <div class="responsive-actions mt-4">
                                    <a href="<?php echo url('/estudiante/cursos/' . $curso->id . '/continuar'); ?>" class="btn btn-success">Continuar curso</a>
                                    <a href="<?php echo url('/estudiante/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-primary">Ver lecciones</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
