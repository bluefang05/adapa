<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>">Actividades</a></li>
            <li class="breadcrumb-item active">Configurar actividad</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-ui-checks-grid"></i> Configurador</span>
        <h1 class="page-title">Actividad de seleccion multiple</h1>
        <p class="page-subtitle">
            Construye una o varias preguntas con opciones y define la respuesta correcta desde un flujo mas limpio.
        </p>
        <div class="hero-actions">
            <a href="<?= url('/profesor/lecciones/' . $leccion->id . '/actividades') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a actividades
            </a>
        </div>
    </section>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-shell">
                <div class="card-body">
                    <form id="config-form" method="POST">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Ficha general</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> <?php echo htmlspecialchars($leccion->titulo); ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required value="<?= htmlspecialchars($_SESSION['actividad_temp']['titulo'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Pregunta o contexto</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($_SESSION['actividad_temp']['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="pregunta" class="form-label">Instruccion global opcional</label>
                                <input type="text" class="form-control" id="pregunta" name="pregunta" placeholder="Ejemplo: selecciona la respuesta correcta en cada caso" value="">
                            </div>

                            <div class="alert alert-light border" role="alert">
                                <i class="bi bi-image"></i>
                                Puedes convertir cualquier pregunta en una actividad visual asociando una imagen desde tu biblioteca.
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Preguntas</h2>
                                <span class="soft-badge"><i class="bi bi-diagram-3"></i> Builder</span>
                            </div>

                            <div id="preguntas-container" class="config-builder"></div>
                            <div class="builder-toolbar mt-3">
                                <button type="button" class="btn btn-outline-primary" onclick="agregarPregunta()">
                                    <i class="bi bi-plus-lg"></i> Anadir pregunta
                                </button>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Parametros</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Evaluacion</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="tiempo_limite" class="form-label">Tiempo limite en minutos</label>
                                    <input type="number" class="form-control" id="tiempo_limite" name="tiempo_limite_minutos" min="1" max="60" value="<?= htmlspecialchars($_SESSION['actividad_temp']['tiempo_limite_minutos'] ?? '5') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="puntos" class="form-label">Puntos maximos</label>
                                    <input type="number" class="form-control" id="puntos" name="puntos_maximos" min="1" max="100" value="<?= htmlspecialchars($_SESSION['actividad_temp']['puntos_maximos'] ?? '10') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="1" value="<?= htmlspecialchars($_SESSION['actividad_temp']['orden'] ?? '1') ?>">
                                </div>
                            </div>
                        </section>

                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="opcion_multiple">
                        <input type="hidden" id="contenido" name="contenido" value="">

                        <div class="responsive-actions mt-4">
                            <a href="<?= url('/profesor/lecciones/' . $leccion->id . '/actividades') ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear actividad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let preguntasCount = 0;

    function agregarPregunta() {
        preguntasCount++;
        const container = document.getElementById('preguntas-container');

        const preguntaDiv = document.createElement('div');
        preguntaDiv.className = 'question-item';
        preguntaDiv.dataset.preguntaId = preguntasCount;

        preguntaDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                <div>
                    <h3 class="h5 mb-1">Pregunta ${preguntasCount}</h3>
                    <div class="small text-muted">Define el texto y sus opciones.</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger eliminar-pregunta">Eliminar pregunta</button>
            </div>
            <div class="mb-3">
                <label class="form-label">Texto de la pregunta</label>
                <input type="text" class="form-control pregunta-texto" placeholder="Texto de la pregunta">
            </div>
            <div class="mb-3">
                <label class="form-label">Imagen opcional</label>
                <select class="form-select pregunta-imagen">
                    <option value="">Sin imagen</option>
                    <?php foreach (($recursosImagen ?? []) as $recurso): ?>
                        <option
                            value="<?php echo (int) $recurso->id; ?>"
                            data-url="<?php echo htmlspecialchars(url('/' . ltrim($recurso->ruta_archivo, '/'))); ?>"
                            data-alt="<?php echo htmlspecialchars($recurso->alt_text ?: $recurso->titulo); ?>"
                        >
                            <?php echo htmlspecialchars($recurso->titulo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="block-media-preview is-empty mt-3 pregunta-media-preview">
                    <span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin imagen asociada</span>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Opciones de respuesta</label>
                <div class="opciones-container config-builder"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-3 agregar-opcion">Anadir opcion</button>
            </div>
        `;

        container.appendChild(preguntaDiv);
        preguntaDiv.dataset.nextOpcionIndex = '1';

        const agregarOpcionBtn = preguntaDiv.querySelector('.agregar-opcion');
        agregarOpcionBtn.addEventListener('click', function () {
            agregarOpcion(preguntaDiv.dataset.preguntaId);
        });

        const eliminarPreguntaBtn = preguntaDiv.querySelector('.eliminar-pregunta');
        eliminarPreguntaBtn.addEventListener('click', function () {
            preguntaDiv.remove();
        });

        const selectImagen = preguntaDiv.querySelector('.pregunta-imagen');
        selectImagen.addEventListener('change', function () {
            renderPreguntaPreview(preguntaDiv, this);
        });

        agregarOpcion(preguntaDiv.dataset.preguntaId);
        agregarOpcion(preguntaDiv.dataset.preguntaId);
    }

    function agregarOpcion(preguntaId) {
        const preguntaDiv = document.querySelector('.question-item[data-pregunta-id="' + preguntaId + '"]');
        if (!preguntaDiv) return;

        const container = preguntaDiv.querySelector('.opciones-container');
        const nextIndex = parseInt(preguntaDiv.dataset.nextOpcionIndex || '1', 10);

        const opcionDiv = document.createElement('div');
        opcionDiv.className = 'option-item';

        opcionDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                <label class="form-label mb-0">Opcion ${nextIndex}</label>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Eliminar</button>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control opcion-texto" placeholder="Texto de la opcion">
            </div>
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input opcion-correcta">
                <label class="form-check-label">Esta es la respuesta correcta</label>
            </div>
        `;

        container.appendChild(opcionDiv);

        const checkbox = opcionDiv.querySelector('.opcion-correcta');
        checkbox.addEventListener('change', function () {
            if (this.checked) {
                container.querySelectorAll('.opcion-correcta').forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });
                container.querySelectorAll('.option-item').forEach(item => {
                    item.classList.remove('correct');
                });
                opcionDiv.classList.add('correct');
            } else {
                opcionDiv.classList.remove('correct');
            }
        });

        const eliminarBtn = opcionDiv.querySelector('.btn-remove');
        eliminarBtn.addEventListener('click', function () {
            opcionDiv.remove();
        });

        preguntaDiv.dataset.nextOpcionIndex = String(nextIndex + 1);
    }

    window.addEventListener('DOMContentLoaded', function () {
        const contenidoExistente = <?php 
            $contenido = $_SESSION['actividad_temp']['contenido'] ?? '{}';
            echo is_string($contenido) ? $contenido : json_encode($contenido); 
        ?>;
        
        if (contenidoExistente.pregunta_global || contenidoExistente.pregunta) {
            document.getElementById('pregunta').value = contenidoExistente.pregunta_global || contenidoExistente.pregunta;
        }

        if (contenidoExistente.preguntas && Array.isArray(contenidoExistente.preguntas) && contenidoExistente.preguntas.length > 0) {
            contenidoExistente.preguntas.forEach(pregunta => {
                cargarPreguntaExistente(pregunta);
            });
        } else if (contenidoExistente.opciones && Array.isArray(contenidoExistente.opciones)) {
            cargarPreguntaExistente({
                texto: contenidoExistente.pregunta || '',
                opciones: contenidoExistente.opciones
            });
        } else {
            agregarPregunta();
        }
    });

    function cargarPreguntaExistente(datosPregunta) {
        preguntasCount++;
        const container = document.getElementById('preguntas-container');

        const preguntaDiv = document.createElement('div');
        preguntaDiv.className = 'question-item';
        preguntaDiv.dataset.preguntaId = preguntasCount;

        preguntaDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                <div>
                    <h3 class="h5 mb-1">Pregunta ${preguntasCount}</h3>
                    <div class="small text-muted">Define el texto y sus opciones.</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger eliminar-pregunta">Eliminar pregunta</button>
            </div>
            <div class="mb-3">
                <label class="form-label">Texto de la pregunta</label>
                <input type="text" class="form-control pregunta-texto" placeholder="Texto de la pregunta" value="${datosPregunta.texto || ''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Imagen opcional</label>
                <select class="form-select pregunta-imagen">
                    <option value="">Sin imagen</option>
                    <?php foreach (($recursosImagen ?? []) as $recurso): ?>
                        <option
                            value="<?php echo (int) $recurso->id; ?>"
                            data-url="<?php echo htmlspecialchars(url('/' . ltrim($recurso->ruta_archivo, '/'))); ?>"
                            data-alt="<?php echo htmlspecialchars($recurso->alt_text ?: $recurso->titulo); ?>"
                        >
                            <?php echo htmlspecialchars($recurso->titulo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="block-media-preview is-empty mt-3 pregunta-media-preview">
                    <span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin imagen asociada</span>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Opciones de respuesta</label>
                <div class="opciones-container config-builder"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-3 agregar-opcion">Anadir opcion</button>
            </div>
        `;

        container.appendChild(preguntaDiv);
        preguntaDiv.dataset.nextOpcionIndex = '1';

        const agregarOpcionBtn = preguntaDiv.querySelector('.agregar-opcion');
        agregarOpcionBtn.addEventListener('click', function () {
            agregarOpcion(preguntaDiv.dataset.preguntaId);
        });

        const eliminarPreguntaBtn = preguntaDiv.querySelector('.eliminar-pregunta');
        eliminarPreguntaBtn.addEventListener('click', function () {
            preguntaDiv.remove();
        });

        const selectImagen = preguntaDiv.querySelector('.pregunta-imagen');
        if (datosPregunta.image_media_id) {
            selectImagen.value = String(datosPregunta.image_media_id);
        }
        renderPreguntaPreview(preguntaDiv, selectImagen);
        selectImagen.addEventListener('change', function () {
            renderPreguntaPreview(preguntaDiv, this);
        });

        if (datosPregunta.opciones && Array.isArray(datosPregunta.opciones)) {
            datosPregunta.opciones.forEach(opcion => {
                cargarOpcionExistente(preguntaDiv.dataset.preguntaId, opcion);
            });
        } else {
            agregarOpcion(preguntaDiv.dataset.preguntaId);
            agregarOpcion(preguntaDiv.dataset.preguntaId);
        }
    }

    function cargarOpcionExistente(preguntaId, datosOpcion) {
        const preguntaDiv = document.querySelector('.question-item[data-pregunta-id="' + preguntaId + '"]');
        if (!preguntaDiv) return;

        const container = preguntaDiv.querySelector('.opciones-container');
        const nextIndex = parseInt(preguntaDiv.dataset.nextOpcionIndex || '1', 10);

        const opcionDiv = document.createElement('div');
        opcionDiv.className = 'option-item';
        if (datosOpcion.es_correcta) {
            opcionDiv.classList.add('correct');
        }

        opcionDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                <label class="form-label mb-0">Opcion ${nextIndex}</label>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Eliminar</button>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control opcion-texto" placeholder="Texto de la opcion" value="${datosOpcion.texto || ''}">
            </div>
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input opcion-correcta" ${datosOpcion.es_correcta ? 'checked' : ''}>
                <label class="form-check-label">Esta es la respuesta correcta</label>
            </div>
        `;

        container.appendChild(opcionDiv);

        const checkbox = opcionDiv.querySelector('.opcion-correcta');
        checkbox.addEventListener('change', function () {
            if (this.checked) {
                container.querySelectorAll('.opcion-correcta').forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });
                container.querySelectorAll('.option-item').forEach(item => {
                    item.classList.remove('correct');
                });
                opcionDiv.classList.add('correct');
            } else {
                opcionDiv.classList.remove('correct');
            }
        });

        const eliminarBtn = opcionDiv.querySelector('.btn-remove');
        eliminarBtn.addEventListener('click', function () {
            opcionDiv.remove();
        });

        preguntaDiv.dataset.nextOpcionIndex = String(nextIndex + 1);
    }

    function renderPreguntaPreview(preguntaDiv, select) {
        const preview = preguntaDiv.querySelector('.pregunta-media-preview');
        if (!preview || !select) {
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        const imageUrl = selectedOption ? selectedOption.dataset.url : '';
        const imageAlt = selectedOption ? selectedOption.dataset.alt : '';

        if (!imageUrl) {
            preview.classList.add('is-empty');
            preview.innerHTML = '<span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin imagen asociada</span>';
            return;
        }

        preview.classList.remove('is-empty');
        preview.innerHTML = `<img src="${imageUrl}" alt="${imageAlt || 'Imagen de la pregunta'}" class="block-media-thumb">`;
    }

    document.getElementById('config-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const preguntas = [];

        document.querySelectorAll('.question-item').forEach((preguntaDiv, index) => {
            const textoInput = preguntaDiv.querySelector('.pregunta-texto');
            const textoPregunta = textoInput ? textoInput.value : '';
            const opciones = [];

            preguntaDiv.querySelectorAll('.option-item').forEach(opcionDiv => {
                const texto = opcionDiv.querySelector('.opcion-texto').value;
                const esCorrecta = opcionDiv.querySelector('.opcion-correcta').checked;

                if (texto) {
                    opciones.push({
                        texto: texto,
                        es_correcta: esCorrecta
                    });
                }
            });

            if (opciones.length) {
                preguntas.push({
                    texto: textoPregunta || 'Pregunta ' + (preguntas.length + 1),
                    image_media_id: preguntaDiv.querySelector('.pregunta-imagen')?.value || null,
                    image_url: preguntaDiv.querySelector('.pregunta-imagen option:checked')?.dataset?.url || null,
                    image_alt: preguntaDiv.querySelector('.pregunta-imagen option:checked')?.dataset?.alt || null,
                    opciones: opciones
                });
            }
        });

        if (!preguntas.length) {
            alert('Debes anadir al menos una pregunta con opciones');
            return;
        }

        for (const pregunta of preguntas) {
            if (pregunta.opciones.length < 2) {
                alert('Cada pregunta debe tener al menos 2 opciones');
                return;
            }
            if (!pregunta.opciones.some(op => op.es_correcta)) {
                alert('Cada pregunta debe tener al menos una respuesta correcta');
                return;
            }
        }

        const contenido = {
            pregunta_global: document.getElementById('pregunta').value || 'Selecciona la respuesta correcta:',
            preguntas: preguntas
        };

        document.getElementById('contenido').value = JSON.stringify(contenido);

        this.submit();
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
