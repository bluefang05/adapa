<?php require_once __DIR__ . '/../../partials/header.php'; ?>

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
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Piezas</div>
                <div class="metric-value"><?php echo count($teorias); ?></div>
                <div class="metric-note">Bloques teoricos dentro de esta leccion.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Duracion total</div>
                <div class="metric-value"><?php echo array_reduce($teorias, fn($carry, $item) => $carry + (int) $item->duracion_minutos, 0); ?></div>
                <div class="metric-note">Minutos estimados de estudio.</div>
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
        <div class="row g-4">
            <?php foreach ($teorias as $teoria): ?>
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
                                <span><i class="bi bi-collection"></i> <?php echo !empty($teoria->bloques) ? count($teoria->bloques) : 0; ?> bloques</span>
                            </div>

                            <div class="responsive-actions mt-4">
                                <a href="<?php echo url('/profesor/teoria/edit/' . $teoria->id); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
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
