<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<?php
$puedePasarAPractica = !empty($teorias);
$teoriaSummary = [
    'total_bloques' => array_reduce($teorias, fn($carry, $item) => $carry + (int) (!empty($item->bloques) ? count($item->bloques) : 0), 0),
    'duracion_total' => array_reduce($teorias, fn($carry, $item) => $carry + (int) ($item->duracion_minutos ?? 0), 0),
];
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?> - Teoria</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-book"></i> Contenido teorico</span>
        <h1 class="page-title">Teoria de <?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">
            Organiza explicaciones, recursos y piezas de apoyo antes de pasar al bloque practico de la leccion.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria/create'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Anadir teoria
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/preview'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Vista completa
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-lightning-charge"></i> Ver practica
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Piezas</div>
                <div class="metric-value"><?php echo count($teorias); ?></div>
                <div class="metric-note">Bloques teoricos dentro de esta leccion.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Duracion total</div>
                <div class="metric-value"><?php echo $teoriaSummary['duracion_total']; ?></div>
                <div class="metric-note">Minutos estimados de estudio.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Bloques</div>
                <div class="metric-value"><?php echo $teoriaSummary['total_bloques']; ?></div>
                <div class="metric-note">Piezas estructuradas visibles para el alumno.</div>
            </div>
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
        <section class="panel mb-4">
            <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="metric-label">Siguiente paso recomendado</div>
                    <div class="fw-semibold mt-1"><?php echo $puedePasarAPractica ? 'La base teorica ya existe. Conviene revisar o pasar a practica.' : 'Crea al menos una pieza teorica antes de seguir.'; ?></div>
                    <div class="small text-muted mt-1"><?php echo $puedePasarAPractica ? 'Usa esta vista para detectar piezas flojas o pasar a actividades.' : 'La leccion todavia no tiene soporte conceptual.'; ?></div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria/create'); ?>" class="btn btn-primary">Anadir teoria</a>
                    <?php if ($puedePasarAPractica): ?>
                        <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-primary">Pasar a practica</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <div class="row g-4">
            <?php foreach ($teorias as $teoria): ?>
                <?php
                $blockCount = !empty($teoria->bloques) ? count($teoria->bloques) : 0;
                $qualityLabel = $blockCount >= 4 ? 'Base solida' : 'Puede crecer';
                ?>
                <div class="col-xl-6">
                    <article class="surface-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                <div>
                                    <h3 class="h4 mb-1"><?php echo htmlspecialchars($teoria->titulo); ?></h3>
                                    <div class="small text-muted text-capitalize"><?php echo htmlspecialchars($teoria->tipo_contenido); ?></div>
                                </div>
                                <span class="soft-badge">Orden <?php echo (int) $teoria->orden; ?></span>
                            </div>

                            <div class="course-meta">
                                <span><i class="bi bi-clock"></i> <?php echo (int) $teoria->duracion_minutos; ?> min</span>
                                <span><i class="bi bi-collection"></i> <?php echo $blockCount; ?> bloques</span>
                                <span><i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($qualityLabel); ?></span>
                            </div>

                            <div class="responsive-actions mt-4">
                                <form method="POST" action="<?php echo url('/profesor/teoria/move-up/' . $teoria->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary" title="Subir teoria">
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/teoria/move-down/' . $teoria->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary" title="Bajar teoria">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="<?php echo url('/profesor/teoria/edit/' . $teoria->id); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/teoria/duplicate/' . $teoria->id); ?>">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="bi bi-copy"></i> Duplicar
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo url('/profesor/teoria/delete/' . $teoria->id); ?>" onsubmit="return confirm('Estas seguro de eliminar esta teoria?');">
                                    <?php echo csrf_input(); ?>
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
