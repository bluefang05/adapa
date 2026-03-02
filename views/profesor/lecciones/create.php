<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-plus-circle"></i> Nueva leccion</span>
        <h1 class="page-title">Agrega una leccion clara y bien ordenada dentro del curso.</h1>
        <p class="page-subtitle">
            Define orden, duracion y visibilidad desde una vista mas legible para escritorio y movil.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones/create'); ?>">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Base de la leccion</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> Curso: <?php echo htmlspecialchars($curso->titulo); ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la leccion *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ejemplo: Introduccion a los verbos en ingles">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe brevemente el contenido de esta leccion."></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Orden y estado</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Secuencia</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="orden" class="form-label">Orden *</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?php echo (int) $siguiente_orden; ?>" min="1" required>
                                    <div class="form-text">Posicion de la leccion dentro del curso.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="duracion_minutos" class="form-label">Duracion estimada en minutos</label>
                                    <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="1" placeholder="Ejemplo: 45">
                                    <div class="form-text">Tiempo estimado para completar la leccion.</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="es_obligatoria" name="es_obligatoria" value="1" checked>
                                        <label class="form-check-label" for="es_obligatoria">Leccion obligatoria</label>
                                    </div>
                                    <div class="form-text">El estudiante debe completarla para avanzar.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="borrador" selected>Borrador</option>
                                        <option value="publicada">Publicada</option>
                                        <option value="archivada">Archivada</option>
                                    </select>
                                    <div class="form-text">Las lecciones en borrador no se muestran al estudiante.</div>
                                </div>
                            </div>
                        </section>

                        <div class="alert alert-info mt-4">
                            <i class="bi bi-lightbulb"></i>
                            Despues podras agregar teoria y actividades para construir el recorrido completo de esta leccion.
                        </div>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear leccion
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
