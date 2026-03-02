<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-pencil-square"></i> Edicion de curso</span>
        <h1 class="page-title">Ajusta la configuracion del curso sin perder claridad operativa.</h1>
        <p class="page-subtitle">
            Revisa identidad, modalidad y acceso desde la misma estructura visual del formulario de creacion.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a mis cursos
            </a>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/profesor/cursos/edit/' . $curso->id); ?>">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Identidad del curso</h2>
                                <span class="soft-badge"><i class="bi bi-journal-text"></i> Base academica</span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo del curso *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="titulo"
                                    name="titulo"
                                    required
                                    value="<?php echo htmlspecialchars($curso->titulo); ?>"
                                    placeholder="Ingrese el titulo del curso"
                                >
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe brevemente el curso"><?php echo htmlspecialchars($curso->descripcion); ?></textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="idioma" class="form-label">Idioma *</label>
                                    <select class="form-select" id="idioma" name="idioma" required>
                                        <option value="ingles" <?php echo $curso->idioma == 'ingles' ? 'selected' : ''; ?>>Ingles</option>
                                        <option value="frances" <?php echo $curso->idioma == 'frances' ? 'selected' : ''; ?>>Frances</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="nivel_cefr" class="form-label">Nivel CEFR *</label>
                                    <select class="form-select" id="nivel_cefr" name="nivel_cefr" required>
                                        <option value="A1" <?php echo $curso->nivel_cefr == 'A1' ? 'selected' : ''; ?>>A1 - Principiante</option>
                                        <option value="A2" <?php echo $curso->nivel_cefr == 'A2' ? 'selected' : ''; ?>>A2 - Elemental</option>
                                        <option value="B1" <?php echo $curso->nivel_cefr == 'B1' ? 'selected' : ''; ?>>B1 - Intermedio bajo</option>
                                        <option value="B2" <?php echo $curso->nivel_cefr == 'B2' ? 'selected' : ''; ?>>B2 - Intermedio alto</option>
                                        <option value="C1" <?php echo $curso->nivel_cefr == 'C1' ? 'selected' : ''; ?>>C1 - Avanzado</option>
                                        <option value="C2" <?php echo $curso->nivel_cefr == 'C2' ? 'selected' : ''; ?>>C2 - Dominio</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Configuracion academica</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Operacion</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="modalidad" class="form-label">Modalidad *</label>
                                    <select class="form-select" id="modalidad" name="modalidad" required>
                                        <option value="perpetuo" <?php echo $curso->modalidad == 'perpetuo' ? 'selected' : ''; ?>>Perpetuo</option>
                                        <option value="ciclo" <?php echo $curso->modalidad == 'ciclo' ? 'selected' : ''; ?>>Ciclo con fechas</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="max_estudiantes" class="form-label">Maximo de estudiantes</label>
                                    <input type="number" class="form-control" id="max_estudiantes" name="max_estudiantes" min="0" value="<?php echo (int) $curso->max_estudiantes; ?>" placeholder="0 = sin limite">
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Acceso e inscripcion</h2>
                                <span class="soft-badge"><i class="bi bi-shield-lock"></i> Acceso</span>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="es_publico" name="es_publico" <?php echo $curso->es_publico ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="es_publico">Curso publico y visible para estudiantes</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requiere_codigo" name="requiere_codigo" <?php echo $curso->requiere_codigo ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="requiere_codigo">Requiere codigo de acceso</label>
                            </div>

                            <div id="codigo_acceso_div" class="mt-3" style="display: <?php echo $curso->requiere_codigo ? 'block' : 'none'; ?>;">
                                <div class="row g-3">
                                    <div class="col-lg-7">
                                        <label for="codigo_acceso" class="form-label">Codigo de acceso</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="codigo_acceso" name="codigo_acceso" value="<?php echo htmlspecialchars($curso->codigo_acceso); ?>" placeholder="Codigo de acceso del curso">
                                            <button class="btn btn-outline-secondary" type="button" id="generar_codigo">Generar</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <label for="tipo_codigo" class="form-label">Tipo de codigo</label>
                                        <select class="form-select" id="tipo_codigo" name="tipo_codigo">
                                            <option value="unico_curso" <?php echo $curso->tipo_codigo == 'unico_curso' ? 'selected' : ''; ?>>Unico para el curso</option>
                                            <option value="por_estudiante" <?php echo $curso->tipo_codigo == 'por_estudiante' ? 'selected' : ''; ?>>Uno por estudiante</option>
                                            <option value="combo_grupo" <?php echo $curso->tipo_codigo == 'combo_grupo' ? 'selected' : ''; ?>>Combo para grupo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/cursos'); ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('requiere_codigo').addEventListener('change', function() {
    document.getElementById('codigo_acceso_div').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('generar_codigo').addEventListener('click', function() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let codigo = '';
    for (let i = 0; i < 8; i++) {
        codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    document.getElementById('codigo_acceso').value = codigo;
});
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
