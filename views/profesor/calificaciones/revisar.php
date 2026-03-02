<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-search"></i> Revision individual</span>
        <h1 class="page-title">Revisa la respuesta y deja una devolucion util.</h1>
        <p class="page-subtitle">
            Tienes a la vista la actividad, la respuesta del estudiante y el formulario de calificacion en una sola pantalla.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/calificaciones/curso/' . $actividad->curso_id); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al curso
            </a>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-5">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Actividad</h2>
                        <span class="soft-badge"><?php echo htmlspecialchars(ucfirst($actividad->tipo_actividad)); ?></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Titulo</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($actividad->titulo); ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Instrucciones</label>
                        <div class="content-block">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($actividad->instrucciones)); ?>
                            </div>
                        </div>
                    </div>

                    <?php 
                    $config = json_decode($actividad->contenido);
                    if ($actividad->tipo_actividad == 'escritura' && isset($config->tema)): 
                    ?>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Tema</label>
                            <div class="content-block">
                                <div class="card-body">
                                    <?php echo htmlspecialchars($config->tema); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-7">
            <section class="form-shell">
                <div class="card-body">
                    <div class="section-title">
                        <h2>Respuesta del estudiante</h2>
                        <span class="soft-badge"><i class="bi bi-person"></i> Revision manual</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Contenido de la respuesta</label>
                        <div class="content-block">
                            <div class="card-body" style="min-height: 140px; white-space: pre-wrap;"><?php echo htmlspecialchars($respuesta->respuesta_texto); ?></div>
                        </div>
                    </div>

                    <form action="<?php echo url('/profesor/calificaciones/calificar/' . $respuesta->id); ?>" method="POST">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="curso_id" value="<?php echo $actividad->curso_id; ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="puntuacion" class="form-label fw-bold">Puntuacion (0 - <?php echo $actividad->puntos_maximos; ?>)</label>
                                <input type="number" step="0.01" min="0" max="<?php echo $actividad->puntos_maximos; ?>" class="form-control" id="puntuacion" name="puntuacion" value="<?php echo htmlspecialchars((string) $respuesta->puntuacion); ?>" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="comentarios" class="form-label fw-bold">Comentarios y retroalimentacion</label>
                            <textarea class="form-control" id="comentarios" name="comentarios" rows="6" placeholder="Escribe aqui tus correcciones y comentarios para el estudiante."><?php echo htmlspecialchars($respuesta->comentarios ?? ''); ?></textarea>
                        </div>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/calificaciones/curso/' . $actividad->curso_id); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar calificacion
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
