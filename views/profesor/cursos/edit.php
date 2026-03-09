<?php
require_once __DIR__ . '/../../partials/header.php';
$courseEditorialStates = app_course_editorial_states();
$courseEditorialMeta = app_course_editorial_state_meta($curso);
$publishedLessons = (int) ($coursePublishSummary['published_lessons'] ?? 0);
$curso->published_lessons = $publishedLessons;
$catalogStatus = app_course_catalog_status($curso);
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-pencil-square"></i> Edicion de curso</span>
        <h1 class="page-title">Ajusta la configuracion del curso sin perder claridad operativa.</h1>
        <p class="page-subtitle">
            Revisa idioma objetivo, idioma base del estudiante, modalidad y acceso desde la misma estructura visual del formulario de creacion.
        </p>
        <div class="compact-meta-row">
            <span class="soft-badge"><i class="bi bi-clipboard-check"></i> Preparacion <?php echo (int) ($coursePublishSummary['percentage'] ?? 0); ?>%</span>
            <span class="soft-badge <?php echo htmlspecialchars($catalogStatus['tone']); ?>">
                <i class="bi bi-broadcast"></i>
                <?php echo htmlspecialchars($catalogStatus['label']); ?>
            </span>
        </div>
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
        <div class="alert context-note">
            <strong>Plan gratuito:</strong> este curso opera como piloto gratuito: acceso por codigo, cupo maximo de 3 estudiantes y sin inscripcion publica directa.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/profesor/cursos/edit/' . $curso->id); ?>" id="formEditarCurso">
                        <?php echo csrf_input(); ?>

                        <details class="panel page-assist-card mb-4">
                            <summary class="page-assist-summary">
                                <div>
                                    <div class="metric-label">Lectura editorial</div>
                                    <div class="fw-semibold mt-1">Checklist de publicacion y estado real del curso</div>
                                    <div class="small text-muted mt-1">Abre esta seccion si necesitas revisar huecos antes de tocar la configuracion principal.</div>
                                </div>
                                <span class="soft-badge"><?php echo (int) ($coursePublishSummary['ok'] ?? 0); ?>/<?php echo (int) ($coursePublishSummary['total'] ?? 0); ?> puntos</span>
                            </summary>
                            <div class="panel-body pt-0 page-assist-body">
                                <div class="alert context-note mb-3">
                                    <div class="split-head">
                                        <div>
                                            <div class="fw-semibold">Preparacion actual: <?php echo (int) ($coursePublishSummary['percentage'] ?? 0); ?>%</div>
                                            <div class="small text-muted mt-1"><?php echo htmlspecialchars($coursePublishHint ?? ''); ?></div>
                                        </div>
                                        <div class="soft-badge"><?php echo (int) ($coursePublishSummary['ok'] ?? 0); ?>/<?php echo (int) ($coursePublishSummary['total'] ?? 0); ?> puntos</div>
                                    </div>
                                    <div class="readiness-meter mt-3"><span style="width: <?php echo (int) ($coursePublishSummary['percentage'] ?? 0); ?>%"></span></div>
                                    <div class="course-meta mt-3">
                                        <span><i class="bi bi-journal-richtext"></i> <?php echo (int) ($coursePublishSummary['lessons'] ?? 0); ?> lecciones</span>
                                        <span><i class="bi bi-broadcast"></i> <?php echo $publishedLessons; ?> publicadas</span>
                                        <span><i class="bi bi-book"></i> <?php echo (int) ($coursePublishSummary['theories'] ?? 0); ?> teorias</span>
                                        <span><i class="bi bi-lightning"></i> <?php echo (int) ($coursePublishSummary['activities'] ?? 0); ?> actividades</span>
                                    </div>
                                    <div class="small text-muted mt-2"><?php echo htmlspecialchars($catalogStatus['hint']); ?></div>
                                </div>

                                <div class="publish-checklist-grid">
                                    <?php foreach (($coursePublishChecklist ?? []) as $item): ?>
                                        <article class="publish-check-card <?php echo !empty($item['ok']) ? 'is-ready' : 'is-missing'; ?>">
                                            <div class="publish-check-head">
                                                <div class="publish-check-title"><?php echo htmlspecialchars($item['label']); ?></div>
                                                <span class="soft-badge"><?php echo !empty($item['ok']) ? 'OK' : 'Falta'; ?></span>
                                            </div>
                                            <div class="publish-check-copy"><?php echo htmlspecialchars($item['hint']); ?></div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <div id="courseVisibilityHint" class="alert context-note mt-3 mb-0" <?php echo !empty($curso->es_publico) && (int) ($coursePublishSummary['percentage'] ?? 0) < 100 ? '' : 'hidden'; ?>>
                                    El curso esta marcado para catalogo, pero todavia no quedara bien expuesto hasta cerrar los huecos editoriales principales.
                                </div>
                            </div>
                        </details>

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
                                    <label for="idioma_objetivo" class="form-label">Idioma objetivo *</label>
                                    <select class="form-select" id="idioma_objetivo" name="idioma_objetivo" required>
                                        <?php foreach (app_course_target_languages() as $languageValue => $languageLabel): ?>
                                            <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo ($curso->idioma_objetivo ?? $curso->idioma) == $languageValue ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($languageLabel); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="idioma_base" class="form-label">Idioma base del estudiante *</label>
                                    <select class="form-select" id="idioma_base" name="idioma_base" required>
                                        <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                                            <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo (Curso::obtenerIdiomaBase($curso) === $languageValue) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($languageLabel); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Idioma desde el cual se explica y contextualiza el curso.</div>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label for="nivel_cefr" class="form-label">Nivel principal *</label>
                                    <select class="form-select" id="nivel_cefr" name="nivel_cefr" required>
                                        <option value="A1" <?php echo $curso->nivel_cefr == 'A1' ? 'selected' : ''; ?>>A1 - Principiante</option>
                                        <option value="A2" <?php echo $curso->nivel_cefr == 'A2' ? 'selected' : ''; ?>>A2 - Elemental</option>
                                        <option value="B1" <?php echo $curso->nivel_cefr == 'B1' ? 'selected' : ''; ?>>B1 - Intermedio bajo</option>
                                        <option value="B2" <?php echo $curso->nivel_cefr == 'B2' ? 'selected' : ''; ?>>B2 - Intermedio alto</option>
                                        <option value="C1" <?php echo $curso->nivel_cefr == 'C1' ? 'selected' : ''; ?>>C1 - Avanzado</option>
                                        <option value="C2" <?php echo $curso->nivel_cefr == 'C2' ? 'selected' : ''; ?>>C2 - Dominio</option>
                                    </select>
                                    <div class="form-text">Mantiene compatibilidad y representa el foco o punto de entrada principal.</div>
                                </div>
                                <div class="col-md-3">
                                    <?php $nivelDesde = $curso->nivel_cefr_desde ?? $curso->nivel_cefr; ?>
                                    <label for="nivel_cefr_desde" class="form-label">Rango desde *</label>
                                    <select class="form-select" id="nivel_cefr_desde" name="nivel_cefr_desde" required>
                                        <option value="A1" <?php echo $nivelDesde == 'A1' ? 'selected' : ''; ?>>A1</option>
                                        <option value="A2" <?php echo $nivelDesde == 'A2' ? 'selected' : ''; ?>>A2</option>
                                        <option value="B1" <?php echo $nivelDesde == 'B1' ? 'selected' : ''; ?>>B1</option>
                                        <option value="B2" <?php echo $nivelDesde == 'B2' ? 'selected' : ''; ?>>B2</option>
                                        <option value="C1" <?php echo $nivelDesde == 'C1' ? 'selected' : ''; ?>>C1</option>
                                        <option value="C2" <?php echo $nivelDesde == 'C2' ? 'selected' : ''; ?>>C2</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <?php $nivelHasta = $curso->nivel_cefr_hasta ?? $curso->nivel_cefr; ?>
                                    <label for="nivel_cefr_hasta" class="form-label">Rango hasta *</label>
                                    <select class="form-select" id="nivel_cefr_hasta" name="nivel_cefr_hasta" required>
                                        <option value="A1" <?php echo $nivelHasta == 'A1' ? 'selected' : ''; ?>>A1</option>
                                        <option value="A2" <?php echo $nivelHasta == 'A2' ? 'selected' : ''; ?>>A2</option>
                                        <option value="B1" <?php echo $nivelHasta == 'B1' ? 'selected' : ''; ?>>B1</option>
                                        <option value="B2" <?php echo $nivelHasta == 'B2' ? 'selected' : ''; ?>>B2</option>
                                        <option value="C1" <?php echo $nivelHasta == 'C1' ? 'selected' : ''; ?>>C1</option>
                                        <option value="C2" <?php echo $nivelHasta == 'C2' ? 'selected' : ''; ?>>C2</option>
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
                                                <?php echo ((int) ($curso->portada_media_id ?? 0) === (int) $recurso->id) ? 'selected' : ''; ?>
                                            >
                                                <?php echo htmlspecialchars($recurso->titulo); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Selecciona una imagen de la biblioteca para reforzar la identidad del curso.</div>
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
                                <h2 class="form-section-title">Workflow editorial y acceso</h2>
                                <span class="soft-badge"><i class="bi bi-shield-lock"></i> Publicacion</span>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-lg-7">
                                    <label for="estado_editorial" class="form-label">Estado editorial *</label>
                                    <select class="form-select" id="estado_editorial" name="estado_editorial" required>
                                        <?php foreach ($courseEditorialStates as $stateValue => $stateMeta): ?>
                                            <option value="<?php echo htmlspecialchars($stateValue); ?>" <?php echo app_course_editorial_state_value($curso) === $stateValue ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($stateMeta['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Mantiene separado lo que esta listo editorialmente de lo que ya esta visible para alumnos.</div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="alert context-note h-100 mb-0" id="courseEditorialCard">
                                        <div class="fw-semibold" id="courseEditorialTitle"><?php echo htmlspecialchars($courseEditorialMeta['label']); ?></div>
                                        <div class="small text-muted mt-1" id="courseEditorialDescription"><?php echo htmlspecialchars($courseEditorialMeta['description']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="es_publico" name="es_publico" <?php echo $curso->es_publico ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="es_publico">Marcar para catalogo</label>
                                <div class="form-text">La visibilidad real para estudiantes solo se sostiene cuando el workflow queda en <strong>Publicado</strong> y existe al menos una leccion publicada.</div>
                            </div>
                            <div class="small text-muted mt-2" id="courseCatalogOutcomeCopy"><?php echo htmlspecialchars($catalogStatus['hint']); ?></div>
                            <div class="mt-2"><span class="soft-badge <?php echo htmlspecialchars($catalogStatus['tone']); ?>" id="courseCatalogOutcomeBadge"><?php echo htmlspecialchars($catalogStatus['short_label']); ?></span></div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requiere_codigo" name="requiere_codigo" <?php echo $curso->requiere_codigo ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="requiere_codigo">Requiere codigo de acceso</label>
                            </div>

                            <div class="alert context-note mt-3 mb-0" id="courseEditorialWorkflowHint" hidden>
                                <i class="bi bi-exclamation-triangle"></i>
                                Mientras el curso no quede en <strong>Publicado</strong> o no tenga una leccion publicada, seguira fuera de la vista del estudiante aunque marques la casilla.
                            </div>

                            <div id="codigo_acceso_div" class="mt-3 <?php echo $curso->requiere_codigo ? '' : 'is-hidden'; ?>">
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

const courseVisibilityCheckbox = document.getElementById('es_publico');
const courseVisibilityHint = document.getElementById('courseVisibilityHint');
const courseEditorialSelect = document.getElementById('estado_editorial');
const courseEditorialWorkflowHint = document.getElementById('courseEditorialWorkflowHint');
const courseEditorialTitle = document.getElementById('courseEditorialTitle');
const courseEditorialDescription = document.getElementById('courseEditorialDescription');
const courseCatalogOutcomeBadge = document.getElementById('courseCatalogOutcomeBadge');
const courseCatalogOutcomeCopy = document.getElementById('courseCatalogOutcomeCopy');
const courseEditorialStates = <?php echo json_encode($courseEditorialStates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const coursePublishPercentage = <?php echo (int) ($coursePublishSummary['percentage'] ?? 0); ?>;
const coursePublishedLessons = <?php echo $publishedLessons; ?>;

function syncCourseVisibilityHint() {
    if (!courseVisibilityCheckbox || !courseVisibilityHint) {
        return;
    }

    courseVisibilityHint.hidden = !(courseVisibilityCheckbox.checked && coursePublishPercentage < 100);
    courseEditorialWorkflowHint.hidden = !(courseVisibilityCheckbox.checked && courseEditorialSelect.value !== 'publicado');

    let label = 'Oculto';
    let tone = '';
    let hint = 'No se muestra en catalogo y no esta listo para abrirse.';

    if (courseVisibilityCheckbox.checked && courseEditorialSelect.value === 'publicado' && coursePublishedLessons > 0) {
        label = 'Visible';
        tone = 'badge-accent';
        hint = 'Visible en catalogo y disponible para estudiantes.';
    } else if (courseVisibilityCheckbox.checked && courseEditorialSelect.value === 'publicado') {
        label = 'En espera';
        tone = 'warning';
        hint = 'Ya esta marcado para catalogo, pero aun necesita al menos una leccion publicada.';
    } else if (courseVisibilityCheckbox.checked) {
        label = 'En espera';
        tone = 'info';
        hint = 'Sigue fuera del catalogo hasta que el workflow editorial llegue a Publicado.';
    }

    if (courseCatalogOutcomeBadge) {
        courseCatalogOutcomeBadge.className = 'soft-badge' + (tone ? ' ' + tone : '');
        courseCatalogOutcomeBadge.textContent = label;
    }

    if (courseCatalogOutcomeCopy) {
        courseCatalogOutcomeCopy.textContent = hint;
    }
}

if (courseVisibilityCheckbox) {
    courseVisibilityCheckbox.addEventListener('change', syncCourseVisibilityHint);
    syncCourseVisibilityHint();
}

if (courseEditorialSelect) {
    courseEditorialSelect.addEventListener('change', function () {
        const current = courseEditorialStates[courseEditorialSelect.value] || courseEditorialStates.borrador;
        courseEditorialTitle.textContent = current.label || 'Borrador';
        courseEditorialDescription.textContent = current.description || '';
        syncCourseVisibilityHint();
    });
}

document.getElementById('generar_codigo').addEventListener('click', function() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let codigo = '';
    for (let i = 0; i < 8; i++) {
        codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    document.getElementById('codigo_acceso').value = codigo;
});

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
const courseEditForm = document.getElementById('formEditarCurso');

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

if (courseEditForm) {
    courseEditForm.addEventListener('submit', function (event) {
        if (courseVisibilityCheckbox && courseVisibilityCheckbox.checked && courseEditorialSelect.value !== 'publicado') {
            const shouldContinue = window.confirm('El curso seguira fuera del catalogo mientras no quede en Publicado y no tenga una leccion publicada. ¿Quieres guardarlo asi de todos modos?');
            if (!shouldContinue) {
                event.preventDefault();
            }
            return;
        }

        if (courseVisibilityCheckbox && courseVisibilityCheckbox.checked && coursePublishPercentage < 100) {
            const shouldContinue = window.confirm('Este curso sigue marcado para catalogo, pero todavia necesita al menos una leccion publicada y menos huecos editoriales. ¿Quieres guardarlo asi de todos modos?');
            if (!shouldContinue) {
                event.preventDefault();
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>

