<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-stars"></i> Panel de aprendizaje</span>
        <h1 class="page-title">Tu progreso ya tiene una vista util.</h1>
        <p class="page-subtitle">
            Continua cursos activos, revisa avance real y detecta rapidamente que contenido te falta por cerrar.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/estudiante/progreso'); ?>" class="btn btn-primary">
                <i class="bi bi-graph-up-arrow"></i> Ver progreso
            </a>
            <a href="<?php echo url('/estudiante/calificaciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-award"></i> Ver calificaciones
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Cursos inscritos</div>
                <div class="metric-value"><?php echo count($cursosInscritos); ?></div>
                <div class="metric-note">Tu espacio activo de estudio.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Disponibles</div>
                <div class="metric-value"><?php echo count($cursosDisponibles); ?></div>
                <div class="metric-note">Cursos que puedes iniciar ahora.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Promedio de avance</div>
                <div class="metric-value">
                    <?php
                    $avgProgress = 0;
                    if (!empty($cursosInscritos)) {
                        $sum = array_reduce($cursosInscritos, function ($carry, $curso) {
                            return $carry + (int) ($curso->porcentaje ?? 0);
                        }, 0);
                        $avgProgress = (int) round($sum / count($cursosInscritos));
                    }
                    echo $avgProgress;
                    ?>%
                </div>
                <div class="metric-note">Promedio entre todos tus cursos.</div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="section-title">
            <h2>Mis cursos</h2>
            <span class="soft-badge"><i class="bi bi-lightning-charge"></i> En marcha</span>
        </div>
        <div class="row g-4">
            <?php if (!empty($cursosInscritos)): ?>
                <?php foreach ($cursosInscritos as $curso): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="course-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($curso->titulo); ?></h5>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars(strtoupper($curso->idioma ?? '')); ?> Â· <?php echo htmlspecialchars($curso->nivel_cefr ?? ''); ?>
                                        </div>
                                    </div>
                                    <span class="soft-badge"><?php echo (int) ($curso->porcentaje ?? 0); ?>%</span>
                                </div>
                                <p class="card-text mb-0"><?php echo htmlspecialchars(substr($curso->descripcion, 0, 140)); ?>...</p>
                                <div class="course-meta">
                                    <span><i class="bi bi-journal-text"></i> <?php echo (int) ($curso->total_lecciones ?? 0); ?> lecciones</span>
                                    <span><i class="bi bi-check2-circle"></i> <?php echo (int) ($curso->completados ?? 0); ?>/<?php echo (int) ($curso->total_items ?? 0); ?> items</span>
                                </div>
                                <div class="course-progress">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo (int) ($curso->porcentaje ?? 0); ?>%" aria-valuenow="<?php echo (int) ($curso->porcentaje ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?php echo url('/estudiante/cursos/' . $curso->id . '/continuar'); ?>" class="btn btn-success">Continuar</a>
                                    <a href="<?php echo url('/estudiante/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-primary">Explorar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="panel empty-state-card">
                        <div class="panel-body">
                            <span class="empty-state-icon"><i class="bi bi-compass"></i></span>
                            <div class="empty-state-copy">Aun no te has inscrito en ningun curso. Elige uno disponible para empezar.</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Cursos disponibles</h2>
            <span class="soft-badge"><i class="bi bi-compass"></i> Nuevas rutas</span>
        </div>
        <div class="row g-4">
            <?php if (!empty($cursosDisponibles)): ?>
                <?php foreach ($cursosDisponibles as $curso): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="surface-card">
                            <div class="card-body">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($curso->titulo); ?></h5>
                                <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(substr($curso->descripcion, 0, 130)); ?>...</p>
                                <div class="course-meta">
                                    <span><i class="bi bi-translate"></i> <?php echo htmlspecialchars(strtoupper($curso->idioma ?? '')); ?></span>
                                    <span><i class="bi bi-ladder"></i> <?php echo htmlspecialchars($curso->nivel_cefr ?? ''); ?></span>
                                </div>
                                <form method="POST" action="<?php echo url('/estudiante/inscribir/' . $curso->id); ?>" class="d-inline">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-primary">Inscribirse</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="panel empty-state-card">
                        <div class="panel-body">
                            <span class="empty-state-icon"><i class="bi bi-journal-x"></i></span>
                            <div class="empty-state-copy">No hay mas cursos publicos disponibles en este momento.</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
