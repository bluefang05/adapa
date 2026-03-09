<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-award"></i> Revision docente</span>
        <h1 class="page-title">Selecciona un curso para entrar al flujo de calificacion.</h1>
        <p class="page-subtitle">
            Esta vista resume rapido que cursos tienen respuestas pendientes y te lleva directo al detalle.
        </p>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-journal-bookmark"></i> <?php echo count($cursos); ?> cursos</span>
            <span class="soft-badge <?php echo array_reduce($cursos, fn($carry, $item) => $carry + (int) ($item->pendientes ?? 0), 0) > 0 ? 'warning' : 'success'; ?>"><i class="bi bi-hourglass-split"></i> <?php echo array_reduce($cursos, fn($carry, $item) => $carry + (int) ($item->pendientes ?? 0), 0); ?> pendientes</span>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <section class="panel">
        <div class="panel-body">
            <div class="section-title">
                <h2>Cursos con respuestas</h2>
                <span class="soft-badge"><i class="bi bi-grid"></i> Prioriza revision</span>
            </div>

            <?php if (empty($cursos)): ?>
                <div class="empty-state">No tienes cursos asignados.</div>
            <?php else: ?>
                <div class="stack-list">
                    <?php foreach ($cursos as $curso): ?>
                        <a href="<?php echo url('/profesor/calificaciones/curso/' . $curso->id); ?>" class="stack-item text-decoration-none">
                            <div>
                                <p class="stack-item-title"><?php echo htmlspecialchars($curso->titulo); ?></p>
                                <div class="stack-item-subtitle"><?php echo htmlspecialchars($curso->descripcion); ?></div>
                            </div>
                            <div class="responsive-actions">
                                <?php if (isset($curso->pendientes) && $curso->pendientes > 0): ?>
                                    <span class="soft-badge"><?php echo (int) $curso->pendientes; ?> pendientes</span>
                                <?php endif; ?>
                                <span class="btn btn-outline-primary btn-sm">Ver respuestas</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
