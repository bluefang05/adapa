<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?> - Actividades</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Practica de la leccion</span>
        <h1 class="page-title">Actividades de <?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">
            Revisa la practica creada, ajusta el orden y entra rapido a edicion o vista de estudiante.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <?php if (!empty($puedeCrearActividad)): ?>
                <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create'); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva actividad
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-lock"></i> Limite de actividades alcanzado
                </button>
            <?php endif; ?>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Actividades</div>
                <div class="metric-value"><?php echo count($actividades); ?></div>
                <div class="metric-note">Practicas dentro de esta leccion.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Puntos totales</div>
                <div class="metric-value"><?php echo array_reduce($actividades, fn($carry, $item) => $carry + (int) $item->puntos_maximos, 0); ?></div>
                <div class="metric-note">Suma de puntuacion maxima disponible.</div>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-lightbulb"></i>
            Plan gratuito: cada leccion admite hasta 3 actividades. <?php echo !empty($mensajeLimiteActividad) ? htmlspecialchars($mensajeLimiteActividad) : 'Aun tienes espacio para una practica mas.'; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($actividades)): ?>
        <div class="panel">
            <div class="panel-body">
                Todavia no hay actividades para esta leccion.
                <?php if (!empty($puedeCrearActividad)): ?>
                    <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades/create'); ?>" class="btn btn-primary ms-2">Crear la primera</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($actividades as $actividad): ?>
                <div class="col-xl-6">
                    <article class="surface-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                <div>
                                    <h3 class="h4 mb-1"><?php echo htmlspecialchars($actividad->titulo); ?></h3>
                                    <div class="small text-muted text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $actividad->tipo_actividad)); ?></div>
                                </div>
                                <span class="soft-badge">Orden <?php echo (int) $actividad->orden; ?></span>
                            </div>

                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($actividad->descripcion)); ?></p>

                            <div class="course-meta">
                                <span><i class="bi bi-clock"></i> <?php echo (int) $actividad->tiempo_limite_minutos; ?> min</span>
                                <span><i class="bi bi-award"></i> <?php echo (int) $actividad->puntos_maximos; ?> puntos</span>
                            </div>

                            <div class="responsive-actions mt-4">
                                <a href="<?php echo url('/profesor/actividad/edit/' . $actividad->id); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/preview'); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Vista estudiante
                                </a>
                                <form method="POST" action="<?php echo url('/profesor/actividad/delete/' . $actividad->id); ?>" onsubmit="return confirm('Esta seguro de eliminar esta actividad?');">
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
