<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../../models/Teoria.php'; ?>

<?php
$puedePasarAPractica = !empty($teorias);
$teoriaSummary = [
    'total_bloques' => array_reduce($teorias, fn($carry, $item) => $carry + (int) (!empty($item->bloques) ? count($item->bloques) : 0), 0),
    'duracion_total' => array_reduce($teorias, fn($carry, $item) => $carry + (int) ($item->duracion_minutos ?? 0), 0),
];
$currentReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/lecciones/' . $leccion->id . '/teoria');
$teoriaSummaries = [];
$teoriaPorPulir = null;

foreach ($teorias as $teoria) {
    $summary = Teoria::resumenDocente($teoria);
    $teoriaSummaries[$teoria->id] = $summary;
    if ($teoriaPorPulir === null && empty($summary['ready_for_practice'])) {
        $teoriaPorPulir = [
            'teoria' => $teoria,
            'summary' => $summary,
        ];
    }
}
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?> - Teoria</li>
        </ol>
    </nav>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-book"></i> Contenido teorico</span>
        <h1 class="page-title">Teoria de <?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">
            Organiza explicaciones, recursos y piezas de apoyo antes de pasar al bloque practico de la leccion.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/builder'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-diagram-3"></i> Constructor
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria/create?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Anadir teoria
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/preview'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Vista completa
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-lightning-charge"></i> Ver practica
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-book"></i> <?php echo count($teorias); ?> piezas</span>
            <span class="soft-badge"><i class="bi bi-clock"></i> <?php echo $teoriaSummary['duracion_total']; ?> min estimados</span>
            <span class="soft-badge"><i class="bi bi-collection"></i> <?php echo $teoriaSummary['total_bloques']; ?> bloques</span>
            <span class="soft-badge <?php echo $teoriaPorPulir ? 'warning' : 'success'; ?>"><i class="bi bi-check2-circle"></i> <?php echo $teoriaPorPulir ? 'Requiere una pasada' : 'Base teorica en forma'; ?></span>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (empty($teorias)): ?>
        <div class="panel">
            <div class="panel-body">
                Todavia no hay contenido teorico para esta leccion.
                <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria/create'); ?>" class="btn btn-primary ms-2">Crear la primera pieza</a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert context-note mb-4">
            <div class="split-head">
                <div>
                    <div class="metric-label">Siguiente paso recomendado</div>
                    <div class="fw-semibold mt-1">
                        <?php if (!$puedePasarAPractica): ?>
                            Crea al menos una pieza teorica antes de seguir.
                        <?php elseif ($teoriaPorPulir): ?>
                            La pieza "<?php echo htmlspecialchars($teoriaPorPulir['teoria']->titulo); ?>" todavia merece una pasada mas.
                        <?php else: ?>
                            La base teorica ya sostiene la practica. Puedes pasar a actividades o revisar el preview completo.
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1">
                        <?php if (!$puedePasarAPractica): ?>
                            La leccion todavia no tiene soporte conceptual.
                        <?php elseif ($teoriaPorPulir): ?>
                            <?php echo htmlspecialchars($teoriaPorPulir['summary']['message']); ?>
                        <?php else: ?>
                            Ya tienes teoria suficiente para validar orden, copy y transicion hacia la practica.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="responsive-actions">
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria/create?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-primary">Anadir teoria</a>
                    <?php if ($teoriaPorPulir): ?>
                        <a href="<?php echo url('/profesor/teoria/edit/' . $teoriaPorPulir['teoria']->id . '?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-outline-primary">Pulir pieza detectada</a>
                    <?php elseif ($puedePasarAPractica): ?>
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">Pasar a practica</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($teorias as $teoria): ?>
                <?php
                $summary = $teoriaSummaries[$teoria->id] ?? Teoria::resumenDocente($teoria);
                ?>
                <div class="col-xl-6">
                    <article class="surface-card h-100">
                        <div class="card-body">
                            <div class="split-head mb-3">
                                <div>
                                    <h3 class="h4 mb-1"><?php echo htmlspecialchars($teoria->titulo); ?></h3>
                                    <div class="small text-muted text-capitalize"><?php echo htmlspecialchars($teoria->tipo_contenido); ?></div>
                                </div>
                                <span class="soft-badge">Orden <?php echo (int) $teoria->orden; ?></span>
                            </div>

                            <div class="course-meta">
                                <span><i class="bi bi-clock"></i> <?php echo (int) $teoria->duracion_minutos; ?> min</span>
                                <span><i class="bi bi-collection"></i> <?php echo (int) $summary['block_count']; ?> bloques</span>
                                <span><i class="bi bi-images"></i> <?php echo (int) $summary['media_count']; ?> con media</span>
                                <?php if ((int) $summary['tts_count'] > 0): ?>
                                    <span><i class="bi bi-volume-up"></i> <?php echo (int) $summary['tts_count']; ?> con TTS</span>
                                <?php endif; ?>
                                <span><i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($summary['label']); ?></span>
                            </div>

                            <div class="small text-muted mt-3"><?php echo htmlspecialchars($summary['message']); ?></div>

                            <div class="responsive-actions mt-4">
                                <form method="POST" action="<?php echo url('/profesor/teoria/move-up/' . $teoria->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentReturnTo); ?>">
                                    <button type="submit" class="btn btn-outline-secondary" title="Subir teoria">
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/teoria/move-down/' . $teoria->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentReturnTo); ?>">
                                    <button type="submit" class="btn btn-outline-secondary" title="Bajar teoria">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="<?php echo url('/profesor/teoria/edit/' . $teoria->id . '?return_to=' . rawurlencode($currentReturnTo)); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/teoria/duplicate/' . $teoria->id) . '?return_to=' . rawurlencode($currentReturnTo); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-copy"></i> Duplicar
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/teoria/duplicate/' . $teoria->id) . '?continue_to=edit&return_to=' . rawurlencode($currentReturnTo); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-copy"></i> Duplicar y ajustar
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/teoria/delete/' . $teoria->id); ?>" onsubmit="return confirm('Estas seguro de eliminar esta teoria?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentReturnTo); ?>">
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
