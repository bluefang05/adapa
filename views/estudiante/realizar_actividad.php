<?php 
require_once __DIR__ . '/../partials/header.php';

function studentActivityTypeLabel($tipo) {
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

function renderStudentSupportResource($resource) {
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
    ?>
    <div class="support-resource-panel mb-4">
        <div class="support-resource-header">
            <div>
                <div class="support-resource-eyebrow"><?php echo htmlspecialchars($kindLabel); ?></div>
                <h3 class="support-resource-title"><?php echo $title; ?></h3>
            </div>
            <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">
                Abrir recurso
            </a>
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
            <div class="support-resource-fallback">
                Usa este recurso como referencia antes de responder la actividad.
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>

<div class="container">
    <!-- Mensajes de sesion -->
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante'); ?>">Mis Cursos</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>"><?php echo htmlspecialchars($leccion->titulo); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($actividad->titulo); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php
                $isRetry = isset($_GET['retry']) && $_GET['retry'] == '1';
                $showFeedback = isset($respuestaExistente) && $respuestaExistente && !$isRetry;
            ?>
            <div class="page-hero mb-4">
                <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Actividad activa</span>
                <h1 class="page-title"><?php echo htmlspecialchars($actividad->titulo); ?></h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($actividad->descripcion ?: 'Completa la actividad y revisa tu avance dentro de la leccion.'); ?></p>
                <div class="hero-actions">
                    <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a la leccion
                    </a>
                    <?php if ($showFeedback && !$isRetry): ?>
                        <a href="?retry=1" class="btn btn-success">
                            <i class="bi bi-arrow-repeat"></i> Practicar otra vez
                        </a>
                    <?php endif; ?>
                </div>
                <div class="metric-grid">
                    <div class="metric-card">
                        <div class="metric-label">Tipo</div>
                        <div class="metric-value"><?php echo htmlspecialchars(studentActivityTypeLabel($actividad->tipo_actividad)); ?></div>
                        <div class="metric-note">Formato de esta practica.</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Estado</div>
                        <div class="metric-value"><?php echo $isRetry ? 'Practica' : ($showFeedback ? 'Hecha' : 'Nueva'); ?></div>
                        <div class="metric-note"><?php echo $showFeedback ? 'Ya existe una respuesta registrada.' : 'Aun no envias esta actividad.'; ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Leccion</div>
                        <div class="metric-value"><?php echo (int) $leccion->orden; ?></div>
                        <div class="metric-note"><?php echo htmlspecialchars($leccion->titulo); ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Objetivo</div>
                        <div class="metric-value"><?php echo !empty($actividad->puntos_maximos) ? (int) $actividad->puntos_maximos . ' pts' : 'Practica'; ?></div>
                        <div class="metric-note"><?php echo !empty($actividad->tiempo_limite_minutos) ? ((int) $actividad->tiempo_limite_minutos . ' min estimados') : 'Sin tiempo fijo'; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Contenido de la actividad -->
            <div class="card activity-shell">
                <div class="card-body">
                    <?php 
                        // Determinar el codigo de idioma para la actividad (Speech API y atributos HTML)
                        $idiomaCurso = isset($curso->idioma_objetivo) && $curso->idioma_objetivo
                            ? strtolower($curso->idioma_objetivo)
                            : (isset($curso->idioma) ? strtolower($curso->idioma) : 'ingles');
                        $ttsLanguageMap = app_tts_language_map();
                        $langCode = $ttsLanguageMap[$idiomaCurso] ?? 'en-US';
                        $supportResource = app_activity_support_resource($actividad->contenido ?? null);
                    ?>

                    <?php if (!empty($actividad->instrucciones) || !empty($actividad->descripcion)): ?>
                        <div class="alert alert-light border mb-4">
                            <div class="fw-semibold mb-2">Como abordar esta actividad</div>
                            <div class="small text-muted">
                                <?php echo htmlspecialchars($actividad->instrucciones ?: $actividad->descripcion); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($supportResource): ?>
                        <?php renderStudentSupportResource($supportResource); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($respuestaExistente) && $respuestaExistente): ?>
                        <div class="alert alert-info mb-4 activity-feedback-alert" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div class="flex-grow-1">
                                    <strong>Actividad ya completada</strong>
                                    <br>
                                    Has realizado esta actividad el <?php echo date('d/m/Y H:i', strtotime($respuestaExistente->fecha_respuesta)); ?>.
                                    <?php if (isset($respuestaExistente->puntuacion)): ?>
                                        Tu puntuacion fue: <strong><?php echo $respuestaExistente->puntuacion; ?></strong> puntos.
                                    <?php elseif (($actividad->tipo_actividad == 'escritura' || $actividad->tipo_actividad == 'escucha') && !isset($respuestaExistente->puntuacion)): ?>
                                        <span class="badge bg-warning text-dark">Pendiente de calificacion</span>
                                    <?php endif; ?>
                                    <br>
                                    Puedes volver a realizarla para mejorar tu resultado.
                                </div>
                                <?php if (isset($siguienteItem) && $siguienteItem): ?>
                                    <div class="ms-3">
                                        <?php 
                                            $nextUrl = '#';
                                            if ($siguienteItem['tipo'] === 'actividad') {
                                                $nextUrl = url('/estudiante/actividades/' . $siguienteItem['id']);
                                            } elseif ($siguienteItem['tipo'] === 'leccion') {
                                                $nextUrl = url('/estudiante/lecciones/' . $siguienteItem['id'] . '/contenido');
                                            } elseif ($siguienteItem['tipo'] === 'teoria') {
                                                $nextUrl = url('/estudiante/lecciones/' . $leccion->id . '/contenido#teoria-' . $siguienteItem['id']);
                                            } else {
                                                $nextUrl = url('/estudiante/cursos/' . $siguienteItem['id'] . '/lecciones');
                                            }
                                        ?>
                                        <a href="<?php echo $nextUrl; ?>" class="btn btn-primary text-nowrap">
                                            <?php echo htmlspecialchars($siguienteItem['mensaje']); ?> <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo url('/estudiante/actividades/' . $actividad->id . '/responder'); ?>" method="post">
                        <?php echo csrf_input(); ?>
                        <?php if (($actividad->tipo_actividad === 'opcion_multiple' || $actividad->tipo_actividad === 'verdadero_falso') && !empty($configActividad)): ?>
                            <!-- Actividad de opcion multiple / verdadero o falso -->
                            <?php foreach ($configActividad as $preguntaIndex => $pregunta): ?>
                                    <div class="card mb-4 border-light shadow-sm">
                                    <div class="card-body">
                                        <?php if (!empty($pregunta->texto)): ?>
                                            <h5 class="card-title mb-3"><?php echo htmlspecialchars($pregunta->texto); ?></h5>
                                        <?php elseif (!empty($actividad->descripcion) && $preguntaIndex === 0): ?>
                                             <h5 class="card-title mb-3"><?php echo htmlspecialchars($actividad->descripcion); ?></h5>
                                        <?php endif; ?>

                                        <?php if (!empty($pregunta->image_url)): ?>
                                            <div class="mb-3">
                                                <img
                                                    src="<?php echo htmlspecialchars($pregunta->image_url); ?>"
                                                    alt="<?php echo htmlspecialchars($pregunta->image_alt ?? ($pregunta->texto ?? 'Imagen de apoyo')); ?>"
                                                    class="img-fluid rounded-4 border activity-question-image"
                                                >
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($pregunta->opciones)): ?>
                                            <?php
                                                $respuestaUsuario = $respuestasUsuario[$pregunta->id] ?? null;
                                                $preguntaTieneRespuesta = $respuestaUsuario !== null && $respuestaUsuario !== '';
                                                $preguntaEsCorrecta = false;
                                                if ($showFeedback && $preguntaTieneRespuesta) {
                                                    foreach ($pregunta->opciones as $opcionRevision) {
                                                        $opcionTextoRevision = $opcionRevision->opcion_texto ?? null;
                                                        $opcionCorrectaRevision = isset($opcionRevision->es_correcta) && ($opcionRevision->es_correcta == 1 || $opcionRevision->es_correcta === true);
                                                        if ($respuestaUsuario === $opcionTextoRevision && $opcionCorrectaRevision) {
                                                            $preguntaEsCorrecta = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <div class="list-group">
                                                <?php foreach ($pregunta->opciones as $opcion): ?>
                                                    <?php 
                                                        $opcionTexto = $opcion->opcion_texto;
                                                        
                                                        $esSeleccionada = false;
                                                        $esCorrecta = false;
                                                        $claseItem = 'list-group-item-action';
                                                        $badge = '';
                                                        $checked = '';
                                                        $disabled = '';
                                                        
                                                        // Solo procesar feedback si NO estamos en modo reintento
                                                        if ($showFeedback) {
                                                            $esSeleccionada = ($respuestaUsuario === $opcionTexto);
                                                            $esCorrecta = isset($opcion->es_correcta) && ($opcion->es_correcta == 1 || $opcion->es_correcta === true);
                                                            
                                                            $disabled = 'disabled';
                                                            if ($esSeleccionada) {
                                                                $checked = 'checked';
                                                                if ($esCorrecta) {
                                                                    $claseItem = 'list-group-item-success';
                                                                    $badge = '<span class="badge bg-success ms-auto">Correcto</span>';
                                                                } else {
                                                                    $claseItem = 'list-group-item-danger';
                                                                    $badge = '<span class="badge bg-danger ms-auto">Incorrecto</span>';
                                                                }
                                                            } 
                                                            
                                                            if ($esCorrecta) {
                                                                // Always highlight correct answer
                                                                if (!$esSeleccionada) {
                                                                    $claseItem = 'list-group-item-success';
                                                                    $badge = '<span class="badge bg-success ms-auto">Respuesta Correcta</span>';
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                    <label class="list-group-item <?php echo $claseItem; ?> d-flex align-items-center">
                                                        <input class="form-check-input me-3" type="radio" 
                                                               name="respuesta[<?php echo $pregunta->id; ?>]" 
                                                               id="opcion_<?php echo $pregunta->id . '_' . $opcion->id; ?>"
                                                               value="<?php echo htmlspecialchars($opcionTexto); ?>" 
                                                               <?php echo $checked; ?> 
                                                               <?php echo $disabled; ?> 
                                                               <?php echo (!$showFeedback) ? 'required' : ''; ?>>
                                                        <div class="flex-grow-1">
                                                            <?php echo htmlspecialchars($opcionTexto); ?>
                                                        </div>
                                                        <?php echo $badge; ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if ($showFeedback): ?>
                                                <?php
                                                    if (!$preguntaTieneRespuesta) {
                                                        $feedbackPreguntaClase = 'text-warning';
                                                        $feedbackPregunta = 'No respondiste esta pregunta en el intento anterior.';
                                                    } elseif ($preguntaEsCorrecta) {
                                                        $feedbackPreguntaClase = 'text-success';
                                                        $feedbackPregunta = 'Bien resuelta. Puedes seguir con confianza.';
                                                    } else {
                                                        $feedbackPreguntaClase = 'text-danger';
                                                        $feedbackPregunta = 'Revisa la opcion marcada en verde y compara por que encaja mejor.';
                                                    }
                                                ?>
                                                <div class="small mt-3 <?php echo $feedbackPreguntaClase; ?>">
                                                    <?php echo htmlspecialchars($feedbackPregunta); ?>
                                                </div>
                                            <?php endif; ?>



                        <?php else: ?>
                                            <p class="text-muted">No hay opciones disponibles para esta pregunta.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif ($actividad->tipo_actividad === 'pronunciacion'): ?>
                            <!-- Actividad de pronunciacion -->

                            <div class="mb-4">
                                <p class="mb-3">Presiona el microfono y lee la frase en voz alta:</p>
                                
                                <?php foreach ($configActividad as $idx => $pregunta): ?>
                                    <div class="card mb-3 border-light shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title text-center mb-4 display-6">"<?php echo htmlspecialchars($pregunta->frase); ?>"</h5>
                                            
                                            <?php 
                                                $qId = $pregunta->id ?? "q$idx";
                                                $val = '';
                                                if (isset($respuestasUsuario) && is_array($respuestasUsuario)) {
                                                    $val = $respuestasUsuario[$qId] ?? '';
                                                }
                                                
                                                $isCorrect = false;
                                                $feedbackClass = '';
                                                $feedbackIcon = '';
                                                
                                                if ($showFeedback) {
                                                    // Simple normalization for comparison
                                                    $target = strtolower(trim(preg_replace('/[^\w\s]/', '', $pregunta->frase)));
                                                    $response = strtolower(trim(preg_replace('/[^\w\s]/', '', $val)));
                                                    // Allow some fuzzy matching or just strict for now
                                                    // Check if response contains the target phrase or is close enough
                                                    $isCorrect = ($target === $response) || (levenshtein($target, $response) < 3);
                                                    
                                                    if ($isCorrect) {
                                                        $feedbackClass = 'alert-success';
                                                        $feedbackIcon = '<i class="bi bi-check-circle-fill text-success fs-4 ms-2"></i>';
                                                    } else {
                                                        $feedbackClass = 'alert-danger';
                                                        $feedbackIcon = '<i class="bi bi-x-circle-fill text-danger fs-4 ms-2"></i>';
                                                    }
                                                }
                                            ?>
                                            
                                            <div class="text-center mb-3">
                                                <?php if (!$showFeedback): ?>
                                                    <button type="button" class="btn btn-outline-primary btn-lg rounded-circle p-3" 
                                                            onclick="startListening('<?php echo $qId; ?>')">
                                                        <i class="bi bi-mic-fill fs-3"></i>
                                                    </button>
                                                    <div id="recording-status-<?php echo $qId; ?>" class="text-muted mt-2 small" style="height: 20px;"></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="display-<?php echo $qId; ?>" class="form-label text-muted small">Lo que escuchamos:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control <?php echo $showFeedback ? ($isCorrect ? 'is-valid' : 'is-invalid') : ''; ?>" 
                                                           id="display-<?php echo $qId; ?>" 
                                                           value="<?php echo htmlspecialchars($val); ?>" 
                                                           readonly 
                                                           placeholder="Tu pronunciacion aparecera aqui...">
                                                    <?php echo $feedbackIcon; ?>
                                                </div>
                                                <input type="hidden" name="respuesta[<?php echo $qId; ?>]" id="respuesta-<?php echo $qId; ?>" value="<?php echo htmlspecialchars($val); ?>">
                                            </div>
                                            
                                            <?php if ($showFeedback && !$isCorrect): ?>
                                                <div class="mt-2 text-danger small">
                                                    <i class="bi bi-info-circle"></i> Intenta pronunciar mas claro.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <script>
                                function startListening(questionId) {
                                    if (!('webkitSpeechRecognition' in window)) {
                                        alert('Tu navegador no soporta reconocimiento de voz. Por favor usa Chrome o Edge.');
                                        return;
                                    }
                                    
                                    const recognition = new webkitSpeechRecognition();
                                    recognition.lang = '<?php echo $langCode; ?>';
                                    recognition.interimResults = false;
                                    recognition.maxAlternatives = 1;
                                    
                                    const statusEl = document.getElementById('recording-status-' + questionId);
                                    const displayEl = document.getElementById('display-' + questionId);
                                    const inputEl = document.getElementById('respuesta-' + questionId);
                                    
                                    statusEl.innerText = 'Escuchando...';
                                    statusEl.className = 'text-danger mt-2 small fw-bold';
                                    
                                    recognition.start();
                                    
                                    recognition.onresult = function(event) {
                                        const transcript = event.results[0][0].transcript;
                                        displayEl.value = transcript;
                                        inputEl.value = transcript;
                                        statusEl.innerText = 'Completado';
                                        statusEl.className = 'text-success mt-2 small';
                                    };
                                    
                                    recognition.onerror = function(event) {
                                        statusEl.innerText = 'Error: ' + event.error;
                                        statusEl.className = 'text-danger mt-2 small';
                                    };
                                    
                                    recognition.onend = function() {
                                        if (statusEl.innerText === 'Escuchando...') {
                                            statusEl.innerText = '';
                                        }
                                    };
                                }
                                
                                // Validation
                                document.querySelector('form').addEventListener('submit', function(e) {
                                    const inputs = document.querySelectorAll('input[type="hidden"][name^="respuesta"]');
                                    let incomplete = false;
                                    inputs.forEach(input => {
                                        if (!input.value.trim()) {
                                            incomplete = true;
                                        }
                                    });
                                    
                                    if (incomplete) {
                                        e.preventDefault();
                                        alert('Por favor, intenta pronunciar todas las frases antes de enviar.');
                                    }
                                });
                            </script>
                        <?php elseif ($actividad->tipo_actividad === 'respuesta_corta'): ?>
                            <!-- Actividad de Respuesta Corta -->
                            <?php 
                                $contenido = json_decode($actividad->contenido ?? '{}');
                                $preguntaTexto = $contenido->pregunta ?? null;
                                // Handle potentially encoded/empty user response
                                $val = $respuestasUsuario ?? '';
                                if (is_array($val)) $val = reset($val) ?: ''; // Fallback for array
                                if (is_string($val) && (str_starts_with($val, '"') || str_starts_with($val, '['))) {
                                    $decoded = json_decode($val, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $val = is_array($decoded) ? (reset($decoded) ?: '') : $decoded;
                                    }
                                }
                                $val = (string)$val;
                            ?>
                            <div class="mb-3">
                                <?php if ($preguntaTexto): ?>
                                    <h4 class="mb-3"><?php echo htmlspecialchars($preguntaTexto); ?></h4>
                                <?php endif; ?>
                                <label for="respuesta" class="form-label">Tu respuesta:</label>
                                <input type="text" class="form-control" id="respuesta" name="respuesta" 
                                       value="<?php echo htmlspecialchars($val); ?>"
                                       required maxlength="255" lang="<?php echo $langCode; ?>">
                                       
                                <?php if ($showFeedback): ?>
                                    <?php 
                                        // Calculate if correct for feedback display
                                        $isCorrect = false;
                                        // Simple client-side check logic matching backend
                                        $aceptadas = [];
                                        if (isset($contenido->respuesta_correcta)) $aceptadas[] = $contenido->respuesta_correcta;
                                        if (isset($contenido->variaciones) && is_array($contenido->variaciones)) {
                                            $aceptadas = array_merge($aceptadas, $contenido->variaciones);
                                        }
                                        if (isset($contenido->respuestas_correctas)) $aceptadas = array_merge($aceptadas, (array)$contenido->respuestas_correctas);
                                        
                                        $valNorm = mb_strtolower(trim($val), 'UTF-8');
                                        foreach ($aceptadas as $opcion) {
                                            if ($valNorm === mb_strtolower(trim($opcion), 'UTF-8')) {
                                                $isCorrect = true;
                                                break;
                                            }
                                        }
                                    ?>
                                    <div class="mt-2">
                                        <?php if ($isCorrect): ?>
                                            <div class="alert alert-success">
                                                <i class="bi bi-check-circle-fill me-2"></i> Correcto.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-danger">
                                                <i class="bi bi-x-circle-fill me-2"></i> Incorrecto.
                                                <?php if (isset($contenido->respuesta_correcta)): ?>
                                                    <br>La respuesta correcta es: <strong><?php echo htmlspecialchars($contenido->respuesta_correcta); ?></strong>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($actividad->tipo_actividad === 'verdadero_falso'): ?>
                            <!-- Actividad de Verdadero/Falso -->
                            <div class="mb-3">
                                <label class="form-label d-block mb-3">Selecciona la opcion correcta:</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="respuesta" id="vf_verdadero" value="Verdadero" required>
                                    <label class="form-check-label" for="vf_verdadero">
                                        Verdadero (True)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="respuesta" id="vf_falso" value="Falso" required>
                                    <label class="form-check-label" for="vf_falso">
                                        Falso (False)
                                    </label>
                                </div>
                            </div>
                        <?php elseif ($actividad->tipo_actividad === 'arrastrar_soltar'): ?>
                            <!-- Actividad de arrastrar y soltar -->
                            <div class="mb-4">
                                <?php 
                                    // Force fresh decode of content to ensure we have the correct object structure
                                    $contenido = json_decode($actividad->contenido ?? '{}');
                                    
                                    // Parse content
                                    $items = $contenido->items ?? [];
                                    $targets = $contenido->targets ?? [];
                                    
                                    // Parse existing answers
                                    $userAnswers = [];
                                    if (isset($respuestasUsuario)) {
                                        if (is_string($respuestasUsuario)) {
                                            $decoded = json_decode($respuestasUsuario, true);
                                            if (is_array($decoded)) $userAnswers = $decoded;
                                        } elseif (is_array($respuestasUsuario)) {
                                            $userAnswers = $respuestasUsuario;
                                        }
                                    }

                                    // Determine where each item sits
                                    // item_name => target_name (or 'pool' if not assigned)
                                    $itemLocations = [];
                                    foreach ($items as $item) {
                                        $itemLocations[$item] = $userAnswers[$item] ?? 'pool';
                                    }
                                    
                                    // Calculate feedback if needed
                                    $feedbackData = [];
                                    if ($showFeedback) {
                                        $solucion = $contenido->solucion ?? [];
                                        if (is_object($solucion)) $solucion = (array)$solucion;
                                        
                                        foreach ($items as $item) {
                                            $userTarget = $itemLocations[$item];
                                            $correctTarget = $solucion[$item] ?? null;
                                            
                                            if ($userTarget === 'pool') {
                                                $feedbackData[$item] = ''; // Not placed
                                            } elseif ($userTarget === $correctTarget) {
                                                $feedbackData[$item] = 'correct';
                                            } else {
                                                $feedbackData[$item] = 'incorrect';
                                            }
                                        }
                                    }
                                ?>

                                <input type="hidden" name="respuesta" id="drag-drop-response" value="<?php echo htmlspecialchars(json_encode($userAnswers)); ?>">
                                <div class="alert alert-light border mb-3">
                                    <strong>Modo tactil:</strong> en movil puedes tocar un elemento y luego tocar el contenedor donde quieres dejarlo.
                                </div>

                                <!-- Pool of items -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <i class="bi bi-box-seam"></i> Elementos disponibles
                                    </div>
                                    <div class="card-body">
                                        <div id="pool" class="drag-container d-flex flex-wrap align-content-start" 
                                             onclick="placeSelectedDragItem('pool')"
                                             ondrop="drop(event)" ondragover="allowDrop(event)">
                                            <?php foreach ($items as $item): ?>
                                                <?php if (($itemLocations[$item] ?? 'pool') === 'pool'): ?>
                                                    <div class="draggable-item" draggable="<?php echo $showFeedback ? 'false' : 'true'; ?>" 
                                                         ondragstart="drag(event)" onclick="selectDragItem('<?php echo 'item-' . md5($item); ?>')"
                                                         id="item-<?php echo md5($item); ?>" 
                                                         data-item="<?php echo htmlspecialchars($item); ?>">
                                                        <?php echo htmlspecialchars($item); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Targets -->
                                <div class="row">
                                    <?php foreach ($targets as $target): ?>
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                <div class="card-header fw-bold text-center bg-primary text-white">
                                                    <?php echo htmlspecialchars($target); ?>
                                                </div>
                                                <div class="card-body">
                                                    <div id="target-<?php echo md5($target); ?>" 
                                                         class="drag-container h-100" 
                                                         data-target="<?php echo htmlspecialchars($target); ?>"
                                                         onclick="placeSelectedDragItem('target-<?php echo md5($target); ?>')"
                                                         ondrop="drop(event)" ondragover="allowDrop(event)">
                                                        <?php foreach ($items as $item): ?>
                                                            <?php if (($itemLocations[$item] ?? '') === $target): ?>
                                                                <?php 
                                                                    $class = 'draggable-item';
                                                                    if ($showFeedback) {
                                                                        $class .= ' ' . ($feedbackData[$item] ?? '');
                                                                    }
                                                                ?>
                                                                <div class="<?php echo $class; ?>" 
                                                                     draggable="<?php echo $showFeedback ? 'false' : 'true'; ?>" 
                                                                     ondragstart="drag(event)" onclick="selectDragItem('<?php echo 'item-' . md5($item); ?>')"
                                                                     id="item-<?php echo md5($item); ?>" 
                                                                     data-item="<?php echo htmlspecialchars($item); ?>">
                                                                    <?php echo htmlspecialchars($item); ?>
                                                                    <?php if ($showFeedback && ($feedbackData[$item] ?? '') === 'incorrect'): ?>
                                                                        <i class="bi bi-x-circle ms-1"></i>
                                                                    <?php elseif ($showFeedback && ($feedbackData[$item] ?? '') === 'correct'): ?>
                                                                        <i class="bi bi-check-circle ms-1"></i>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <script>
                                let selectedDragItemId = null;

                                function clearSelectedDragItem() {
                                    document.querySelectorAll('.draggable-item.is-selected-touch').forEach(el => {
                                        el.classList.remove('is-selected-touch');
                                    });
                                    selectedDragItemId = null;
                                }

                                function selectDragItem(itemId) {
                                    if (<?php echo $showFeedback ? 'true' : 'false'; ?>) return;

                                    const item = document.getElementById(itemId);
                                    if (!item) return;

                                    if (selectedDragItemId === itemId) {
                                        clearSelectedDragItem();
                                        return;
                                    }

                                    clearSelectedDragItem();
                                    selectedDragItemId = itemId;
                                    item.classList.add('is-selected-touch');
                                }

                                function placeSelectedDragItem(containerId) {
                                    if (<?php echo $showFeedback ? 'true' : 'false'; ?>) return;
                                    if (!selectedDragItemId) return;

                                    const draggedElement = document.getElementById(selectedDragItemId);
                                    const targetEl = document.getElementById(containerId);

                                    if (!draggedElement || !targetEl || !targetEl.classList.contains('drag-container')) {
                                        return;
                                    }

                                    targetEl.appendChild(draggedElement);
                                    clearSelectedDragItem();
                                    updateDragDropResponse();
                                }

                                function allowDrop(ev) {
                                    if (<?php echo $showFeedback ? 'true' : 'false'; ?>) return;
                                    ev.preventDefault();
                                    ev.currentTarget.classList.add('drag-over');
                                }

                                function drag(ev) {
                                    if (<?php echo $showFeedback ? 'true' : 'false'; ?>) return;
                                    ev.dataTransfer.setData("text", ev.target.id);
                                    ev.dataTransfer.setData("item", ev.target.getAttribute('data-item'));
                                }

                                function drop(ev) {
                                    if (<?php echo $showFeedback ? 'true' : 'false'; ?>) return;
                                    ev.preventDefault();
                                    
                                    // Remove drag-over class from all containers
                                    document.querySelectorAll('.drag-container').forEach(el => el.classList.remove('drag-over'));
                                    
                                    var data = ev.dataTransfer.getData("text");
                                    var item = ev.dataTransfer.getData("item");
                                    var draggedElement = document.getElementById(data);
                                    
                                    // Find the drop target (handle dropping on child elements)
                                    let targetEl = ev.target;
                                    while (targetEl && !targetEl.classList.contains('drag-container')) {
                                        targetEl = targetEl.parentElement;
                                    }
                                    
                                    if (targetEl && draggedElement) {
                                        targetEl.appendChild(draggedElement);
                                        clearSelectedDragItem();
                                        updateDragDropResponse();
                                    }
                                }
                                
                                // Remove drag-over styling when leaving
                                document.querySelectorAll('.drag-container').forEach(el => {
                                    el.addEventListener('dragleave', function(ev) {
                                        this.classList.remove('drag-over');
                                    });
                                });

                                function updateDragDropResponse() {
                                    const response = {};
                                    // Find all target containers
                                    const targets = document.querySelectorAll('[data-target]');
                                    
                                    targets.forEach(targetContainer => {
                                        const targetName = targetContainer.getAttribute('data-target');
                                        // Find items inside this target
                                        const items = targetContainer.querySelectorAll('.draggable-item');
                                        items.forEach(item => {
                                            const itemName = item.getAttribute('data-item');
                                            response[itemName] = targetName;
                                        });
                                    });
                                    
                                    document.getElementById('drag-drop-response').value = JSON.stringify(response);
                                }
                            </script>
                        <?php elseif ($actividad->tipo_actividad === 'emparejamiento'): ?>
                            <!-- Actividad de Emparejamiento (Interactive) -->
                            <div class="mb-4">
                                <p class="mb-3">Haz clic en una opcion (derecha) y luego en el espacio correspondiente (izquierda) para emparejar:</p>
                                
                                <?php 
                                    // 1. Prepare options and answers
                                    $allOptions = [];
                                    if (isset($configActividad) && is_array($configActividad)) {
                                        foreach ($configActividad as $pair) {
                                            $pairObj = (object)$pair;
                                            if (!empty($pairObj->right)) {
                                                $allOptions[] = [
                                                    'id' => uniqid('opt_'),
                                                    'text' => $pairObj->right
                                                ];
                                            }
                                        }
                                    }
                                    
                                    if (!$showFeedback) {
                                        shuffle($allOptions);
                                    } else {
                                        sort($allOptions); // Or keep consistent order
                                    }

                                    // 2. Map answers to slots
                                    $slots = []; // pairId => chip(option)
                                    $usedOptionIds = [];
                                    
                                    if (isset($configActividad) && is_array($configActividad)) {
                                        foreach ($configActividad as $pair) {
                                            $pairObj = (object)$pair;
                                            $pairId = $pairObj->id;
                                            
                                            $userVal = '';
                                            if (isset($respuestasUsuario) && is_array($respuestasUsuario)) {
                                                $userVal = $respuestasUsuario[$pairId] ?? '';
                                            }
                                            
                                            if ($userVal !== '') {
                                                // Find matching option
                                                foreach ($allOptions as $opt) {
                                                    if ($opt['text'] === $userVal && !in_array($opt['id'], $usedOptionIds)) {
                                                        $slots[$pairId] = $opt;
                                                        $usedOptionIds[] = $opt['id'];
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                ?>
                                
                                <!-- Pairs and Slots -->
                                <div class="row mb-4">
                                    <?php if (isset($configActividad) && is_array($configActividad)): ?>
                                        <?php foreach ($configActividad as $pair): ?>
                                            <?php 
                                                $pairObj = (object)$pair; 
                                                $pairId = $pairObj->id;
                                                $assignedChip = $slots[$pairId] ?? null;
                                                
                                                $feedbackClass = '';
                                                $feedbackIcon = '';
                                                if ($showFeedback && isset($respuestasUsuario)) {
                                                    $userVal = $respuestasUsuario[$pairId] ?? '';
                                                    $esCorrecta = trim((string)$userVal) === trim((string)$pairObj->right);
                                                    if ($esCorrecta) {
                                                        $feedbackClass = 'border-success';
                                                        $feedbackIcon = '<i class="bi bi-check-circle-fill text-success ms-2"></i>';
                                                    } else {
                                                        $feedbackClass = 'border-danger';
                                                        $feedbackIcon = '<i class="bi bi-x-circle-fill text-danger ms-2"></i> <small class="text-danger">(Correcto: ' . htmlspecialchars($pairObj->right) . ')</small>';
                                                    }
                                                }
                                            ?>
                                            
                                            <div class="col-md-6 mb-2 text-end">
                                                <div class="p-2 border rounded bg-light fw-bold">
                                                    <?php echo htmlspecialchars($pairObj->left ?? ''); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div id="slot-<?php echo $pairId; ?>" 
                                                         class="p-2 border rounded w-100 <?php echo $feedbackClass; ?>" 
                                                         style="min-height: 45px; cursor: pointer; background-color: #f8f9fa;"
                                                         onclick="placeChip('<?php echo $pairId; ?>')">
                                                        <?php if ($assignedChip): ?>
                                                            <span class="badge bg-primary fs-6 chip-item" 
                                                                  data-id="<?php echo $assignedChip['id']; ?>" 
                                                                  data-text="<?php echo htmlspecialchars($assignedChip['text']); ?>"
                                                                  onclick="event.stopPropagation(); returnToPool(this);">
                                                                <?php echo htmlspecialchars($assignedChip['text']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php echo $feedbackIcon; ?>
                                                </div>
                                                <input type="hidden" name="respuesta[<?php echo $pairId; ?>]" id="input-<?php echo $pairId; ?>" value="<?php echo htmlspecialchars($assignedChip['text'] ?? ''); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Pool -->
                                <div class="card">
                                    <div class="card-header bg-light">Opciones disponibles:</div>
                                    <div class="card-body" id="pool-container" style="min-height: 60px;">
                                        <?php foreach ($allOptions as $opt): ?>
                                            <?php if (!in_array($opt['id'], $usedOptionIds)): ?>
                                                <span class="badge bg-secondary fs-6 me-2 mb-2 chip-item" 
                                                      style="cursor: pointer;" 
                                                      data-id="<?php echo $opt['id']; ?>" 
                                                      data-text="<?php echo htmlspecialchars($opt['text']); ?>"
                                                      onclick="selectChip(this)">
                                                    <?php echo htmlspecialchars($opt['text']); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                let selectedChip = null;
                                const isFeedback = <?php echo $showFeedback ? 'true' : 'false'; ?>;

                                function selectChip(element) {
                                    if (isFeedback) return;
                                    
                                    // Deselect current
                                    if (selectedChip) {
                                        selectedChip.style.boxShadow = 'none';
                                        selectedChip.classList.remove('border', 'border-primary');
                                    }
                                    
                                    // Select new
                                    selectedChip = element;
                                    selectedChip.style.boxShadow = '0 0 0 0.25rem rgba(13, 110, 253, 0.5)';
                                }

                                function placeChip(pairId) {
                                    if (isFeedback) return;
                                    if (!selectedChip) return;
                                    
                                    const slot = document.getElementById('slot-' + pairId);
                                    
                                    // If slot has item, return it to pool
                                    if (slot.children.length > 0) {
                                        const existing = slot.children[0];
                                        returnToPool(existing, false); 
                                    }
                                    
                                    // Move selected chip to slot
                                    slot.appendChild(selectedChip);
                                    selectedChip.classList.remove('bg-secondary');
                                    selectedChip.classList.add('bg-primary');
                                    selectedChip.style.boxShadow = 'none';
                                    
                                    // Update click handler to return
                                    selectedChip.setAttribute('onclick', 'event.stopPropagation(); returnToPool(this);');
                                    
                                    // Update input
                                    document.getElementById('input-' + pairId).value = selectedChip.getAttribute('data-text');
                                    
                                    selectedChip = null;
                                }

                                function returnToPool(element, clearSelected = true) {
                                    if (isFeedback) return;
                                    
                                    // Check if currently in a slot
                                    const parent = element.parentNode;
                                    if (parent.id && parent.id.startsWith('slot-')) {
                                        const pairId = parent.id.replace('slot-', '');
                                        document.getElementById('input-' + pairId).value = '';
                                    }
                                    
                                    const pool = document.getElementById('pool-container');
                                    pool.appendChild(element);
                                    
                                    element.classList.remove('bg-primary');
                                    element.classList.add('bg-secondary');
                                    element.style.boxShadow = 'none';
                                    element.setAttribute('onclick', 'selectChip(this)');
                                    
                                    if (clearSelected && selectedChip === element) {
                                        selectedChip = null;
                                    }
                                }
                            </script>
                        <?php elseif ($actividad->tipo_actividad === 'completar_oracion'): ?>
                            <!-- Actividad de completar oracion -->
                            <?php 
                                // Ensure array structure
                                $preguntas = is_array($configActividad) ? $configActividad : [$configActividad];
                            ?>
                            
                            <?php foreach ($preguntas as $index => $pregunta): ?>
                                <?php
                                    $oracion = $pregunta->oracion ?? '';
                                    $partes = explode('____', $oracion);
                                    $preguntaId = $pregunta->id ?? $index;
                                    
                                    // Prepare value for input
                                    $valorRespuesta = '';
                                    $feedbackClass = '';
                                    $feedbackIcon = '';
                                    $readonly = '';
                                    
                                    if ($showFeedback && isset($respuestasUsuario)) {
                                        // Try to get answer by ID or index
                                        if (is_array($respuestasUsuario)) {
                                            $valorRespuesta = $respuestasUsuario[$preguntaId] ?? $respuestasUsuario[$index] ?? '';
                                        } else {
                                            $valorRespuesta = $respuestasUsuario;
                                        }
                                        
                                        $esCorrecta = strtolower(trim((string)$valorRespuesta)) === strtolower(trim((string)($pregunta->respuesta_correcta ?? '')));
                                        if ($esCorrecta) {
                                            $feedbackClass = 'is-valid';
                                            $feedbackIcon = '<span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i> Correcto</span>';
                                        } else {
                                            $feedbackClass = 'is-invalid';
                                            $feedbackIcon = '<span class="text-danger ms-2"><i class="bi bi-x-circle-fill"></i> Incorrecto (Correcto: ' . htmlspecialchars($pregunta->respuesta_correcta ?? '') . ')</span>';
                                        }
                                        $readonly = 'readonly';
                                    }
                                ?>
                                <div class="mb-4 p-3 border rounded shadow-sm">
                                    <div class="text-center">
                                        <h4>
                                            <?php 
                                                if (count($partes) > 1) {
                                                    echo htmlspecialchars($partes[0]);
                                                    ?>
                                                    <input type="text" class="form-control d-inline-block w-auto mx-2 text-center fw-bold <?php echo $feedbackClass; ?>" 
                                                           name="respuesta[<?php echo htmlspecialchars($preguntaId); ?>]" 
                                                           value="<?php echo htmlspecialchars($valorRespuesta); ?>" 
                                                           required 
                                                           placeholder="..." 
                                                           lang="<?php echo $langCode; ?>"
                                                           <?php echo $readonly; ?>>
                                                    <?php
                                                    echo htmlspecialchars($partes[1]);
                                                } else {
                                                    echo htmlspecialchars($oracion);
                                                    ?>
                                                    <div class="mt-3">
                                                        <input type="text" class="form-control" name="respuesta[<?php echo htmlspecialchars($preguntaId); ?>]" value="<?php echo htmlspecialchars($valorRespuesta); ?>" required placeholder="Tu respuesta..." lang="<?php echo $langCode; ?>" <?php echo $readonly; ?>>
                                                    </div>
                                                    <?php
                                                }
                                            ?>
                                        </h4>
                                        <?php echo $feedbackIcon; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif ($actividad->tipo_actividad === 'ordenar_palabras'): ?>
                            <!-- Actividad de Ordenar Palabras -->
                            <?php foreach ($configActividad as $pregunta): ?>
                                <div class="mb-4">
                                    <p class="mb-3 fw-bold"><?php echo htmlspecialchars($pregunta->instruction ?? 'Ordena correctamente:'); ?></p>
                                    <?php if (!$showFeedback): ?>
                                        <div class="alert alert-light border mb-3">
                                            <strong>Modo tactil:</strong> toca las palabras para formar la frase y usa subir o bajar para ajustar el orden fino.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Contenedor de Respuesta -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">Tu respuesta:</div>
                                        <div class="card-body" id="sentence-container-<?php echo $pregunta->id; ?>" style="min-height: 60px;">
                                            <!-- Palabras seleccionadas iran aqui -->
                                            <?php
                                                // Determine user answer for this question
                                                $userAnswer = [];
                                                if ($showFeedback && isset($respuestasUsuario) && is_array($respuestasUsuario)) {
                                                    if (isset($respuestasUsuario[$pregunta->id])) {
                                                        $userAnswer = $respuestasUsuario[$pregunta->id];
                                                        // Handle if it's a JSON string
                                                        if (is_string($userAnswer)) {
                                                            $decoded = json_decode($userAnswer, true);
                                                            if (is_array($decoded)) $userAnswer = $decoded;
                                                            else $userAnswer = [$userAnswer]; // Fallback
                                                        }
                                                    } elseif (array_keys($respuestasUsuario) === range(0, count($respuestasUsuario) - 1)) {
                                                        // Legacy: single question, responses are the words directly
                                                        if (count($respuestasUsuario) > 0 && is_string($respuestasUsuario[0])) {
                                                            $userAnswer = $respuestasUsuario;
                                                        }
                                                    }
                                                }

                                                $usedIds = [];
                                                foreach ($userAnswer as $wordText) {
                                                    if (isset($pregunta->items) && is_array($pregunta->items)) {
                                                        foreach ($pregunta->items as $item) {
                                                            // Defensive check for item structure
                                                            $itemText = isset($item->text) ? $item->text : (is_string($item) ? $item : '');
                                                            $itemId = isset($item->id) ? $item->id : '';
                                                            
                                                            if ($itemText === $wordText && !in_array($itemId, $usedIds)) {
                                                                echo '<span class="badge bg-primary fs-6 me-2 mb-2 draggable-word" style="cursor: pointer;" data-id="' . $itemId . '" data-text="' . htmlspecialchars($itemText) . '" onclick="moveWord(this, \'pool\', \'' . $pregunta->id . '\')">' . htmlspecialchars($itemText) . '</span>';
                                                                $usedIds[] = $itemId;
                                                                break; 
                                                            }
                                                        }
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </div>

                                    <!-- Contenedor de Palabras Disponibles -->
                                    <div class="card">
                                        <div class="card-header bg-light">Palabras disponibles:</div>
                                        <div class="card-body" id="pool-container-<?php echo $pregunta->id; ?>" style="min-height: 60px;">
                                            <?php 
                                                if (isset($pregunta->items) && is_array($pregunta->items)) {
                                                    foreach ($pregunta->items as $item) {
                                                        $itemText = isset($item->text) ? $item->text : (is_string($item) ? $item : '');
                                                        $itemId = isset($item->id) ? $item->id : '';
                                                        
                                                        if (!in_array($itemId, $usedIds)) {
                                                            echo '<span class="badge bg-secondary fs-6 me-2 mb-2 draggable-word" style="cursor: pointer;" data-id="' . $itemId . '" data-text="' . htmlspecialchars($itemText) . '" onclick="moveWord(this, \'sentence\', \'' . $pregunta->id . '\')">' . htmlspecialchars($itemText) . '</span>';
                                                        }
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="respuesta[<?php echo $pregunta->id; ?>]" id="respuesta_ordenada_<?php echo $pregunta->id; ?>" value="<?php echo htmlspecialchars(json_encode($userAnswer)); ?>">
                                    
                                    <?php if ($showFeedback): ?>
                                        <div class="mt-3">
                                            <?php
                                                // Recalculate here for display
                                                $correctOrder = [];
                                                // Find original items from content
                                                $contenido = json_decode($actividad->contenido ?? '[]');
                                                // Normalize content
                                                $preguntasContenido = [];
                                                if (is_array($contenido) && isset($contenido[0]->items)) {
                                                    $preguntasContenido = $contenido;
                                                } elseif (isset($contenido->items)) {
                                                    $preguntasContenido = [$contenido];
                                                }
                                                
                                                // Find this question in content
                                                foreach ($preguntasContenido as $idx => $q) {
                                                    $qId = $q->id ?? "q$idx";
                                                    if ($qId === $pregunta->id || (count($preguntasContenido) === 1 && $idx === 0)) { // Fallback match
                                                        $correctOrder = $q->items ?? [];
                                                        break;
                                                    }
                                                }
                                                
                                                // Compare arrays
                                                if ($userAnswer === $correctOrder) {
                                                    echo '<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Correcto.</div>';
                                                } else {
                                                    echo '<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> Incorrecto. La respuesta correcta es: <strong>' . htmlspecialchars(implode(' ', $correctOrder)) . '</strong></div>';
                                                }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                                    <script>
                                function normalizeSentenceWordControls(questionId) {
                                    const sentenceContainer = document.getElementById('sentence-container-' + questionId);
                                    if (!sentenceContainer) return;

                                    Array.from(sentenceContainer.querySelectorAll('.draggable-word')).forEach((el, index, arr) => {
                                        if (el.nextElementSibling && el.nextElementSibling.classList.contains('word-order-controls')) {
                                            el.nextElementSibling.remove();
                                        }

                                        if (<?php echo $showFeedback ? 'true' : 'false'; ?>) {
                                            return;
                                        }

                                        const controls = document.createElement('span');
                                        controls.className = 'word-order-controls';
                                        controls.innerHTML =
                                            '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="shiftWord(this, \'up\', \'' + questionId + '\')"><i class="bi bi-arrow-up"></i></button>' +
                                            '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="shiftWord(this, \'down\', \'' + questionId + '\')"><i class="bi bi-arrow-down"></i></button>';
                                        sentenceContainer.insertBefore(controls, el.nextSibling);
                                    });
                                }

                                function shiftWord(button, direction, questionId) {
                                    const controls = button.closest('.word-order-controls');
                                    if (!controls) return;

                                    const word = controls.previousElementSibling;
                                    const sentenceContainer = document.getElementById('sentence-container-' + questionId);
                                    if (!word || !sentenceContainer) return;

                                    if (direction === 'up') {
                                        const previousControls = word.previousElementSibling;
                                        const previousWord = previousControls && previousControls.classList.contains('word-order-controls')
                                            ? previousControls.previousElementSibling
                                            : null;

                                        if (previousWord) {
                                            sentenceContainer.insertBefore(word, previousWord);
                                            sentenceContainer.insertBefore(controls, word.nextSibling);
                                        }
                                    } else {
                                        const nextWord = controls.nextElementSibling;
                                        const nextControls = nextWord && nextWord.nextElementSibling && nextWord.nextElementSibling.classList.contains('word-order-controls')
                                            ? nextWord.nextElementSibling
                                            : null;

                                        if (nextWord && nextControls) {
                                            sentenceContainer.insertBefore(nextWord, word);
                                            sentenceContainer.insertBefore(nextControls, controls);
                                        }
                                    }

                                    normalizeSentenceWordControls(questionId);
                                    updateHiddenInput(questionId);
                                }

                                function moveWord(element, target, questionId) {
                                    if (<?php echo $showFeedback ? 'true' : 'false'; ?>) return; 
                                    
                                    const sentenceContainer = document.getElementById('sentence-container-' + questionId);
                                    const poolContainer = document.getElementById('pool-container-' + questionId);
                                    
                                    if (target === 'sentence') {
                                        sentenceContainer.appendChild(element);
                                        element.classList.remove('bg-secondary');
                                        element.classList.add('bg-primary');
                                        element.setAttribute('onclick', "moveWord(this, 'pool', '" + questionId + "')");
                                    } else {
                                        poolContainer.appendChild(element);
                                        element.classList.remove('bg-primary');
                                        element.classList.add('bg-secondary');
                                        element.setAttribute('onclick', "moveWord(this, 'sentence', '" + questionId + "')");
                                    }
                                    
                                    normalizeSentenceWordControls(questionId);
                                    updateHiddenInput(questionId);
                                }
                                
                                function updateHiddenInput(questionId) {
                                    const words = [];
                                    document.querySelectorAll('#sentence-container-' + questionId + ' .draggable-word').forEach(el => {
                                        words.push(el.getAttribute('data-text'));
                                    });
                                    document.getElementById('respuesta_ordenada_' + questionId).value = JSON.stringify(words);
                                }

                                document.querySelector('form').addEventListener('submit', function(e) {
                                    const pools = document.querySelectorAll('[id^="pool-container-"]');
                                    let incomplete = false;
                                    pools.forEach(pool => {
                                        if (pool.children.length > 0) {
                                            incomplete = true;
                                        }
                                    });
                                    
                                    if (incomplete) {
                                        e.preventDefault();
                                        alert('Por favor, utiliza todas las palabras disponibles para completar las oraciones antes de enviar.');
                                    }
                                });

                                <?php foreach ($configActividad as $sortableQuestion): ?>
                                normalizeSentenceWordControls('<?php echo $sortableQuestion->id; ?>');
                                <?php endforeach; ?>
                            </script>

                        <?php elseif ($actividad->tipo_actividad === 'escritura'): ?>
                            <!-- Actividad de Escritura -->
                            <div class="mb-4">
                                <?php if (isset($configActividad->tema)): ?>
                                    <div class="alert alert-light border">
                                        <strong>Tema:</strong> <?php echo htmlspecialchars($configActividad->tema); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($configActividad->min_palabras)): ?>
                                    <p class="text-muted small mb-2">
                                        Minimo de palabras requeridas: <span id="min-words"><?php echo $configActividad->min_palabras; ?></span>
                                    </p>
                                <?php endif; ?>

                                <label for="respuesta" class="form-label">Escribe tu respuesta:</label>
                                <textarea class="form-control" id="respuesta" name="respuesta" rows="10" <?php echo $showFeedback ? 'readonly' : 'required'; ?> lang="<?php echo $langCode; ?>"><?php echo htmlspecialchars($respuestaExistente->respuesta_texto ?? ''); ?></textarea>
                                <div class="text-end text-muted small mt-1">
                                    Palabras: <span id="word-count">0</span>
                                </div>
                            </div>
                            
                            <script>
                                const writingField = document.getElementById('respuesta');
                                function updateWritingWordCount() {
                                    const text = writingField.value.trim();
                                    const count = text ? text.split(/\s+/).length : 0;
                                    document.getElementById('word-count').textContent = count;

                                    const min = <?php echo isset($configActividad->min_palabras) ? $configActividad->min_palabras : 0; ?>;
                                    if (min > 0 && count < min) {
                                        writingField.classList.add('is-invalid');
                                    } else {
                                        writingField.classList.remove('is-invalid');
                                    }
                                }

                                writingField.addEventListener('input', function() {
                                    const text = this.value.trim();
                                    const count = text ? text.split(/\s+/).length : 0;
                                    document.getElementById('word-count').textContent = count;
                                    
                                    const min = <?php echo isset($configActividad->min_palabras) ? $configActividad->min_palabras : 0; ?>;
                                    if (min > 0 && count < min) {
                                        this.classList.add('is-invalid');
                                    } else {
                                        this.classList.remove('is-invalid');
                                    }
                                });

                                updateWritingWordCount();
                            </script>

                        <?php elseif ($actividad->tipo_actividad === 'escucha'): ?>
                            <!-- Actividad de Escucha -->
                            <div class="mb-4">
                                <?php if (isset($configActividad->audio_url) && !empty($configActividad->audio_url)): ?>
                                    <div class="mb-4 text-center">
                                        <audio controls class="w-100">
                                            <source src="<?php echo htmlspecialchars($configActividad->audio_url); ?>" type="audio/mpeg">
                                            Tu navegador no soporta el elemento de audio.
                                        </audio>
                                    </div>
                                <?php elseif (isset($configActividad->texto_tts) || isset($configActividad->transcripcion)): ?>
                                    <div class="mb-4 text-center">
                                        <?php 
                                            $textToSpeak = $configActividad->texto_tts ?? $configActividad->transcripcion ?? '';
                                        ?>
                                        <button type="button" class="btn btn-primary btn-lg" onclick="speakText('<?php echo htmlspecialchars(addslashes($textToSpeak)); ?>', '<?php echo $langCode; ?>')">
                                            <i class="bi bi-volume-up-fill"></i> Reproducir Audio
                                        </button>
                                        <p class="text-muted mt-2"><small>Haz clic para escuchar el texto</small></p>
                                    </div>
                                    <script>
                                        function speakText(text, lang) {
                                            if ('speechSynthesis' in window) {
                                                window.speechSynthesis.cancel(); // Stop any current speech
                                                const utterance = new SpeechSynthesisUtterance(text);
                                                utterance.lang = lang;
                                                utterance.rate = 0.9; // Slightly slower for clarity
                                                window.speechSynthesis.speak(utterance);
                                            } else {
                                                alert('Tu navegador no soporta la sintesis de voz.');
                                            }
                                        }
                                    </script>
                                <?php endif; ?>

                                <label for="respuesta" class="form-label">Escribe lo que escuchaste o responde a la pregunta:</label>
                                <textarea class="form-control" id="respuesta" name="respuesta" rows="4" <?php echo $showFeedback ? 'readonly' : 'required'; ?> lang="<?php echo $langCode; ?>"><?php echo htmlspecialchars($respuestasUsuario[0] ?? ''); ?></textarea>
                                
                                <?php if ($showFeedback && isset($configActividad->transcripcion)): ?>
                                    <div class="mt-3 p-3 bg-light border rounded">
                                        <strong>Transcripcion correcta:</strong>
                                        <p class="mb-0 mt-1 fst-italic"><?php echo htmlspecialchars($configActividad->transcripcion); ?></p>
                                        
                                        <?php 
                                            // Comparacion simple para mostrar feedback visual inmediato
                                            $respUser = trim(strip_tags($respuestasUsuario[0] ?? ''));
                                            $transcrip = trim($configActividad->transcripcion);
                                            // Normalizar para comparacion
                                            $respUserNorm = strtolower(preg_replace('/[.,;!?]/', '', $respUser));
                                            $transcripNorm = strtolower(preg_replace('/[.,;!?]/', '', $transcrip));
                                            
                                            if ($respUserNorm === $transcripNorm && $respUserNorm !== '') {
                                                echo '<div class="mt-2 text-success"><i class="bi bi-check-circle-fill"></i> Correcto. Tu respuesta coincide exactamente.</div>';
                                            } elseif (levenshtein($respUserNorm, $transcripNorm) < 5) {
                                                echo '<div class="mt-2 text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Casi correcto. Revisa la ortografia o puntuacion.</div>';
                                            } else {
                                                echo '<div class="mt-2 text-danger"><i class="bi bi-x-circle-fill"></i> Tu respuesta es diferente.</div>';
                                            }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>
                            <!-- Formulario generico por defecto -->
                            <div class="mb-3">
                                <label for="respuesta" class="form-label">Tu respuesta:</label>
                                <textarea class="form-control" id="respuesta" name="respuesta" rows="3" required lang="<?php echo $langCode; ?>"></textarea>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$showFeedback): ?>
                            <div class="d-flex gap-2 flex-wrap mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Enviar respuesta
                                </button>
                                <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>

                    <?php if (isset($respuestaExistente) && $respuestaExistente): ?>
                        
                        <?php if (isset($respuestaExistente->comentarios) && !empty($respuestaExistente->comentarios)): ?>
                            <div class="alert alert-info mt-3">
                                <h5><i class="bi bi-chat-square-text"></i> Comentarios del profesor</h5>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($respuestaExistente->comentarios)); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <?php if (!$isRetry): ?>
                                    <a href="?retry=1" class="btn btn-success me-2">
                                        <i class="bi bi-arrow-repeat"></i> Practicar otra vez
                                    </a>
                                    <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver a la leccion
                                    </a>
                                <?php else: ?>
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle"></i> Estas en modo practica.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($isRetry): ?>
                                <a href="?" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Ver respuesta anterior
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $issueReportTitle = 'Reportar un fallo en esta actividad';
            $issueReportAction = url('/reportar-fallo');
            $issueReportContextType = 'actividad';
            $issueReportContextId = 'actividad_' . (int) $actividad->id;
            $issueReportReturnTo = $_SERVER['REQUEST_URI'] ?? url('/estudiante');
            $issueReportCourseId = (int) $leccion->curso_id;
            $issueReportLessonId = (int) $leccion->id;
            $issueReportActivityId = (int) $actividad->id;
            $issueReportDescriptionPlaceholder = 'Que paso, en que pregunta y como lo reproduces.';
            require __DIR__ . '/../partials/issue_report_panel.php';
            ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
