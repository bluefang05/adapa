<?php require_once __DIR__ . '/../../partials/header.php'; ?>



<div class="container activity-player-page">
    <div class="activity-container activity-player">
        <section class="page-hero mb-4">
            <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Actividad interactiva</span>
            <h1 class="page-title"><?php echo htmlspecialchars($actividad->titulo); ?></h1>
            <p class="page-subtitle"><?php echo htmlspecialchars($actividad->descripcion ?: 'Resuelve la actividad y recibe retroalimentacion inmediata.'); ?></p>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Tipo</div>
                    <div class="metric-value text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $actividad->tipo_actividad)); ?></div>
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
            
            <div id="activity-content" class="activity-player-content">
                <!-- El contenido de la actividad se cargará aquí dinámicamente -->
            </div>
            
            <div id="feedback" class="feedback" style="display: none;"></div>
            
            <div class="responsive-actions activity-player-actions">
                <button id="submit-activity" class="btn btn-primary btn-lg submit-button" onclick="submitActivity()">Enviar respuesta</button>
                <button id="next-activity" class="btn btn-success btn-lg submit-button" style="display: none;" onclick="nextActivity()">Siguiente actividad</button>
                <a href="<?php echo url('/estudiante'); ?>" class="btn btn-outline-secondary btn-lg">Volver al panel</a>
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
            case 'escucha':
                contentDiv.innerHTML = '<div class="alert alert-warning">Esta actividad requiere recursos multimedia no disponibles en este entorno de prueba.</div>';
                break;
            default:
                contentDiv.innerHTML = '<p>Tipo de actividad no soportado.</p>';
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
                container.innerHTML = '<p>Esta actividad aún no tiene preguntas configuradas.</p>';
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
                container.innerHTML = '<p>Esta actividad aún no tiene opciones configuradas.</p>';
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
        
        let html = `<div class="drag-drop-container">
            <div class="drag-zone">
                <h4>Arrastra aquí:</h4>`;
        
        items.forEach((item, index) => {
            html += `<div class="draggable-item" draggable="true" data-id="${item.id}">${item.texto}</div>`;
        });
        
        html += `</div>
            <div class="drop-zone">
                <h4>Suelta aquí:</h4>`;
        
        targets.forEach((target, index) => {
            html += `<div class="drop-target" data-target="${target.id}">${target.texto}</div>`;
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

    function loadSortable(container) {
        const items = shuffleArray(sortableCorrectOrder);
        let html = '<ul id="sortable-list" class="sortable-list">';
        items.forEach(text => {
            html += `<li class="sortable-item" draggable="true">${text}</li>`;
        });
        html += '</ul>';
        container.innerHTML = html;
        addSortableEvents();
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
                            if (esRespCorrecta) {
                                correctas++;
                            }
                        } else {
                            respuestasDetalle.push({
                                pregunta: q.texto || ('Pregunta ' + (qIndex + 1)),
                                respuesta: null,
                                es_correcta: false
                            });
                        }
                    });

                    esCorrecta = totalPreguntas > 0 && correctas === totalPreguntas;
                    respuesta = JSON.stringify(respuestasDetalle);
                } else {
                    if (selectedOption !== null) {
                        respuesta = currentOptions[selectedOption].texto;
                        esCorrecta = currentOptions[selectedOption].es_correcta;
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
                            feedback.textContent = '¡Correcto!';
                            feedback.className = 'feedback-msg text-success';
                            feedback.style.display = 'block';
                        } else {
                            input.classList.add('is-invalid');
                            input.classList.remove('is-valid');
                            // Opcional: mostrar respuestas correctas
                            // feedback.textContent = 'Incorrecto. Respuestas aceptadas: ' + p.respuestas_correctas.join(', ');
                            feedback.textContent = 'Incorrecto.'; 
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
                        .map(li => li.textContent.trim());
                    const correctOrder = sortableCorrectOrder;
                    esCorrecta = currentOrder.length === correctOrder.length &&
                        currentOrder.every((text, idx) => text === correctOrder[idx]);
                    respuesta = currentOrder.join(' | ');
                }
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
        })
        .catch(error => {
            console.error('Error al guardar:', error);
        });

        // Mostrar feedback
        const feedbackDiv = document.getElementById('feedback');
        feedbackDiv.style.display = 'block';
        
        if (esCorrecta) {
            feedbackDiv.className = 'feedback correct';
            feedbackDiv.innerHTML = '<h4><i class="bi bi-check-circle-fill"></i> ¡Actividad Completada!</h4><p>¡Correcto! Bien hecho.</p>';
            
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
            feedbackDiv.textContent = 'Incorrecto. Intenta de nuevo.';
            
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
        document.getElementById('submit-activity').disabled = true;
        const nextBtn = document.getElementById('next-activity');
        
        if (siguienteActividad && siguienteActividad.id) {
            nextBtn.textContent = 'Siguiente Actividad';
            nextBtn.innerHTML = 'Siguiente Actividad <i class="bi bi-arrow-right"></i>';
        } else {
            nextBtn.textContent = 'Finalizar Lección';
            nextBtn.innerHTML = '<i class="bi bi-check-lg"></i> Finalizar Lección';
        }
        nextBtn.style.display = 'inline-block';
        
        // Guardar respuesta (aquí iría la lógica para guardar en la base de datos)
        console.log('Respuesta:', respuesta, 'Es correcta:', esCorrecta);
    }
    
    // Función para ir a la siguiente actividad
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
