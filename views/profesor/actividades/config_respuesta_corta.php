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

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-keyboard"></i> Configurador</span>
        <h1 class="page-title">Actividad de respuesta corta</h1>
        <p class="page-subtitle">
            Define instrucciones, placeholders y respuestas correctas para una o varias preguntas breves.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/respuesta_corta/' . $leccion->id)) . '&context=actividad_respuesta_corta'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir recurso de apoyo
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-keyboard"></i> Configurador</span>
            <span class="soft-badge"><i class="bi bi-chat-left-text"></i> Respuestas breves</span>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert context-note">
            <strong>Recurso de apoyo listo:</strong> <?php echo htmlspecialchars((string) ($_GET['selected_media_title'] ?? 'Recurso seleccionado')); ?>.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" id="config-form">
                        <?php echo csrf_input(); ?>
                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Ficha general</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> <?php echo htmlspecialchars($leccion->titulo); ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($_SESSION['actividad_temp']['titulo'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required><?= htmlspecialchars($_SESSION['actividad_temp']['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="pregunta_global" class="form-label">Instruccion global opcional</label>
                                <input type="text" class="form-control" id="pregunta_global" name="pregunta_global" placeholder="Ejemplo: responde a las siguientes preguntas">
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Preguntas y respuestas</h2>
                                <span class="soft-badge"><i class="bi bi-diagram-3"></i> Builder</span>
                            </div>

                            <div id="preguntas-container" class="config-builder mb-4"></div>

                            <div class="builder-toolbar mb-4">
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
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?= htmlspecialchars($_SESSION['actividad_temp']['orden'] ?? '1') ?>" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="puntos_maximos" class="form-label">Puntos maximos</label>
                                    <input type="number" class="form-control" id="puntos_maximos" name="puntos_maximos" value="<?= htmlspecialchars($_SESSION['actividad_temp']['puntos_maximos'] ?? '10') ?>" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="tiempo_limite_minutos" class="form-label">Tiempo limite en minutos</label>
                                    <input type="number" class="form-control" id="tiempo_limite_minutos" name="tiempo_limite_minutos" value="<?= htmlspecialchars($_SESSION['actividad_temp']['tiempo_limite_minutos'] ?? '5') ?>" min="1" required>
                                </div>
                            </div>
                        </section>

                        <input type="hidden" id="contenido" name="contenido">
                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="respuesta_corta">

                        <div class="responsive-actions mt-4">
                            <a href="<?= url('/profesor/lecciones/' . $leccion->id . '/actividades') ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar actividad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let preguntasCount = 0;
    const selectedMediaParams = new URLSearchParams(window.location.search);

    function agregarPregunta(datos = null) {
        preguntasCount++;
        const container = document.getElementById('preguntas-container');
        const preguntaDiv = document.createElement('div');
        preguntaDiv.className = 'pregunta-item';
        preguntaDiv.dataset.id = preguntasCount;
        
        const textoValue = datos ? (datos.texto || datos.pregunta || '') : '';
        const placeholderValue = datos ? (datos.placeholder || 'Escribe aqui tu respuesta...') : 'Escribe aqui tu respuesta...';

        preguntaDiv.innerHTML = `
            <div class="split-head mb-3">
                <div>
                    <h3 class="h5 mb-1">Pregunta ${preguntasCount}</h3>
                    <div class="small text-muted">Define la consigna y una o varias respuestas correctas.</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarPregunta(${preguntasCount})">Eliminar pregunta</button>
            </div>
            <div class="mb-3">
                <label class="form-label">Texto de la pregunta</label>
                <textarea class="form-control pregunta-texto" rows="2" placeholder="Cual es la capital de Francia?" required>${textoValue}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Placeholder</label>
                <input type="text" class="form-control pregunta-placeholder" value="${placeholderValue}" placeholder="Escribe aqui tu respuesta...">
            </div>

            <div class="mb-3">
                <label class="form-label">Respuestas correctas</label>
                <div class="respuestas-container config-builder" id="respuestas-container-${preguntasCount}"></div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-3" onclick="agregarRespuesta(${preguntasCount})">Anadir respuesta correcta</button>
            </div>
        `;
        
        container.appendChild(preguntaDiv);

        if (datos && datos.respuestas_correctas) {
            datos.respuestas_correctas.forEach(respuesta => {
                agregarRespuesta(preguntasCount, respuesta);
            });
        } else {
            agregarRespuesta(preguntasCount);
        }
    }

    function eliminarPregunta(id) {
        const element = document.querySelector(`.pregunta-item[data-id="${id}"]`);
        if (element) {
            element.remove();
            actualizarNumeracion();
        }
    }

    function actualizarNumeracion() {
        const preguntas = document.querySelectorAll('.pregunta-item');
        preguntas.forEach((p, index) => {
            p.querySelector('h3').textContent = `Pregunta ${index + 1}`;
        });
    }

    function agregarRespuesta(preguntaId, valor = '') {
        const container = document.getElementById(`respuestas-container-${preguntaId}`);
        const div = document.createElement('div');
        div.className = 'option-item inline-field-actions';
        div.innerHTML = `
            <input type="text" class="form-control respuesta-input" value="${valor}" placeholder="Respuesta correcta" required>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()">Eliminar</button>
        `;
        container.appendChild(div);
    }

    document.getElementById('config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const preguntas = [];
        const preguntaItems = document.querySelectorAll('.pregunta-item');
        
        if (preguntaItems.length === 0) {
            alert('Debes anadir al menos una pregunta');
            return;
        }

        let valid = true;

        preguntaItems.forEach(item => {
            const texto = item.querySelector('.pregunta-texto').value.trim();
            const placeholder = item.querySelector('.pregunta-placeholder').value.trim();
            const respuestasInputs = item.querySelectorAll('.respuesta-input');
            const respuestas = [];
            
            respuestasInputs.forEach(input => {
                const val = input.value.trim();
                if (val) respuestas.push(val);
            });

            if (!texto) {
                alert('Todas las preguntas deben tener texto');
                valid = false;
                return;
            }

            if (respuestas.length === 0) {
                alert('Cada pregunta debe tener al menos una respuesta correcta');
                valid = false;
                return;
            }

            preguntas.push({
                texto: texto,
                placeholder: placeholder,
                respuestas_correctas: respuestas
            });
        });

        if (!valid) return;

        const contenido = {
            pregunta_global: document.getElementById('pregunta_global').value.trim(),
            preguntas: preguntas,
            recurso_apoyo_media_id: selectedMediaParams.get('selected_media_id') || '',
            recurso_apoyo_titulo: selectedMediaParams.get('selected_media_title') || '',
            recurso_apoyo_url: selectedMediaParams.get('selected_media_url') || '',
            recurso_apoyo_tipo: selectedMediaParams.get('selected_media_type') || ''
        };

        document.getElementById('contenido').value = JSON.stringify(contenido);
        this.submit();
    });

    window.addEventListener('DOMContentLoaded', function() {
        const contenidoExistente = <?php 
            $contenido = $_SESSION['actividad_temp']['contenido'] ?? '{}';
            echo is_string($contenido) ? $contenido : json_encode($contenido); 
        ?>;

        if (contenidoExistente.pregunta_global) {
            document.getElementById('pregunta_global').value = contenidoExistente.pregunta_global;
        }

        if (contenidoExistente.preguntas && Array.isArray(contenidoExistente.preguntas)) {
            contenidoExistente.preguntas.forEach(p => agregarPregunta(p));
        } else if (contenidoExistente.pregunta || contenidoExistente.respuestas_correctas) {
            agregarPregunta({
                texto: contenidoExistente.pregunta,
                placeholder: contenidoExistente.placeholder,
                respuestas_correctas: contenidoExistente.respuestas_correctas
            });
        } else {
            agregarPregunta();
        }
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
