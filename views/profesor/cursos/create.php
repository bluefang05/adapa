<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-plus-circle"></i> Nuevo curso</span>
        <h1 class="page-title">Crea un curso listo para escalar a contenido, cupos y acceso.</h1>
        <p class="page-subtitle">
            Define idioma objetivo, idioma de ensenanza, nivel y reglas de acceso desde un formulario mas claro en escritorio y movil.
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

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert alert-info">
            <i class="bi bi-shield-check"></i>
            Plan gratuito activo: este curso piloto quedara privado, funcionara por codigo y mantendra un cupo maximo de 3 estudiantes.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/profesor/cursos/create'); ?>" id="formCrearCurso" novalidate>
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Identidad del curso</h2>
                                <span class="soft-badge"><i class="bi bi-journal-text"></i> Base academica</span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo del curso *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ejemplo: Ingles B1 para comunicacion profesional" maxlength="200">
                                <div class="invalid-feedback">Por favor ingrese un titulo para el curso.</div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion *</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required placeholder="Describe objetivos, enfoque y alcance del curso." maxlength="1000"></textarea>
                                <div class="invalid-feedback">Por favor ingrese una descripcion para el curso.</div>
                                <div class="form-text">Maximo 1000 caracteres.</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="idioma_objetivo" class="form-label">Idioma objetivo *</label>
                                    <select class="form-select" id="idioma_objetivo" name="idioma_objetivo" required>
                                        <option value="">Seleccione el idioma que se aprende</option>
                                        <?php foreach (app_course_target_languages() as $languageValue => $languageLabel): ?>
                                            <option value="<?php echo htmlspecialchars($languageValue); ?>"><?php echo htmlspecialchars($languageLabel); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione el idioma objetivo.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="idioma_ensenanza" class="form-label">Idioma de ensenanza *</label>
                                    <select class="form-select" id="idioma_ensenanza" name="idioma_ensenanza" required>
                                        <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                                            <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'espanol' ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($languageLabel); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Idioma desde el cual explicas el curso.</div>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label for="nivel_cefr" class="form-label">Nivel principal *</label>
                                    <select class="form-select" id="nivel_cefr" name="nivel_cefr" required>
                                        <option value="">Seleccione un nivel principal</option>
                                        <option value="A1">A1 - Principiante</option>
                                        <option value="A2">A2 - Elemental</option>
                                        <option value="B1">B1 - Intermedio bajo</option>
                                        <option value="B2">B2 - Intermedio alto</option>
                                        <option value="C1">C1 - Avanzado</option>
                                        <option value="C2">C2 - Dominio</option>
                                    </select>
                                    <div class="form-text">Usado como compatibilidad y punto de entrada principal del curso.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="nivel_cefr_desde" class="form-label">Rango desde *</label>
                                    <select class="form-select" id="nivel_cefr_desde" name="nivel_cefr_desde" required>
                                        <option value="A1">A1</option>
                                        <option value="A2">A2</option>
                                        <option value="B1">B1</option>
                                        <option value="B2">B2</option>
                                        <option value="C1">C1</option>
                                        <option value="C2">C2</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="nivel_cefr_hasta" class="form-label">Rango hasta *</label>
                                    <select class="form-select" id="nivel_cefr_hasta" name="nivel_cefr_hasta" required>
                                        <option value="A1">A1</option>
                                        <option value="A2">A2</option>
                                        <option value="B1">B1</option>
                                        <option value="B2">B2</option>
                                        <option value="C1">C1</option>
                                        <option value="C2">C2</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Portada visual</h2>
                                <span class="soft-badge"><i class="bi bi-image"></i> Catalogo</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-7">
                                    <label for="portada_media_id" class="form-label">Imagen de portada</label>
                                    <select class="form-select" id="portada_media_id" name="portada_media_id">
                                        <option value="">Sin portada personalizada</option>
                                        <?php foreach ($recursosImagen as $recurso): ?>
                                            <option
                                                value="<?php echo (int) $recurso->id; ?>"
                                                data-preview-url="<?php echo htmlspecialchars(url('/' . ltrim($recurso->ruta_archivo, '/'))); ?>"
                                                data-preview-alt="<?php echo htmlspecialchars($recurso->alt_text ?: $recurso->titulo); ?>"
                                            >
                                                <?php echo htmlspecialchars($recurso->titulo); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Usa una imagen de tu biblioteca para que el curso se vea mejor en catalogos y paneles.</div>
                                </div>
                                <div class="col-lg-5">
                                    <div id="courseCoverPreview" class="course-cover-preview is-empty">
                                        <span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin portada</span>
                                    </div>
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
                                        <option value="">Seleccione una modalidad</option>
                                        <option value="perpetuo">Perpetuo</option>
                                        <option value="ciclo">Ciclo con fechas</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione una modalidad.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="max_estudiantes" class="form-label">Maximo de estudiantes</label>
                                    <input type="number" class="form-control" id="max_estudiantes" name="max_estudiantes" min="0" max="500" value="30" placeholder="30">
                                    <div class="form-text">Usa 0 o deja vacio para no limitar cupos.</div>
                                </div>
                            </div>

                            <div id="fechas_ciclo" class="mt-3" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha_fin" class="form-label">Fecha de fin</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Acceso e inscripcion</h2>
                                <span class="soft-badge"><i class="bi bi-shield-lock"></i> Acceso</span>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="es_publico" name="es_publico">
                                <label class="form-check-label" for="es_publico">Curso publico y visible para estudiantes</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requiere_codigo" name="requiere_codigo">
                                <label class="form-check-label" for="requiere_codigo">Requiere codigo de acceso para inscribirse</label>
                            </div>

                            <div id="codigo_acceso_div" class="mt-3" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-lg-7">
                                        <label for="codigo_acceso" class="form-label">Codigo de acceso</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="codigo_acceso" name="codigo_acceso" placeholder="Codigo de acceso del curso">
                                            <button class="btn btn-outline-secondary" type="button" id="generar_codigo">Generar</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <label for="tipo_codigo" class="form-label">Tipo de codigo</label>
                                        <select class="form-select" id="tipo_codigo" name="tipo_codigo">
                                            <option value="unico_curso">Unico para el curso</option>
                                            <option value="por_estudiante">Uno por estudiante</option>
                                            <option value="combo_grupo">Combo para grupo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/cursos'); ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formCrearCurso').addEventListener('submit', function(event) {
    event.preventDefault();
    event.stopPropagation();

    if (this.checkValidity()) {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        const modalidad = document.getElementById('modalidad').value;
        const fromIndex = cefrOrder.indexOf(nivelDesde.value);
        const toIndex = cefrOrder.indexOf(nivelHasta.value);

        if (modalidad === 'ciclo' && fechaInicio && fechaFin && new Date(fechaInicio) >= new Date(fechaFin)) {
            alert('La fecha de fin debe ser posterior a la fecha de inicio');
            return false;
        }

        if (fromIndex > toIndex) {
            alert('El rango CEFR no es valido: el nivel inicial no puede ser mayor que el final.');
            return false;
        }

        this.submit();
    }

    this.classList.add('was-validated');
});

document.getElementById('modalidad').addEventListener('change', function() {
    const fechasCiclo = document.getElementById('fechas_ciclo');
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    if (this.value === 'ciclo') {
        fechasCiclo.style.display = 'block';
        fechaInicio.required = true;
        fechaFin.required = true;
    } else {
        fechasCiclo.style.display = 'none';
        fechaInicio.required = false;
        fechaFin.required = false;
        fechaInicio.value = '';
        fechaFin.value = '';
    }
});

document.getElementById('requiere_codigo').addEventListener('change', function() {
    const codigoDiv = document.getElementById('codigo_acceso_div');
    const codigoInput = document.getElementById('codigo_acceso');

    if (this.checked) {
        codigoDiv.style.display = 'block';
        codigoInput.required = true;
        if (!codigoInput.value) {
            generarCodigoAcceso();
        }
    } else {
        codigoDiv.style.display = 'none';
        codigoInput.required = false;
        codigoInput.value = '';
    }
});

function generarCodigoAcceso() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let codigo = '';
    for (let i = 0; i < 8; i++) {
        codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    document.getElementById('codigo_acceso').value = codigo;
}

document.getElementById('generar_codigo').addEventListener('click', generarCodigoAcceso);

const nivelPrincipal = document.getElementById('nivel_cefr');
const nivelDesde = document.getElementById('nivel_cefr_desde');
const nivelHasta = document.getElementById('nivel_cefr_hasta');
const cefrOrder = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

function sincronizarNivelPrincipal() {
    if (!nivelPrincipal.value) {
        nivelPrincipal.value = nivelDesde.value;
    }
}

function validarRangoCefr() {
    const fromIndex = cefrOrder.indexOf(nivelDesde.value);
    const toIndex = cefrOrder.indexOf(nivelHasta.value);

    if (fromIndex > toIndex) {
        nivelHasta.value = nivelDesde.value;
    }

    sincronizarNivelPrincipal();
}

nivelPrincipal.addEventListener('change', function () {
    if (!nivelDesde.value) {
        nivelDesde.value = this.value;
    }
});

nivelDesde.addEventListener('change', validarRangoCefr);
nivelHasta.addEventListener('change', validarRangoCefr);
sincronizarNivelPrincipal();

const portadaSelect = document.getElementById('portada_media_id');
const portadaPreview = document.getElementById('courseCoverPreview');

function actualizarPreviewPortada() {
    const option = portadaSelect.options[portadaSelect.selectedIndex];
    const previewUrl = option ? option.getAttribute('data-preview-url') : '';
    const previewAlt = option ? option.getAttribute('data-preview-alt') : '';

    if (previewUrl) {
        portadaPreview.classList.remove('is-empty');
        portadaPreview.innerHTML = '<img src="' + previewUrl + '" alt="' + previewAlt + '" class="course-cover-image">';
        return;
    }

    portadaPreview.classList.add('is-empty');
    portadaPreview.innerHTML = '<span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin portada</span>';
}

portadaSelect.addEventListener('change', actualizarPreviewPortada);
actualizarPreviewPortada();

document.getElementById('fecha_inicio').addEventListener('change', function() {
    const fechaFin = document.getElementById('fecha_fin');
    if (fechaFin.value && this.value >= fechaFin.value) {
        fechaFin.value = '';
    }
    fechaFin.min = this.value;
});

document.getElementById('fecha_fin').addEventListener('change', function() {
    const fechaInicio = document.getElementById('fecha_inicio');
    if (fechaInicio.value && this.value <= fechaInicio.value) {
        this.value = '';
        alert('La fecha de fin debe ser posterior a la fecha de inicio');
    }
});
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
