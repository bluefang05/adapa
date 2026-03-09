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
        <span class="eyebrow"><i class="bi bi-headphones"></i> Configurador</span>
        <h1 class="page-title">Actividad de escucha</h1>
        <p class="page-subtitle">
            Vincula el audio, añade transcripcion opcional y construye preguntas de comprension para el estudiante.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/escucha/' . $leccion->id)) . '&context=actividad_escucha'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir audio en biblioteca
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-headphones"></i> Configurador</span>
            <span class="soft-badge"><i class="bi bi-soundwave"></i> Audio y comprension</span>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="alert context-note">
        Puedes usar una URL directa de audio. La subida de archivos todavia no esta integrada en esta pantalla.
    </div>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert context-note">
            <strong>Recurso listo para escuchar:</strong> <?php echo htmlspecialchars((string) ($_GET['selected_media_title'] ?? 'Recurso seleccionado')); ?>.
            Si es audio o video, la URL se colocara automaticamente.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" id="config-form" enctype="multipart/form-data">
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

                            <div class="mb-3">
                                <label for="audio_url" class="form-label">URL del audio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-link"></i></span>
                                    <input type="text" class="form-control" id="audio_url" placeholder="https://ejemplo.com/audio.mp3">
                                </div>
                                <div class="form-text">Introduce una URL directa al archivo de audio.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Transcripcion opcional</label>
                                <textarea class="form-control" id="transcripcion" rows="4" placeholder="Texto del audio..."></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Preguntas de comprension</h2>
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
                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="escucha">

                        <div class="responsive-actions mt-4">
                            <a href="<?= url('/profesor/lecciones/' . $leccion->id . '/actividades') ?>" class="btn btn-outline-secondary">Volver</a>
                            <button type="submit" class="btn btn-primary">Guardar actividad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="pregunta-template">
    <div class="pregunta-item">
        <div class="split-head mb-3">
            <h6 class="mb-0">Pregunta <span class="numero-pregunta"></span></h6>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarPregunta(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="mb-3">
            <input type="text" class="form-control pregunta-texto" placeholder="Escribe la pregunta..." required>
        </div>
        <div class="opciones-container config-builder">
            <label class="form-label small">Opciones (marca la correcta)</label>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-3" onclick="agregarOpcion(this)">Anadir opcion</button>
    </div>
</template>

<template id="opcion-template">
    <div class="option-item">
        <div class="input-group">
            <div class="input-group-text">
                <input class="form-check-input mt-0 opcion-correcta" type="radio" name="" aria-label="Seleccionar respuesta correcta">
            </div>
            <input type="text" class="form-control opcion-texto" placeholder="Opcion...">
            <button class="btn btn-outline-secondary" type="button" onclick="eliminarOpcion(this)">X</button>
        </div>
    </div>
</template>

<script>
    const form = document.getElementById('config-form');
    const preguntasContainer = document.getElementById('preguntas-container');
    const preguntaTemplate = document.getElementById('pregunta-template');
    const opcionTemplate = document.getElementById('opcion-template');
    const selectedMediaParams = new URLSearchParams(window.location.search);

    function agregarPregunta(datos = null) {
        const clone = preguntaTemplate.content.cloneNode(true);
        const preguntaEl = clone.querySelector('.pregunta-item');
        const opcionesContainer = clone.querySelector('.opciones-container');
        const radioName = 'radio_' + Date.now() + Math.random().toString(36).substr(2, 9);
        
        if (datos) {
            preguntaEl.querySelector('.pregunta-texto').value = datos.pregunta;
            datos.opciones.forEach(op => {
                agregarOpcionToContainer(opcionesContainer, radioName, op);
            });
        } else {
            agregarOpcionToContainer(opcionesContainer, radioName);
            agregarOpcionToContainer(opcionesContainer, radioName);
        }

        preguntasContainer.appendChild(clone);
        actualizarNumerosPreguntas();
    }

    function agregarOpcion(btn) {
        const container = btn.previousElementSibling;
        const existingRadio = container.querySelector('input[type="radio"]');
        const radioName = existingRadio ? existingRadio.name : 'radio_' + Date.now();
        agregarOpcionToContainer(container, radioName);
    }

    function agregarOpcionToContainer(container, radioName, datos = null) {
        const clone = opcionTemplate.content.cloneNode(true);
        const radio = clone.querySelector('.opcion-correcta');
        const input = clone.querySelector('.opcion-texto');
        
        radio.name = radioName;
        
        if (datos) {
            input.value = datos.texto;
            radio.checked = datos.correcta;
        }
        
        container.appendChild(clone);
    }

    function eliminarPregunta(btn) {
        btn.closest('.pregunta-item').remove();
        actualizarNumerosPreguntas();
    }

    function eliminarOpcion(btn) {
        const container = btn.closest('.opciones-container');
        if (container && container.querySelectorAll('.option-item').length > 2) {
            btn.closest('.option-item').remove();
        } else {
            alert('Debe haber al menos 2 opciones.');
        }
    }

    function actualizarNumerosPreguntas() {
        document.querySelectorAll('.numero-pregunta').forEach((span, index) => {
            span.textContent = index + 1;
        });
    }

    window.addEventListener('DOMContentLoaded', () => {
        const selectedMediaUrl = selectedMediaParams.get('selected_media_url');
        const selectedMediaType = selectedMediaParams.get('selected_media_type');
        try {
            const contenido = <?php 
                $c = $_SESSION['actividad_temp']['contenido'] ?? '{}';
                echo is_string($c) ? $c : json_encode($c); 
            ?>;
            
            if (contenido.audio_url) {
                document.getElementById('audio_url').value = contenido.audio_url;
            }

            if (!contenido.audio_url && selectedMediaUrl && (selectedMediaType === 'audio' || selectedMediaType === 'video')) {
                document.getElementById('audio_url').value = selectedMediaUrl;
            }
            
            if (contenido.transcripcion) {
                document.getElementById('transcripcion').value = contenido.transcripcion;
            }

            if (contenido.preguntas && Array.isArray(contenido.preguntas) && contenido.preguntas.length > 0) {
                contenido.preguntas.forEach(p => agregarPregunta(p));
            } else {
                agregarPregunta();
            }
        } catch(e) { 
            console.error(e);
            if (selectedMediaUrl && (selectedMediaType === 'audio' || selectedMediaType === 'video')) {
                document.getElementById('audio_url').value = selectedMediaUrl;
            }
            agregarPregunta();
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const preguntas = [];
        const items = document.querySelectorAll('.pregunta-item');
        let valid = true;

        items.forEach(item => {
            const textoPregunta = item.querySelector('.pregunta-texto').value.trim();
            const opcionesEls = item.querySelectorAll('.option-item');
            const opciones = [];
            let tieneCorrecta = false;

            opcionesEls.forEach(op => {
                const textoOp = op.querySelector('.opcion-texto').value.trim();
                const esCorrecta = op.querySelector('.opcion-correcta').checked;
                
                if (esCorrecta) tieneCorrecta = true;
                if (!textoOp) valid = false;
                
                opciones.push({
                    texto: textoOp,
                    correcta: esCorrecta
                });
            });

            if (!textoPregunta || !tieneCorrecta) {
                valid = false;
            }
            
            preguntas.push({
                pregunta: textoPregunta,
                opciones: opciones
            });
        });

        if (!valid) {
            alert('Asegurate de completar todas las preguntas y marcar una respuesta correcta en cada una.');
            return;
        }

        const contenido = {
            audio_url: document.getElementById('audio_url').value,
            transcripcion: document.getElementById('transcripcion').value,
            preguntas: preguntas
        };
        
        document.getElementById('contenido').value = JSON.stringify(contenido);
        form.submit();
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
