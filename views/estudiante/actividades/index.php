<?php
require_once __DIR__ . '/../../partials/header.php';

function previewActivityTypeLabel($tipo) {
    $labels = [
        'opcion_multiple' => 'Opcion multiple',
        'verdadero_falso' => 'Verdadero/Falso',
        'completar_oracion' => 'Completar oracion',
        'emparejamiento' => 'Emparejamiento',
        'ordenar_palabras' => 'Ordenar palabras',
        'pronunciacion' => 'Pronunciacion',
        'escritura' => 'Escritura',
        'escucha' => 'Escucha',
        'arrastrar_soltar' => 'Arrastrar y soltar',
        'respuesta_corta' => 'Respuesta corta'
    ];

    return $labels[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
}

function renderPreviewSupportResource($resource) {
    if (empty($resource['url'])) {
        return;
    }

    $title = htmlspecialchars($resource['title'] ?? 'Recurso de apoyo');
    $url = htmlspecialchars($resource['url']);
    $kind = $resource['kind'] ?? 'link';
    $kindLabels = [
        'video' => 'Video de apoyo',
        'audio' => 'Audio de apoyo',
        'image' => 'Imagen de apoyo',
        'pdf' => 'Documento de apoyo',
        'link' => 'Enlace de apoyo',
    ];
    $kindLabel = $kindLabels[$kind] ?? 'Recurso de apoyo';
    $sourceLabel = app_url_host_label($resource['url'] ?? '');
    ?>
    <section class="support-resource-panel mb-4">
        <div class="support-resource-header">
            <div>
                <div class="support-resource-eyebrow"><?php echo htmlspecialchars($kindLabel); ?></div>
                <h3 class="support-resource-title"><?php echo $title; ?></h3>
                <div class="small text-muted mt-1">Fuente: <?php echo htmlspecialchars($sourceLabel); ?></div>
            </div>
            <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">Abrir recurso</a>
        </div>
        <?php if ($kind === 'video' && !empty($resource['embed_url'])): ?>
            <div class="<?php echo htmlspecialchars($resource['frame_class'] ?? 'media-embed-frame'); ?>">
                <iframe
                    src="<?php echo htmlspecialchars($resource['embed_url']); ?>"
                    title="<?php echo $title; ?>"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen>
                </iframe>
            </div>
        <?php elseif ($kind === 'audio'): ?>
            <audio controls class="w-100 media-preview-player">
                <source src="<?php echo $url; ?>">
                Tu navegador no soporta audio embebido.
            </audio>
        <?php elseif ($kind === 'image'): ?>
            <img src="<?php echo $url; ?>" alt="<?php echo $title; ?>" class="img-fluid rounded-4 border activity-question-image">
        <?php else: ?>
            <div class="support-resource-fallback">Este recurso se mostrara al alumno antes de responder.</div>
        <?php endif; ?>
    </section>
    <?php
}

$isPreviewUser = Auth::getUserRole() === 'profesor' || Auth::getUserRole() === 'admin';
$backUrl = $isPreviewUser ? url('/profesor/lecciones/' . $actividad->leccion_id . '/actividades') : url('/estudiante');
$backLabel = $isPreviewUser ? 'Volver a actividades' : 'Volver al panel';
$supportResource = app_activity_support_resource($actividad->contenido ?? null);
?>



<div class="container activity-player-page">
    <div class="activity-container activity-player">
        <section class="page-hero mb-4">
            <span class="eyebrow"><i class="bi bi-lightning-charge"></i> <?php echo $isPreviewUser ? 'Vista previa interactiva' : 'Actividad interactiva'; ?></span>
            <h1 class="page-title"><?php echo htmlspecialchars($actividad->titulo); ?></h1>
            <p class="page-subtitle"><?php echo htmlspecialchars($actividad->descripcion ?: 'Resuelve la actividad y recibe retroalimentacion inmediata.'); ?></p>
            <?php if ($isPreviewUser): ?>
                <p class="text-muted mb-0">Esta vista ayuda a comprobar interaccion, feedback y orden de preguntas antes de que la vea el alumno.</p>
            <?php endif; ?>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Tipo</div>
                    <div class="metric-value"><?php echo htmlspecialchars(previewActivityTypeLabel($actividad->tipo_actividad)); ?></div>
                    <div class="metric-note">Formato configurado para esta practica.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Secuencia</div>
                    <div class="metric-value"><?php echo isset($siguienteActividad) && $siguienteActividad ? 'Con siguiente paso' : 'Ultima actividad'; ?></div>
                    <div class="metric-note"><?php echo isset($siguienteActividad) && $siguienteActividad ? 'Podras continuar al terminar.' : 'Al cerrar volveras a la leccion.'; ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Modo</div>
                    <div class="metric-value">Interactivo</div>
                    <div class="metric-note">Retroalimentacion dentro de la misma pantalla.</div>
                </div>
            </div>
        </section>

        <div class="activity-card activity-player-shell">
            <?php if ($supportResource): ?>
                <?php renderPreviewSupportResource($supportResource); ?>
            <?php endif; ?>
            
            <div id="activity-content" class="activity-player-content">
                <!-- El contenido de la actividad se cargará aquí dinámicamente -->
            </div>
            
            <div id="feedback" class="feedback" style="display: none;"></div>
            
            <div class="responsive-actions activity-player-actions">
                <button id="submit-activity" class="btn btn-primary btn-lg submit-button" onclick="submitActivity()">Enviar respuesta</button>
                <button id="next-activity" class="btn btn-success btn-lg submit-button" style="display: none;" onclick="nextActivity()">Siguiente actividad</button>
                <a href="<?php echo $backUrl; ?>" class="btn btn-outline-secondary btn-lg"><?php echo $backLabel; ?></a>
            </div>
        </div>
    </div>
</div>

<script>
    const actividadData = <?php echo json_encode($actividad); ?>;
    const siguienteActividad = <?php echo isset($siguienteActividad) && $siguienteActividad ? json_encode($siguienteActividad) : 'null'; ?>;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Mejor manejo del contenido JSON
    let contenido;
    try {
        if (typeof actividadData.contenido === 'string') {
            contenido = JSON.parse(actividadData.contenido || '{}');
        } else {
            contenido = actividadData.contenido || {};
        }
        console.log('Contenido de actividad cargado:', contenido);
    } catch (e) {
        console.error('Error al parsear contenido de actividad:', e);
        contenido = {};
    }
    
    const sortableCorrectOrder = (contenido.items || []).map(text => (text || '').trim());
    const userRole = '<?php echo Auth::getUserRole(); ?>';
    let currentOptions = [];
    let questionMode = 'single';
    let questions = [];
    let selectedOptionsByQuestion = {};
    let pronunciationRecognition = null;
    let pronunciationActiveTarget = null;
    let selectedPreviewDragItemId = null;
    
    // Función para barajar un array
    function shuffleArray(array) {
        const shuffled = [...array];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }
    
    // Función para cargar la actividad según su tipo
    function loadActivity() {
        const contentDiv = document.getElementById('activity-content');
        
        switch (actividadData.tipo_actividad) {
            case 'opcion_multiple':
            case 'verdadero_falso':
                loadMultipleChoice(contentDiv);
                break;
            case 'arrastrar_soltar':
            case 'emparejamiento':
                loadDragAndDrop(contentDiv);
                break;
            case 'respuesta_corta':
            case 'completar_oracion':
            case 'escritura':
                loadShortAnswer(contentDiv);
                break;
            case 'ordenar_palabras':
                loadSortable(contentDiv);
                break;
            case 'pronunciacion':
                loadPronunciation(contentDiv);
                break;
            case 'escucha':
                loadListening(contentDiv);
                break;
            default:
                contentDiv.innerHTML = '<div class="alert alert-warning">Tipo de actividad no soportado en esta vista previa.</div>';
        }
    }
    
    function loadMultipleChoice(container) {
        const preguntasConfig = Array.isArray(contenido.preguntas) ? contenido.preguntas : null;

        if (preguntasConfig && preguntasConfig.length) {
            questionMode = 'multi';
            questions = preguntasConfig.map(q => ({
                texto: q.texto || '',
                opciones: shuffleArray(q.opciones || [])
            }));
            selectedOptionsByQuestion = {};

            if (!questions.length) {
                container.innerHTML = '<div class="alert alert-warning">Esta actividad aun no tiene preguntas configuradas.</div>';
                const submitBtn = document.getElementById('submit-activity');
                if (submitBtn) submitBtn.style.display = 'none';
                return;
            }

            let html = '';
            const tituloPregunta = contenido.pregunta_global || contenido.pregunta || '';
            if (tituloPregunta) {
                html += `<p><strong>${tituloPregunta}</strong></p>`;
            }

            questions.forEach((q, qIndex) => {
                const textoPregunta = q.texto || `Pregunta ${qIndex + 1}`;
                html += `<div class="mb-3"><p><strong>${textoPregunta}</strong></p>`;

                if (q.image_url) {
                    html += `<div class="mb-3"><img src="${q.image_url}" alt="${q.image_alt || textoPregunta}" class="img-fluid rounded-4 border activity-question-image"></div>`;
                }

                q.opciones.forEach((opcion, oIndex) => {
                    html += `<button class="option-button" onclick="selectOption(${qIndex}, ${oIndex})" data-q="${qIndex}" data-o="${oIndex}">${opcion.texto}</button>`;
                });

                html += `</div>`;
            });

            container.innerHTML = html;
        } else {
            questionMode = 'single';
            currentOptions = shuffleArray(contenido.opciones || []);
            const pregunta = contenido.pregunta || contenido.pregunta_global || 'Selecciona la respuesta correcta:';
            
            if (!currentOptions.length) {
                container.innerHTML = '<div class="alert alert-warning">Esta actividad aun no tiene opciones configuradas.</div>';
                const submitBtn = document.getElementById('submit-activity');
                if (submitBtn) submitBtn.style.display = 'none';
                return;
            }
            
            let html = `<p><strong>${pregunta}</strong></p>`;

            if (contenido.image_url) {
                html += `<div class="mb-3"><img src="${contenido.image_url}" alt="${contenido.image_alt || pregunta}" class="img-fluid rounded-4 border activity-question-image"></div>`;
            }
            
            currentOptions.forEach((opcion, index) => {
                html += `<button class="option-button" onclick="selectOption(${index})" data-index="${index}">${opcion.texto}</button>`;
            });
            
            container.innerHTML = html;
        }
    }
    
    // Cargar actividad de arrastrar y soltar
    function loadDragAndDrop(container) {
        let items = [];
        let targets = [];

        if (Array.isArray(contenido)) {
            // Estructura de Pares (seed format)
            contenido.forEach((pair, idx) => {
                items.push({ id: pair.id || `item-${idx}`, texto: pair.left });
                targets.push({ id: pair.id || `target-${idx}`, texto: pair.right });
            });
            // Barajar targets para que no estén alineados inicialmente
            targets = shuffleArray(targets);
        } else if (contenido.pares) {
             // Estructura de Pares explícita
             contenido.pares.forEach((pair, idx) => {
                items.push({ id: `item-${idx}`, texto: pair.left });
                targets.push({ id: `target-${idx}`, texto: pair.right });
            });
            targets = shuffleArray(targets);
        } else {
            // Estructura original (items/targets)
            items = contenido.items || [];
            targets = contenido.targets || [];
        }
        
        let html = `<div class="alert alert-light border mb-3"><strong>Modo tactil:</strong> toca un elemento y luego toca la columna destino para moverlo.</div>
        <div class="drag-drop-container">
            <div class="drag-zone">
                <h4>Arrastra aqui:</h4>`;
        
        items.forEach((item, index) => {
            html += `<div class="draggable-item" draggable="true" data-id="${item.id}" onclick="selectPreviewDragItem('${item.id}')">${item.texto}</div>`;
        });
        
        html += `</div>
            <div class="drop-zone">
                <h4>Suelta aqui:</h4>`;
        
        targets.forEach((target, index) => {
            html += `<div class="drop-target" data-target="${target.id}" onclick="placePreviewDragItem('${target.id}')">${target.texto}</div>`;
        });
        
        html += `</div>
        </div>`;
        
        container.innerHTML = html;
        
        // Añadir eventos de arrastre
        addDragAndDropEvents();
    }
    
    // Cargar actividad de respuesta corta
    function loadShortAnswer(container) {
        if (contenido.preguntas && contenido.preguntas.length > 0) {
            // Modo múltiples preguntas
            let html = '';
            if (contenido.pregunta_global) {
                html += `<p class="mb-3"><strong>${contenido.pregunta_global}</strong></p>`;
            }
            
            contenido.preguntas.forEach((p, index) => {
                const texto = p.texto || `Pregunta ${index + 1}`;
                const placeholder = p.placeholder || 'Escribe aquí tu respuesta...';
                html += `
                    <div class="mb-4 question-block" data-index="${index}">
                        <p><strong>${texto}</strong></p>
                        <input type="text" class="form-control text-input mb-2" id="short-answer-${index}" placeholder="${placeholder}">
                        <div class="feedback-msg" id="feedback-${index}" style="display:none; font-size: 0.9em;"></div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            // Legacy mode
            const pregunta = contenido.pregunta || contenido.oracion || contenido.tema || 'Escribe tu respuesta:';
            const placeholder = contenido.placeholder || 'Escribe aquí tu respuesta...';
            
            let html = `<p><strong>${pregunta}</strong></p>
                <input type="text" class="form-control text-input" id="short-answer" placeholder="${placeholder}">`;
            
            container.innerHTML = html;
        }
    }

    function loadListening(container) {
        const textToSpeak = contenido.texto_tts || contenido.transcripcion || '';
        const transcript = contenido.transcripcion || '';
        let html = '<div class="alert alert-info">Vista previa de actividad de escucha.</div>';

        if (textToSpeak) {
            const encodedText = encodeURIComponent(textToSpeak);
            html += `
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-primary btn-lg" onclick="speakPreviewText(decodeURIComponent('${encodedText}'))">
                        <i class="bi bi-volume-up-fill"></i> Reproducir audio
                    </button>
                    <p class="text-muted mt-2 mb-0"><small>Usa sintesis de voz del navegador para una comprobacion rapida.</small></p>
                </div>
            `;
        } else {
            html += '<div class="alert alert-warning">Esta actividad no tiene audio o texto TTS configurado.</div>';
        }

        html += `
            <label for="listening-answer" class="form-label">Escribe lo que escuchaste:</label>
            <textarea id="listening-answer" class="form-control text-input" rows="4" placeholder="Escribe aqui tu respuesta..."></textarea>
        `;

        if (userRole === 'profesor' || userRole === 'admin') {
            html += `<div class="alert alert-light border mt-3 mb-0"><strong>Referencia:</strong> ${escapeHtml(transcript || 'Sin transcripcion configurada.')}</div>`;
        }

        container.innerHTML = html;
    }

    function loadPronunciation(container) {
        const prompts = Array.isArray(contenido) ? contenido : [];

        if (!prompts.length) {
            container.innerHTML = '<div class="alert alert-warning">Esta actividad no tiene frases configuradas para pronunciacion.</div>';
            return;
        }

        let html = '<div class="alert alert-info">Vista previa de actividad de pronunciacion.</div>';
        prompts.forEach((prompt, index) => {
            const promptId = prompt.id || `pron-${index}`;
            const phrase = prompt.frase || '';
            html += `
                <div class="card mb-3 border-light shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3">"${escapeHtml(phrase)}"</h5>
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-outline-primary btn-lg rounded-circle p-3" onclick="startPronunciationListening('${promptId}')">
                                <i class="bi bi-mic-fill fs-3"></i>
                            </button>
                            <div id="pron-status-${promptId}" class="text-muted mt-2 small" style="min-height: 20px;">Pulsa para probar reconocimiento.</div>
                        </div>
                        <label for="pron-input-${promptId}" class="form-label text-muted small">Texto reconocido:</label>
                        <input type="text" id="pron-input-${promptId}" class="form-control text-input" placeholder="Tu pronunciacion aparecera aqui...">
                    </div>
                </div>
            `;
        });

        if (userRole === 'profesor' || userRole === 'admin') {
            html += '<div class="alert alert-light border mb-0">El preview usa reconocimiento de voz del navegador. No reemplaza la experiencia completa del alumno.</div>';
        }

        container.innerHTML = html;
    }

    function speakPreviewText(text) {
        if (!('speechSynthesis' in window)) {
            alert('Tu navegador no soporta sintesis de voz.');
            return;
        }

        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = detectSpeechLang();
        utterance.rate = 0.9;
        window.speechSynthesis.speak(utterance);
    }

    function startPronunciationListening(promptId) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const statusEl = document.getElementById(`pron-status-${promptId}`);
        const inputEl = document.getElementById(`pron-input-${promptId}`);

        if (!SpeechRecognition) {
            if (statusEl) statusEl.innerText = 'Reconocimiento de voz no disponible en este navegador.';
            return;
        }

        if (pronunciationRecognition) {
            pronunciationRecognition.stop();
        }

        pronunciationActiveTarget = promptId;
        pronunciationRecognition = new SpeechRecognition();
        pronunciationRecognition.lang = detectSpeechLang();
        pronunciationRecognition.interimResults = false;
        pronunciationRecognition.maxAlternatives = 1;

        pronunciationRecognition.onstart = () => {
            if (statusEl) statusEl.innerText = 'Escuchando...';
        };

        pronunciationRecognition.onresult = (event) => {
            const transcript = event.results?.[0]?.[0]?.transcript || '';
            if (inputEl) inputEl.value = transcript;
            if (statusEl) statusEl.innerText = 'Captura completada.';
        };

        pronunciationRecognition.onerror = () => {
            if (statusEl) statusEl.innerText = 'No pudimos capturar audio.';
        };

        pronunciationRecognition.onend = () => {
            if (statusEl && statusEl.innerText === 'Escuchando...') {
                statusEl.innerText = 'Sin resultados.';
            }
        };

        pronunciationRecognition.start();
    }

    function detectSpeechLang() {
        const languageMap = {
            ingles: 'en-US',
            frances: 'fr-FR',
            aleman: 'de-DE'
        };
        return languageMap[actividadData.idioma_objetivo] || languageMap[actividadData.idioma] || 'es-ES';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function loadSortable(container) {
        const items = shuffleArray(sortableCorrectOrder);
        let html = '<div class="alert alert-light border mb-3"><strong>Modo tactil:</strong> arrastra o usa subir y bajar para ajustar el orden.</div><ul id="sortable-list" class="sortable-list">';
        items.forEach(text => {
            html += `<li class="sortable-item" draggable="true"><span class="sortable-item-label">${text}</span><span class="word-order-controls"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="shiftPreviewSortable(this, 'up')"><i class="bi bi-arrow-up"></i></button><button type="button" class="btn btn-sm btn-outline-secondary" onclick="shiftPreviewSortable(this, 'down')"><i class="bi bi-arrow-down"></i></button></span></li>`;
        });
        html += '</ul>';
        container.innerHTML = html;
        addSortableEvents();
    }

    function shiftPreviewSortable(button, direction) {
        const item = button.closest('.sortable-item');
        const list = document.getElementById('sortable-list');
        if (!item || !list) return;

        if (direction === 'up' && item.previousElementSibling) {
            list.insertBefore(item, item.previousElementSibling);
        }

        if (direction === 'down' && item.nextElementSibling) {
            list.insertBefore(item.nextElementSibling, item);
        }
    }
    
    let selectedOption = null;
    
    function selectOption(index, optionIndex) {
        if (questionMode === 'multi') {
            const qIndex = index;
            const oIndex = optionIndex;

            document.querySelectorAll('.option-button[data-q="' + qIndex + '"]').forEach(btn => {
                btn.classList.remove('selected');
            });

            const button = document.querySelector('.option-button[data-q="' + qIndex + '"][data-o="' + oIndex + '"]');
            if (button) {
                button.classList.add('selected');
                selectedOptionsByQuestion[qIndex] = oIndex;
            }
        } else {
            document.querySelectorAll('.option-button').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            const button = document.querySelector('[data-index="' + index + '"]');
            if (button) {
                button.classList.add('selected');
                selectedOption = index;
            }
        }
    }
    
    // Funciones de arrastrar y soltar
    function clearPreviewDragSelection() {
        document.querySelectorAll('.draggable-item.is-selected-touch').forEach(item => {
            item.classList.remove('is-selected-touch');
        });
        selectedPreviewDragItemId = null;
    }

    function selectPreviewDragItem(itemId) {
        const draggedItem = document.querySelector(`.draggable-item[data-id="${itemId}"]`);
        if (!draggedItem) return;

        if (selectedPreviewDragItemId === itemId) {
            clearPreviewDragSelection();
            return;
        }

        clearPreviewDragSelection();
        selectedPreviewDragItemId = itemId;
        draggedItem.classList.add('is-selected-touch');
    }

    function placePreviewDragItem(targetId) {
        if (!selectedPreviewDragItemId) return;

        const target = document.querySelector(`.drop-target[data-target="${targetId}"]`);
        const draggedItem = document.querySelector(`.draggable-item[data-id="${selectedPreviewDragItemId}"]`);
        if (!target || !draggedItem) return;

        target.appendChild(draggedItem);
        clearPreviewDragSelection();
    }

    function addDragAndDropEvents() {
        const draggables = document.querySelectorAll('.draggable-item');
        const dropZones = document.querySelectorAll('.drop-target');
        
        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', e.target.dataset.id);
                e.target.classList.add('dragging');
            });
            
            draggable.addEventListener('dragend', (e) => {
                e.target.classList.remove('dragging');
            });
        });
        
        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
            
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('drag-over');
            });
            
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                
                const itemId = e.dataTransfer.getData('text/plain');
                const draggedItem = document.querySelector(`[data-id="${itemId}"]`);
                
                if (draggedItem) {
                    zone.appendChild(draggedItem);
                    clearPreviewDragSelection();
                }
            });
        });
    }

    // Funciones para lista sortable
    function addSortableEvents() {
        const list = document.getElementById('sortable-list');
        if (!list) return;

        let draggedItem = null;

        list.querySelectorAll('.sortable-item').forEach(item => {
            item.addEventListener('dragstart', (e) => {
                draggedItem = e.target;
                e.target.classList.add('dragging');
            });

            item.addEventListener('dragend', (e) => {
                e.target.classList.remove('dragging');
                draggedItem = null;
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                const bounding = e.target.getBoundingClientRect();
                const offset = bounding.y + (bounding.height / 2);
                const parent = e.target.parentNode;
                if (e.clientY - offset > 0) {
                    parent.insertBefore(draggedItem, e.target.nextSibling);
                } else {
                    parent.insertBefore(draggedItem, e.target);
                }
            });
        });
    }
    
    // Función para enviar la respuesta
    function submitActivity() {
        let respuesta = '';
        let esCorrecta = false;
        let feedbackSummary = [];
        
        switch (actividadData.tipo_actividad) {
            case 'opcion_multiple':
                if (questionMode === 'multi') {
                    const respuestasDetalle = [];
                    let totalPreguntas = questions.length;
                    let correctas = 0;

                    questions.forEach((q, qIndex) => {
                        const selectedIdx = selectedOptionsByQuestion[qIndex];
                        if (typeof selectedIdx === 'number') {
                            const opcion = q.opciones[selectedIdx];
                            const esRespCorrecta = !!opcion.es_correcta;
                            respuestasDetalle.push({
                                pregunta: q.texto || ('Pregunta ' + (qIndex + 1)),
                                respuesta: opcion.texto,
                                es_correcta: esRespCorrecta
                            });
                            feedbackSummary.push({
                                pregunta: q.texto || ('Pregunta ' + (qIndex + 1)),
                                estado: esRespCorrecta ? 'correcta' : 'incorrecta',
                                nota: esRespCorrecta ? 'Elegiste la opcion correcta.' : 'Revisa la opcion correcta marcada en verde.'
                            });
                            if (esRespCorrecta) {
                                correctas++;
                            }
                        } else {
                            respuestasDetalle.push({
                                pregunta: q.texto || ('Pregunta ' + (qIndex + 1)),
                                respuesta: null,
                                es_correcta: false
                            });
                            feedbackSummary.push({
                                pregunta: q.texto || ('Pregunta ' + (qIndex + 1)),
                                estado: 'pendiente',
                                nota: 'No respondiste esta pregunta.'
                            });
                        }
                    });

                    esCorrecta = totalPreguntas > 0 && correctas === totalPreguntas;
                    respuesta = JSON.stringify(respuestasDetalle);
                } else {
                    if (selectedOption !== null) {
                        respuesta = currentOptions[selectedOption].texto;
                        esCorrecta = currentOptions[selectedOption].es_correcta;
                        feedbackSummary.push({
                            pregunta: contenido.pregunta || contenido.pregunta_global || 'Pregunta',
                            estado: esCorrecta ? 'correcta' : 'incorrecta',
                            nota: esCorrecta ? 'Elegiste la opcion correcta.' : 'Revisa la opcion correcta marcada en verde.'
                        });
                    }
                }
                break;
                
            case 'arrastrar_soltar':
                // Verificar si los elementos están en las zonas correctas
                const dropZones = document.querySelectorAll('.drop-target');
                esCorrecta = true;
                const matches = {}; // Para guardar detalle de respuestas
                
                dropZones.forEach(zone => {
                    const items = zone.querySelectorAll('.draggable-item');
                    const targetId = zone.dataset.target;
                    
                    if (items.length === 0) {
                        esCorrecta = false;
                    }
                    
                    items.forEach(item => {
                        matches[item.dataset.id] = targetId; // item.id -> target.id
                        if (item.dataset.id !== targetId) {
                            esCorrecta = false;
                        }
                    });
                });
                respuesta = JSON.stringify(matches);
                break;
                
            case 'respuesta_corta':
                if (contenido.preguntas && contenido.preguntas.length > 0) {
                    // Multiple questions
                    let allCorrect = true;
                    const userAnswers = [];
                    
                    contenido.preguntas.forEach((p, index) => {
                        const input = document.getElementById(`short-answer-${index}`);
                        const feedback = document.getElementById(`feedback-${index}`);
                        const val = input.value.trim().toLowerCase();
                        
                        // Check correctness
                        const isCorrect = p.respuestas_correctas.some(r => r.trim().toLowerCase() === val);
                        
                        userAnswers.push({
                            pregunta: p.texto,
                            respuesta: input.value,
                            es_correcta: isCorrect
                        });
                        
                        if (isCorrect) {
                            input.classList.add('is-valid');
                            input.classList.remove('is-invalid');
                            feedback.textContent = 'Correcto.';
                            feedback.className = 'feedback-msg text-success';
                            feedback.style.display = 'block';
                        } else {
                            input.classList.add('is-invalid');
                            input.classList.remove('is-valid');
                            // Opcional: mostrar respuestas correctas
                            // feedback.textContent = 'Incorrecto. Respuestas aceptadas: ' + p.respuestas_correctas.join(', ');
                            feedback.textContent = 'Incorrecto. Revisa la consigna y vuelve a probar.'; 
                            feedback.className = 'feedback-msg text-danger';
                            feedback.style.display = 'block';
                            allCorrect = false;
                        }
                    });
                    
                    esCorrecta = allCorrect;
                    respuesta = JSON.stringify(userAnswers);
                    
                } else {
                    // Legacy single question
                    const input = document.getElementById('short-answer');
                    if (input) {
                        respuesta = input.value;
                        const respuestasCorrectas = contenido.respuestas_correctas || [];
                        esCorrecta = respuestasCorrectas.some(respuestaCorrecta => 
                            respuesta.toLowerCase().trim() === respuestaCorrecta.toLowerCase().trim()
                        );
                        
                        if (esCorrecta) {
                            input.classList.add('is-valid');
                            input.classList.remove('is-invalid');
                        } else {
                            input.classList.add('is-invalid');
                            input.classList.remove('is-valid');
                        }
                    }
                }
                break;
            case 'ordenar_palabras':
                const list = document.getElementById('sortable-list');
                if (list) {
                    const currentOrder = Array.from(list.querySelectorAll('.sortable-item'))
                        .map(li => (li.querySelector('.sortable-item-label')?.textContent || li.textContent).trim());
                    const correctOrder = sortableCorrectOrder;
                    esCorrecta = currentOrder.length === correctOrder.length &&
                        currentOrder.every((text, idx) => text === correctOrder[idx]);
                    respuesta = currentOrder.join(' | ');
                    feedbackSummary.push({
                        pregunta: 'Orden final',
                        estado: esCorrecta ? 'correcta' : 'incorrecta',
                        nota: esCorrecta ? 'El orden coincide con la solucion esperada.' : 'Ajusta la secuencia hasta que la frase suene natural.'
                    });
                }
                break;
            case 'escucha':
                const listeningAnswer = document.getElementById('listening-answer');
                if (listeningAnswer) {
                    respuesta = listeningAnswer.value.trim();
                    const expectedTranscript = (contenido.transcripcion || '').trim().toLowerCase();
                    const normalizedAnswer = respuesta.toLowerCase().trim();
                    esCorrecta = normalizedAnswer !== '' && normalizedAnswer === expectedTranscript;
                }
                break;
            case 'pronunciacion':
                const prompts = Array.isArray(contenido) ? contenido : [];
                const pronunciationAnswers = [];
                esCorrecta = prompts.length > 0;
                prompts.forEach((prompt, index) => {
                    const promptId = prompt.id || `pron-${index}`;
                    const input = document.getElementById(`pron-input-${promptId}`);
                    const recognized = input ? input.value.trim() : '';
                    pronunciationAnswers.push({
                        id: promptId,
                        frase: prompt.frase || '',
                        respuesta: recognized
                    });
                    if (!recognized) {
                        esCorrecta = false;
                    }
                });
                respuesta = JSON.stringify(pronunciationAnswers);
                break;
        }
        
        // Guardar respuesta en servidor
        const data = {
            actividad_id: actividadData.id,
            respuesta: respuesta,
            es_correcta: esCorrecta ? 1 : 0,
            tiempo_respuesta: 0 // TODO: Implement timer
        };

        fetch('<?= url('/estudiante/actividad/guardar-respuesta') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            console.log('Guardado:', result);
            if (result && result.message) {
                const feedbackDiv = document.getElementById('feedback');
                if (feedbackDiv) {
                    const serverNote = document.createElement('div');
                    serverNote.className = 'mt-3 border-top pt-3';
                    serverNote.innerHTML = '<strong>Guardado:</strong> ' + escapeHtml(result.message + (typeof result.lesson_progress === 'number' ? ' Progreso de la leccion: ' + result.lesson_progress + '%.' : ''));
                    feedbackDiv.appendChild(serverNote);
                }
            }
        })
        .catch(error => {
            console.error('Error al guardar:', error);
        });

        // Mostrar feedback
        const feedbackDiv = document.getElementById('feedback');
        feedbackDiv.style.display = 'block';
        
        if (esCorrecta) {
            feedbackDiv.className = 'feedback correct';
            feedbackDiv.innerHTML = '<h4><i class="bi bi-check-circle-fill"></i> Actividad completada</h4><p>La interaccion funciono correctamente.</p>';
            
            if (actividadData.tipo_actividad === 'opcion_multiple') {
                if (questionMode === 'multi') {
                    questions.forEach((q, qIndex) => {
                        q.opciones.forEach((opcion, oIndex) => {
                            const btn = document.querySelector('.option-button[data-q="' + qIndex + '"][data-o="' + oIndex + '"]');
                            if (btn && opcion.es_correcta) {
                                btn.classList.add('correct');
                            }
                        });
                    });
                } else {
                    document.querySelectorAll('.option-button').forEach((btn, index) => {
                        if (currentOptions[index].es_correcta) {
                            btn.classList.add('correct');
                        }
                    });
                }
            }
        } else {
            feedbackDiv.className = 'feedback incorrect';
            feedbackDiv.innerHTML = '<h4><i class="bi bi-exclamation-circle-fill"></i> Respuesta incompleta o incorrecta</h4><p>Revisa las pistas visuales y vuelve a intentarlo.</p>';
            
            if (actividadData.tipo_actividad === 'opcion_multiple') {
                if (questionMode === 'multi') {
                    questions.forEach((q, qIndex) => {
                        const selectedIdx = selectedOptionsByQuestion[qIndex];
                        q.opciones.forEach((opcion, oIndex) => {
                            const btn = document.querySelector('.option-button[data-q="' + qIndex + '"][data-o="' + oIndex + '"]');
                            if (!btn) return;
                            if (opcion.es_correcta) {
                                btn.classList.add('correct');
                            } else if (typeof selectedIdx === 'number' && oIndex === selectedIdx) {
                                btn.classList.add('incorrect');
                            }
                        });
                    });
                } else {
                    document.querySelectorAll('.option-button').forEach((btn, index) => {
                        if (currentOptions[index].es_correcta) {
                            btn.classList.add('correct');
                        } else if (index === selectedOption) {
                            btn.classList.add('incorrect');
                        }
                    });
                }
            }
        }
        
        // Deshabilitar el botón de enviar y mostrar el de siguiente
        if (feedbackSummary.length > 0) {
            const summaryHtml = feedbackSummary.map(item => {
                const badgeClass = item.estado === 'correcta'
                    ? 'text-success'
                    : (item.estado === 'pendiente' ? 'text-warning' : 'text-danger');
                return `<div class="mt-2"><strong>${escapeHtml(item.pregunta)}:</strong> <span class="${badgeClass}">${escapeHtml(item.nota)}</span></div>`;
            }).join('');
            feedbackDiv.innerHTML += `<div class="mt-3 border-top pt-3">${summaryHtml}</div>`;
        }

        document.getElementById('submit-activity').disabled = true;
        const nextBtn = document.getElementById('next-activity');

        if (siguienteActividad && siguienteActividad.id) {
            nextBtn.textContent = 'Siguiente actividad';
            nextBtn.innerHTML = 'Siguiente actividad <i class="bi bi-arrow-right"></i>';
        } else {
            nextBtn.textContent = 'Finalizar leccion';
            nextBtn.innerHTML = '<i class="bi bi-check-lg"></i> Finalizar leccion';
        }
        nextBtn.style.display = 'inline-block';
    }

    // Funcion para ir a la siguiente actividad
    function nextActivity() {
        if (siguienteActividad && siguienteActividad.id) {
            if (userRole === 'profesor' || userRole === 'admin') {
                window.location.href = '<?= url('/profesor/actividad/') ?>' + siguienteActividad.id + '/preview';
            } else {
                window.location.href = '<?= url('/estudiante/actividad/') ?>' + siguienteActividad.id;
            }
        } else {
            if (userRole === 'profesor' || userRole === 'admin') {
                window.location.href = '<?= url('/profesor/lecciones/') ?>' + actividadData.leccion_id + '/actividades';
            } else {
                window.location.href = '<?= url('/estudiante/lecciones/') ?>' + actividadData.leccion_id + '/contenido';
            }
        }
    }
    
    // Cargar la actividad al iniciar
    document.addEventListener('DOMContentLoaded', loadActivity);
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
