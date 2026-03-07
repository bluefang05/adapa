<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>">Actividades</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar actividad</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-pencil"></i> Ajuste de actividad</span>
        <h1 class="page-title">Edita la ficha general antes de entrar al contenido interno.</h1>
        <p class="page-subtitle">
            Aqui corriges datos base de la actividad. La configuracion detallada del contenido sigue en su panel especializado.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a actividades
            </a>
            <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/configurar'); ?>" class="btn btn-success">
                <i class="bi bi-sliders"></i> Configurar contenido
            </a>
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividad/edit/' . $actividad->id)) . '&context=actividad'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Abrir biblioteca
            </a>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check2-circle"></i>
            Recurso contextual listo: <strong><?php echo htmlspecialchars((string) ($_GET['selected_media_title'] ?? 'Recurso seleccionado')); ?></strong>.
            Usa <em>Configurar contenido</em> para insertarlo donde corresponda.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Datos base</h2>
                                <span class="soft-badge"><i class="bi bi-pencil-square"></i> Configuracion</span>
                            </div>
                            <div class="production-hint-card tone-info mb-3">
                                <div class="production-hint-title">Checklist rapido antes de guardar</div>
                                <ul class="quality-checklist-list mb-0">
                                    <li>La ficha general dice que habilidad medira esta actividad.</li>
                                    <li>El tipo sigue alineado con el contenido interno configurado.</li>
                                    <li>Si usas media, aplicala en la configuracion especializada.</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($actividad->titulo); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?php echo htmlspecialchars($actividad->descripcion); ?></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Parametros de evaluacion</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Escala</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tipo_actividad" class="form-label">Tipo de actividad</label>
                                    <select class="form-select" id="tipo_actividad" name="tipo_actividad" required>
                                        <?php foreach ($tipos_actividad as $tipo => $label): ?>
                                            <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo $actividad->tipo_actividad === $tipo ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="1" required value="<?php echo htmlspecialchars($actividad->orden); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="puntos_maximos" class="form-label">Puntos maximos</label>
                                    <input type="number" class="form-control" id="puntos_maximos" name="puntos_maximos" min="1" required value="<?php echo htmlspecialchars($actividad->puntos_maximos); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label for="tiempo_limite_minutos" class="form-label">Tiempo limite en minutos</label>
                                    <input type="number" class="form-control" id="tiempo_limite_minutos" name="tiempo_limite_minutos" min="1" required value="<?php echo htmlspecialchars($actividad->tiempo_limite_minutos); ?>">
                                </div>
                            </div>
                        </section>

                        <div class="alert alert-info mt-4" role="alert">
                            <i class="bi bi-info-circle"></i>
                            Estos cambios afectan la ficha general. El contenido interno de preguntas, audios u opciones se edita aparte.
                        </div>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/configurar'); ?>" class="btn btn-outline-primary">
                                <i class="bi bi-sliders"></i> Configurar contenido
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
