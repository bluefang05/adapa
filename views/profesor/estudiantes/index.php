<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-people"></i> Seguimiento docente</span>
        <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="page-subtitle">
            Mira inscritos, porcentaje de avance y carga de teoria y practica sin ir curso por curso.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos'); ?>" class="btn btn-primary">
                <i class="bi bi-journal-bookmark-fill"></i> Volver a cursos
            </a>
            <a href="<?php echo url('/profesor/calificaciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-check2-square"></i> Ver calificaciones
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Registros</div>
                <div class="metric-value"><?php echo count($estudiantes); ?></div>
                <div class="metric-note">Filas activas en esta vista.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Promedio avance</div>
                <div class="metric-value">
                    <?php
                    $avg = 0;
                    if (!empty($estudiantes)) {
                        $avg = (int) round(array_reduce($estudiantes, fn($carry, $item) => $carry + (int) $item->porcentaje, 0) / count($estudiantes));
                    }
                    echo $avg;
                    ?>%
                </div>
                <div class="metric-note">Progreso promedio de la vista actual.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Actividades respondidas</div>
                <div class="metric-value"><?php echo array_reduce($estudiantes, fn($carry, $item) => $carry + (int) $item->actividades_respondidas, 0); ?></div>
                <div class="metric-note">Practica ya registrada por estudiantes.</div>
            </div>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Vista de estudiantes</h2>
            <span class="soft-badge"><i class="bi bi-person-lines-fill"></i> Estado actual</span>
        </div>

        <?php if (empty($estudiantes)): ?>
            <div class="panel">
                <div class="panel-body">
                    Todavia no hay estudiantes inscritos en los cursos visibles para tu cuenta.
                </div>
            </div>
        <?php else: ?>
            <div class="data-table-shell">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Estudiante</th>
                                <th>Email</th>
                                <th>Inscripcion</th>
                                <th>Progreso</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($estudiante->curso_titulo); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars(trim($estudiante->nombre . ' ' . $estudiante->apellido)); ?></td>
                                    <td><?php echo htmlspecialchars($estudiante->email); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($estudiante->fecha_inscripcion)); ?></td>
                                    <td style="min-width: 220px;">
                                        <div class="progress mb-2" role="progressbar" aria-valuenow="<?php echo (int) $estudiante->porcentaje; ?>" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                                            <div class="progress-bar" style="width: <?php echo (int) $estudiante->porcentaje; ?>%"></div>
                                        </div>
                                        <div class="small text-muted"><?php echo (int) $estudiante->porcentaje; ?>% completado</div>
                                    </td>
                                    <td class="small text-muted">
                                        Teoria <?php echo (int) $estudiante->teorias_leidas; ?>/<?php echo (int) $estudiante->total_teorias; ?><br>
                                        Actividades <?php echo (int) $estudiante->actividades_respondidas; ?>/<?php echo (int) $estudiante->total_actividades; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
