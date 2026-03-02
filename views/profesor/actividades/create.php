<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>">Actividades</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crear actividad</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Nueva actividad</span>
        <h1 class="page-title">Configura una practica clara para la leccion actual.</h1>
        <p class="page-subtitle">
            Crea la actividad base y luego ajusta su contenido especifico segun el tipo que elijas.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a actividades
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Leccion</div>
                <div class="metric-value"><?php echo (int) $leccion->orden; ?></div>
                <div class="metric-note"><?php echo htmlspecialchars($leccion->titulo); ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Tipos disponibles</div>
                <div class="metric-value"><?php echo count($tipos_actividad); ?></div>
                <div class="metric-note">Opciones configurables para esta practica.</div>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

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

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ejemplo: Completa el dialogo en presente simple">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required placeholder="Indica al estudiante que debe hacer y como responder."></textarea>
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
                                        <option value="">Seleccione un tipo</option>
                                        <?php foreach ($tipos_actividad as $tipo => $label): ?>
                                            <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="1" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="puntos_maximos" class="form-label">Puntos maximos</label>
                                    <input type="number" class="form-control" id="puntos_maximos" name="puntos_maximos" value="10" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tiempo_limite_minutos" class="form-label">Tiempo limite en minutos</label>
                                    <input type="number" class="form-control" id="tiempo_limite_minutos" name="tiempo_limite_minutos" value="10" min="1" required>
                                </div>
                            </div>
                        </section>

                        <div class="alert alert-info mt-4" role="alert">
                            <i class="bi bi-info-circle"></i>
                            Despues de crear la actividad podras configurar el contenido especifico segun el tipo seleccionado.
                        </div>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear actividad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
