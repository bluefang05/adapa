<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-award"></i> Rendimiento</span>
        <h1 class="page-title">Tus respuestas ya cuentan una historia.</h1>
        <p class="page-subtitle">
            Consulta puntajes, tipo de actividad y fechas de entrega desde una vista clara y util para seguimiento.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/estudiante'); ?>" class="btn btn-primary">
                <i class="bi bi-journal-text"></i> Volver al dashboard
            </a>
            <a href="<?php echo url('/estudiante/progreso'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up-arrow"></i> Ver progreso
            </a>
        </div>
        <?php
        $graded = array_values(array_filter($calificaciones, fn($item) => $item->puntuacion !== null && $item->puntos_maximos > 0));
        $avg = 0;
        if (!empty($graded)) {
            $avg = (int) round(array_reduce($graded, function ($carry, $item) {
                return $carry + (((float) $item->puntuacion / (float) $item->puntos_maximos) * 100);
            }, 0) / count($graded));
        }
        ?>
        <?php if (!empty($calificacionesScopeHint)): ?>
            <div class="alert context-note mt-3 mb-0"><?php echo htmlspecialchars($calificacionesScopeHint); ?></div>
        <?php endif; ?>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-chat-square-text"></i> <?php echo count($calificaciones); ?> respuestas</span>
            <span class="soft-badge"><i class="bi bi-check2-circle"></i> <?php echo count(array_filter($calificaciones, fn($item) => $item->puntuacion !== null)); ?> evaluadas</span>
            <span class="soft-badge"><i class="bi bi-award"></i> <?php echo $avg; ?>% promedio</span>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Historial de calificaciones</h2>
            <span class="soft-badge"><i class="bi bi-clock-history"></i> Registro reciente</span>
        </div>

        <?php if (empty($calificaciones)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-award"></i></span>
                    <div class="empty-state-copy">Aun no tienes respuestas registradas para mostrar calificaciones.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="data-table-shell">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Leccion</th>
                                <th>Actividad</th>
                                <th>Tipo</th>
                                <th>Puntaje</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($calificaciones as $item): ?>
                                <?php
                                $scoreLabel = 'Pendiente';
                                $scoreTone = 'warning';
                                if ($item->puntuacion !== null) {
                                    $scoreLabel = number_format((float) $item->puntuacion, 1) . ' / ' . (int) $item->puntos_maximos;
                                    $scoreTone = ((float) $item->puntuacion >= ((float) $item->puntos_maximos * 0.7)) ? 'success' : 'warning';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($item->curso_titulo); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($item->leccion_titulo); ?></td>
                                    <td><?php echo htmlspecialchars($item->actividad_titulo); ?></td>
                                    <td><span class="soft-badge"><?php echo htmlspecialchars(str_replace('_', ' ', $item->tipo_actividad)); ?></span></td>
                                    <td><span class="soft-badge <?php echo $scoreTone; ?>"><?php echo $scoreLabel; ?></span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($item->fecha_respuesta)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mobile-table-hint">En movil puedes desplazar horizontalmente la tabla para ver todo el historial.</div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
