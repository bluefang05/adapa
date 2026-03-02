<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-clipboard-check"></i> Respuestas del curso</span>
        <h1 class="page-title"><?php echo htmlspecialchars($curso->titulo); ?></h1>
        <p class="page-subtitle">
            Revisa respuestas, detecta pendientes y entra rapido a la revision individual de cada actividad.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/calificaciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Respuestas</div>
                <div class="metric-value"><?php echo count($respuestas); ?></div>
                <div class="metric-note">Registros visibles en esta revision.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Pendientes</div>
                <div class="metric-value">
                    <?php echo array_reduce($respuestas, fn($carry, $item) => $carry + (($item->puntuacion === null || ($item->puntuacion == 0 && ($item->tipo_actividad == 'escritura' || $item->tipo_actividad == 'escucha'))) ? 1 : 0), 0); ?>
                </div>
                <div class="metric-note">Items que todavia necesitan criterio docente.</div>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <section>
        <div class="section-title">
            <h2>Respuestas registradas</h2>
            <span class="soft-badge"><i class="bi bi-list-check"></i> Lectura operativa</span>
        </div>

        <?php if (empty($respuestas)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-clipboard-x"></i></span>
                    <div class="empty-state-copy">No hay respuestas registradas para este curso.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="data-table-shell">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Estudiante</th>
                                <th>Leccion</th>
                                <th>Actividad</th>
                                <th>Puntuacion</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($respuestas as $respuesta): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($respuesta->fecha_respuesta)); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($respuesta->estudiante_nombre . ' ' . $respuesta->estudiante_apellido); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($respuesta->estudiante_email ?? ''); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($respuesta->leccion_titulo); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($respuesta->actividad_titulo); ?></div>
                                        <span class="soft-badge"><?php echo htmlspecialchars($respuesta->tipo_actividad); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($respuesta->puntuacion === null): ?>
                                            <span class="soft-badge">Pendiente</span>
                                        <?php elseif ($respuesta->puntuacion == 0 && ($respuesta->tipo_actividad == 'escritura' || $respuesta->tipo_actividad == 'escucha')): ?>
                                            <span class="soft-badge">Pendiente (0.00)</span>
                                        <?php else: ?>
                                            <span class="soft-badge"><?php echo number_format($respuesta->puntuacion, 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo url('/profesor/calificaciones/revisar/' . $respuesta->id); ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> Revisar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mobile-table-hint">En movil puedes desplazar horizontalmente la tabla para ver todas las columnas.</div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
