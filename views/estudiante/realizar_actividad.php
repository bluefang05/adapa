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
    $sourceLabel = app_url_host_label($resource['url'] ?? '');
    ?>
    <div class="support-resource-panel mb-4">
        <div class="support-resource-header">
            <div>
                <div class="support-resource-eyebrow"><?php echo htmlspecialchars($kindLabel); ?></div>
                <h3 class="support-resource-title"><?php echo $title; ?></h3>
                <div class="small text-muted mt-1">Fuente: <?php echo htmlspecialchars($sourceLabel); ?></div>
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
                $activitySummaryCta = $activitySummaryCta ?? ($showFeedback ? 'Continuar' : 'Volver a la leccion');
                $activityStateLabel = $isRetry ? 'Modo practica' : ($showFeedback ? 'Respuesta guardada' : 'Lista para responder');
                $activityStateTone = $isRetry ? 'badge-accent' : ($showFeedback ? 'success' : 'info');
            ?>
            <div class="page-hero activity-hero mb-4">
                <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Actividad activa</span>
                <h1 class="page-title"><?php echo htmlspecialchars($actividad->titulo); ?></h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($actividad->descripcion ?: 'Completa la actividad y revisa tu avance dentro de la leccion.'); ?></p>
                <?php if ($isRetry && isset($respuestaExistente) && $respuestaExistente): ?>
                    <div class="hero-actions">
                        <a href="?" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Ver respuesta anterior
                        </a>
                    </div>
                <?php endif; ?>
                <div class="activity-meta-row">
                    <span class="soft-badge info"><i class="bi bi-grid"></i> <?php echo htmlspecialchars(studentActivityTypeLabel($actividad->tipo_actividad)); ?></span>
                    <span class="soft-badge <?php echo htmlspecialchars($activityStateTone); ?>"><i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($activityStateLabel); ?></span>
                    <span class="soft-badge"><i class="bi bi-journal-text"></i> Leccion <?php echo (int) $leccion->orden; ?></span>
                    <?php if (!empty($actividad->puntos_maximos)): ?>
                        <span class="soft-badge"><i class="bi bi-award"></i> <?php echo (int) $actividad->puntos_maximos; ?> pts</span>
                    <?php endif; ?>
                    <?php if (!empty($actividad->tiempo_limite_minutos)): ?>
                        <span class="soft-badge warning"><i class="bi bi-clock"></i> <?php echo (int) $actividad->tiempo_limite_minutos; ?> min</span>
                    <?php endif; ?>
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
                        $supportSectionsCount = ($supportResource ? 1 : 0)
                            + (!empty($activityGuidance) ? 1 : 0);
                    ?>

                    <?php if (!empty($actividad->instrucciones)): ?>
                        <div class="alert context-note mb-4">
                            <div class="fw-semibold mb-2">Como abordar esta actividad</div>
                            <div class="small text-muted">
                                <?php echo htmlspecialchars($actividad->instrucciones); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($supportSectionsCount > 0): ?>
                        <section class="mb-4">
                            <details class="panel activity-details-card">
                                <summary class="activity-details-summary">
                                    <div>
                                        <div class="metric-label">Ayuda opcional</div>
                                        <div class="fw-semibold mt-1">Ayuda y atajos para destrabarte</div>
                                        <div class="small text-muted mt-1">Abre esta seccion solo si necesitas contexto extra antes de responder.</div>
                                    </div>
                                    <span class="soft-badge"><?php echo (int) $supportSectionsCount; ?> bloques</span>
                                </summary>
                                <div class="panel-body pt-0 activity-details-body">
                                    <?php if ($supportResource): ?>
                                        <?php renderStudentSupportResource($supportResource); ?>
                                    <?php endif; ?>

                                    <?php if (!empty($activityGuidance)): ?>
                                        <section>
                                            <div class="split-head mb-3">
                                                <div>
                                                    <h2 class="h5 mb-1">Como sacarle mas provecho</h2>
                                                    <div class="small text-muted">Pistas cortas para resolver mejor sin recargar la pantalla principal.</div>
                                                </div>
                                            </div>
                                            <div class="activity-guidance-list">
                                                <?php foreach ($activityGuidance as $guide): ?>
                                                    <article class="activity-guidance-item">
                                                        <div class="stack-item-title"><?php echo htmlspecialchars($guide['title'] ?? 'Guia'); ?></div>
                                                        <div class="stack-item-subtitle"><?php echo htmlspecialchars($guide['copy'] ?? ''); ?></div>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        </section>
                                    <?php endif; ?>
                                </div>
                            </details>
                        </section>
                    <?php endif; ?>
                    <form action="<?php echo url('/estudiante/actividades/' . $actividad->id . '/responder'); ?>" method="post">
                        <?php echo csrf_input(); ?>
                        <?php if (($actividad->tipo_actividad === 'opcion_multiple' || $actividad->tipo_actividad === 'verdadero_falso') && !empty($configActividad)): ?>
                            <!-- Actividad de opcion multiple / verdadero o falso -->
                            <?php foreach ($configActividad as $preguntaIndex => $pregunta): ?>
                                    <div class="activity-question-card mb-4">
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
                            <?php
                                $normalizePron = static function(string $text): string {
                                    $text = mb_strtolower(trim($text), 'UTF-8');
                                    $text = strtr($text, [
                                        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
                                        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                                        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
                                        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                                        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
                                        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                                    ]);
                                    $text = preg_replace('/[^a-z0-9\\s]/u', ' ', $text);
                                    $text = preg_replace('/\\s+/u', ' ', $text);
                                    return trim((string) $text);
                                };
                                $tokenizePron = static function(string $text) use ($normalizePron): array {
                                    $normalized = $normalizePron($text);
                                    return $normalized === '' ? [] : array_values(array_filter(explode(' ', $normalized), static fn($token) => $token !== ''));
                                };
                                $comparePron = static function(string $target, string $response) use ($normalizePron, $tokenizePron): array {
                                    $targetNorm = $normalizePron($target);
                                    $responseNorm = $normalizePron($response);
                                    if ($targetNorm === '' || $responseNorm === '') {
                                        return ['ratio' => 0.0, 'missing' => $tokenizePron($target)];
                                    }

                                    $targetTokens = $tokenizePron($target);
                                    $responseTokens = $tokenizePron($response);
                                    $matched = 0;
                                    $missing = [];
                                    $targetFreq = array_count_values($targetTokens);
                                    $responseFreq = array_count_values($responseTokens);
                                    foreach ($targetFreq as $token => $count) {
                                        $present = min($count, $responseFreq[$token] ?? 0);
                                        $matched += $present;
                                        if ($present < $count) {
                                            $missing[] = $token;
                                        }
                                    }
                                    similar_text($targetNorm, $responseNorm, $percent);
                                    $maxLen = max(strlen($targetNorm), strlen($responseNorm));
                                    $levScore = $maxLen > 0 ? 1 - (min($maxLen, levenshtein($targetNorm, $responseNorm)) / $maxLen) : 0.0;
                                    $stringScore = (($percent / 100) * 0.60) + ($levScore * 0.40);
                                    $recall = $matched / max(1, count($targetTokens));
                                    $precision = $matched / max(1, count($responseTokens));
                                    $completitud = min(1, count($responseTokens) / max(1, count($targetTokens)));
                                    $ratio = $targetNorm === $responseNorm
                                        ? 1.0
                                        : (($recall * 0.45) + ($precision * 0.15) + ($stringScore * 0.30) + ($completitud * 0.10));

                                    return [
                                        'ratio' => max(0.0, min(1.0, $ratio)),
                                        'missing' => array_values(array_unique($missing)),
                                    ];
                                };
                                $pronunciationSequence = array_values(array_map(static function($item): array {
                                    return [
                                        'text' => (string) ($item->texto_tts ?? $item->frase ?? ''),
                                        'normal_rate' => (float) ($item->tts_rate ?? 0.88),
                                        'slow_rate' => (float) ($item->tts_rate_slow ?? 0.72),
                                        'pitch' => (float) ($item->tts_pitch ?? 1.0),
                                    ];
                                }, (array) $configActividad));
                            ?>
                            <div class="mb-4">
                                <p class="mb-3">Escucha el modelo, activa el microfono y repite cada frase con calma. Busca claridad, no velocidad.</p>
                                <?php if (!$showFeedback && !empty($pronunciationSequence)): ?>
                                    <div class="responsive-actions mb-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick='playPronunciationSequence(<?php echo json_encode($pronunciationSequence, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, "normal")'>
                                            <i class="bi bi-collection-play"></i> Secuencia
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick='playPronunciationSequence(<?php echo json_encode($pronunciationSequence, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, "slow")'>
                                            <i class="bi bi-hourglass-split"></i> Secuencia lenta
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php foreach ($configActividad as $idx => $pregunta): ?>
                                    <div class="card mb-3 border-light shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title text-center mb-3 display-6">"<?php echo htmlspecialchars($pregunta->frase); ?>"</h5>
                                            <div class="text-center mb-3">
                                                <div class="btn-group btn-group-sm" role="group" aria-label="Controles de reproduccion">
                                                    <button type="button" class="btn btn-outline-secondary" onclick='playPronunciationTarget(<?php echo json_encode((string) ($pregunta->texto_tts ?? $pregunta->frase), JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode((float) ($pregunta->tts_rate ?? 0.88)); ?>, <?php echo json_encode((float) ($pregunta->tts_pitch ?? 1.0)); ?>)'>
                                                        <i class="bi bi-volume-up"></i> Modelo
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" onclick='playPronunciationTarget(<?php echo json_encode((string) ($pregunta->texto_tts ?? $pregunta->frase), JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode((float) ($pregunta->tts_rate_slow ?? 0.72)); ?>, <?php echo json_encode((float) ($pregunta->tts_pitch ?? 1.0)); ?>)'>
                                                        <i class="bi bi-hourglass-split"></i> Lento
                                                    </button>
                                                </div>
                                            </div>

                                            <?php 
                                                $qId = $pregunta->id ?? "q$idx";
                                                $val = '';
                                                if (isset($respuestasUsuario) && is_array($respuestasUsuario)) {
                                                    $val = $respuestasUsuario[$qId] ?? '';
                                                }

                                                $pronAnalysis = $showFeedback ? $comparePron((string) $pregunta->frase, (string) $val) : ['ratio' => null, 'missing' => []];
                                                $pronRatio = $pronAnalysis['ratio'];
                                                $feedbackIcon = '';
                                                $feedbackCopy = '';
                                                $inputClass = '';

                                                if ($showFeedback) {
                                                    if ($pronRatio >= 0.90) {
                                                        $inputClass = 'is-valid';
                                                        $feedbackIcon = '<i class="bi bi-check-circle-fill text-success fs-4 ms-2"></i>';
                                                        $feedbackCopy = 'Muy bien. La frase se reconocio con buena claridad.';
                                                    } elseif ($pronRatio >= 0.65) {
                                                        $feedbackIcon = '<i class="bi bi-exclamation-circle-fill text-warning fs-4 ms-2"></i>';
                                                        $feedbackCopy = 'Base correcta, pero todavia conviene limpiar algunas palabras o el final de la frase.';
                                                    } else {
                                                        $inputClass = 'is-invalid';
                                                        $feedbackIcon = '<i class="bi bi-x-circle-fill text-danger fs-4 ms-2"></i>';
                                                        $feedbackCopy = 'Aun falta claridad. Repite mas lento y cuida mejor los bloques clave.';
                                                    }
                                                }
                                            ?>

                                            <?php if (!empty($pregunta->focos) || !empty($pregunta->pista)): ?>
                                                <div class="alert context-note mb-3">
                                                    <?php if (!empty($pregunta->focos)): ?>
                                                        <div class="small mb-1"><strong>Foco:</strong> <?php echo htmlspecialchars(implode(' | ', array_map('strval', (array) $pregunta->focos))); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($pregunta->pista)): ?>
                                                        <div class="small mb-0"><?php echo htmlspecialchars((string) $pregunta->pista); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($pregunta->practice_goal)): ?>
                                                        <div class="small mt-1"><strong>Meta oral:</strong> <?php echo htmlspecialchars((string) $pregunta->practice_goal); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="text-center mb-3">
                                                <?php if (!$showFeedback): ?>
                                                    <button type="button" class="btn btn-outline-primary btn-lg rounded-circle p-3" onclick="startListening('<?php echo $qId; ?>')">
                                                        <i class="bi bi-mic-fill fs-3"></i>
                                                    </button>
                                                    <div id="recording-status-<?php echo $qId; ?>" class="text-muted mt-2 small recording-status-slot"></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="display-<?php echo $qId; ?>" class="form-label text-muted small">Lo que reconocio el sistema:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control <?php echo htmlspecialchars($inputClass); ?>" 
                                                           id="display-<?php echo $qId; ?>" 
                                                           value="<?php echo htmlspecialchars($val); ?>" 
                                                           readonly 
                                                           placeholder="Tu pronunciacion aparecera aqui...">
                                                    <?php echo $feedbackIcon; ?>
                                                </div>
                                                <input type="hidden" name="respuesta[<?php echo $qId; ?>]" id="respuesta-<?php echo $qId; ?>" value="<?php echo htmlspecialchars($val); ?>">
                                            </div>
                                            
                                            <?php if ($showFeedback): ?>
                                                <div class="mt-2 small <?php echo $pronRatio >= 0.90 ? 'text-success' : ($pronRatio >= 0.65 ? 'text-warning' : 'text-danger'); ?>">
                                                    <i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($feedbackCopy); ?>
                                                    <?php if (!empty($pronAnalysis['missing']) && $pronRatio < 0.90): ?>
                                                        <span> Revisa: <?php echo htmlspecialchars(implode(', ', array_slice((array) $pronAnalysis['missing'], 0, 3))); ?>.</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <script>
                                let pronunciationPlaybackToken = 0;

                                function playSpeechModel(text, lang, rate, pitch) {
                                    if (!('speechSynthesis' in window)) {
                                        alert('Tu navegador no soporta sintesis de voz.');
                                        return;
                                    }
                                    window.speechSynthesis.cancel();
                                    const utterance = new SpeechSynthesisUtterance(text);
                                    utterance.lang = lang;
                                    utterance.rate = rate || 0.88;
                                    utterance.pitch = pitch || 1;
                                    window.speechSynthesis.speak(utterance);
                                }

                                function playPronunciationTarget(text, lang, rate, pitch) {
                                    pronunciationPlaybackToken += 1;
                                    playSpeechModel(text, lang, rate, pitch);
                                }

                                function playPronunciationSequence(items, lang, mode) {
                                    if (!('speechSynthesis' in window)) {
                                        alert('Tu navegador no soporta sintesis de voz.');
                                        return;
                                    }

                                    pronunciationPlaybackToken += 1;
                                    const token = pronunciationPlaybackToken;
                                    const sequence = Array.isArray(items) ? items.filter(item => item && item.text) : [];
                                    if (!sequence.length) {
                                        return;
                                    }

                                    window.speechSynthesis.cancel();
                                    const playNext = function(index) {
                                        if (token !== pronunciationPlaybackToken || index >= sequence.length) {
                                            return;
                                        }

                                        const item = sequence[index];
                                        const utterance = new SpeechSynthesisUtterance(item.text);
                                        utterance.lang = lang;
                                        utterance.rate = mode === 'slow' ? (item.slow_rate || 0.72) : (item.normal_rate || 0.88);
                                        utterance.pitch = item.pitch || 1;
                                        utterance.onend = function() {
                                            window.setTimeout(function() {
                                                playNext(index + 1);
                                            }, 420);
                                        };
                                        window.speechSynthesis.speak(utterance);
                                    };

                                    playNext(0);
                                }

                                function startListening(questionId) {
                                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                                    if (!SpeechRecognition) {
                                        alert('Tu navegador no soporta reconocimiento de voz. Por favor usa Chrome o Edge.');
                                        return;
                                    }
                                    
                                    const recognition = new SpeechRecognition();
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
                                            <div class="feedback correct">
                                                <i class="bi bi-check-circle-fill me-2"></i> Correcto.
                                            </div>
                                        <?php else: ?>
                                            <div class="feedback incorrect">
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
                                <div class="alert context-note mb-3">
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
                                                         class="p-2 border rounded w-100 selection-slot <?php echo $feedbackClass; ?>" 
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
                                    <div class="card-body selection-pool" id="pool-container">
                                        <?php foreach ($allOptions as $opt): ?>
                                            <?php if (!in_array($opt['id'], $usedOptionIds)): ?>
                                                <span class="badge bg-secondary fs-6 me-2 mb-2 chip-item clickable-word" 
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
                                        <div class="alert context-note mb-3">
                                            <strong>Modo tactil:</strong> toca las palabras para formar la frase y usa subir o bajar para ajustar el orden fino.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Contenedor de Respuesta -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">Tu respuesta:</div>
                                        <div class="card-body selection-pool" id="sentence-container-<?php echo $pregunta->id; ?>">
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
                                                                echo '<span class="badge bg-primary fs-6 me-2 mb-2 draggable-word clickable-word" data-id="' . $itemId . '" data-text="' . htmlspecialchars($itemText) . '" onclick="moveWord(this, \'pool\', \'' . $pregunta->id . '\')">' . htmlspecialchars($itemText) . '</span>';
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
                                        <div class="card-body selection-pool" id="pool-container-<?php echo $pregunta->id; ?>">
                                            <?php 
                                                if (isset($pregunta->items) && is_array($pregunta->items)) {
                                                    foreach ($pregunta->items as $item) {
                                                        $itemText = isset($item->text) ? $item->text : (is_string($item) ? $item : '');
                                                        $itemId = isset($item->id) ? $item->id : '';
                                                        
                                                        if (!in_array($itemId, $usedIds)) {
                                                            echo '<span class="badge bg-secondary fs-6 me-2 mb-2 draggable-word clickable-word" data-id="' . $itemId . '" data-text="' . htmlspecialchars($itemText) . '" onclick="moveWord(this, \'sentence\', \'' . $pregunta->id . '\')">' . htmlspecialchars($itemText) . '</span>';
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
                                                    echo '<div class="feedback correct"><i class="bi bi-check-circle-fill"></i> Correcto.</div>';
                                                } else {
                                                    echo '<div class="feedback incorrect"><i class="bi bi-x-circle-fill"></i> Incorrecto. La respuesta correcta es: <strong>' . htmlspecialchars(implode(' ', $correctOrder)) . '</strong></div>';
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
                                    <div class="alert context-note">
                                        <strong>Tema:</strong> <?php echo htmlspecialchars($configActividad->tema); ?>
                                        <?php if (!empty($configActividad->criterios) || !empty($configActividad->palabras_clave) || !empty($configActividad->conectores_sugeridos) || !empty($configActividad->estructura_sugerida) || !empty($configActividad->movimientos_clave) || !empty($configActividad->patrones_morfosintacticos) || !empty($configActividad->diagnosticos_gramaticales) || !empty($configActividad->modelo_inicio) || !empty($configActividad->marcadores_registro) || !empty($configActividad->errores_a_evitar) || !empty($configActividad->focos_gramaticales) || !empty($configActividad->errores_gramaticales_comunes) || !empty($configActividad->registro)): ?>
                                            <div class="mt-3">
                                                <?php if (!empty($configActividad->registro)): ?>
                                                    <div class="small mb-1"><strong>Registro esperado:</strong> <?php echo htmlspecialchars(ucfirst((string) $configActividad->registro)); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->focos_gramaticales)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Focos gramaticales</div>
                                                    <ul class="small mb-2">
                                                        <?php foreach ((array) $configActividad->focos_gramaticales as $foco): ?>
                                                            <li><?php echo htmlspecialchars((string) $foco); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->criterios)): ?>
                                                    <div class="small fw-semibold mb-1">Debe cubrir</div>
                                                    <ul class="small mb-2">
                                                        <?php foreach ((array) $configActividad->criterios as $criterio): ?>
                                                            <li><?php echo htmlspecialchars((string) $criterio); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->palabras_clave)): ?>
                                                    <div class="small mb-1"><strong>Piezas utiles:</strong> <?php echo htmlspecialchars(implode(' | ', array_map('strval', (array) $configActividad->palabras_clave))); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->conectores_sugeridos)): ?>
                                                    <div class="small"><strong>Conectores sugeridos:</strong> <?php echo htmlspecialchars(implode(' | ', array_map('strval', (array) $configActividad->conectores_sugeridos))); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->marcadores_registro)): ?>
                                                    <div class="small mt-2"><strong>Marcadores de registro:</strong> <?php echo htmlspecialchars(implode(' | ', array_map('strval', (array) $configActividad->marcadores_registro))); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->estructura_sugerida)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Recorrido sugerido</div>
                                                    <ul class="small mb-2">
                                                        <?php foreach ((array) $configActividad->estructura_sugerida as $paso): ?>
                                                            <li><?php echo htmlspecialchars((string) $paso); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->movimientos_clave)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Movimientos clave</div>
                                                    <ul class="small mb-2">
                                                        <?php foreach ((array) $configActividad->movimientos_clave as $movimiento): ?>
                                                            <?php $movimientoLabel = is_object($movimiento) ? ($movimiento->label ?? '') : ((is_array($movimiento) && isset($movimiento['label'])) ? $movimiento['label'] : ''); ?>
                                                            <?php if ($movimientoLabel !== ''): ?>
                                                                <li><?php echo htmlspecialchars((string) $movimientoLabel); ?></li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->patrones_morfosintacticos)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Patrones gramaticales a cuidar</div>
                                                    <ul class="small mb-2">
                                                        <?php foreach ((array) $configActividad->patrones_morfosintacticos as $patron): ?>
                                                            <?php $patronLabel = is_object($patron) ? ($patron->label ?? '') : ((is_array($patron) && isset($patron['label'])) ? $patron['label'] : ''); ?>
                                                            <?php if ($patronLabel !== ''): ?>
                                                                <li><?php echo htmlspecialchars((string) $patronLabel); ?></li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->diagnosticos_gramaticales)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Controles delicados</div>
                                                    <ul class="small mb-2">
                                                        <?php foreach ((array) $configActividad->diagnosticos_gramaticales as $diagnostico): ?>
                                                            <?php $diagnosticoLabel = is_object($diagnostico) ? ($diagnostico->label ?? '') : ((is_array($diagnostico) && isset($diagnostico['label'])) ? $diagnostico['label'] : ''); ?>
                                                            <?php if ($diagnosticoLabel !== ''): ?>
                                                                <li><?php echo htmlspecialchars((string) $diagnosticoLabel); ?></li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->modelo_inicio)): ?>
                                                    <div class="small mt-2"><strong>Arranque util:</strong> <?php echo htmlspecialchars((string) $configActividad->modelo_inicio); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->errores_a_evitar)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Evita esto</div>
                                                    <ul class="small mb-0">
                                                        <?php foreach ((array) $configActividad->errores_a_evitar as $alerta): ?>
                                                            <li><?php echo htmlspecialchars((string) $alerta); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($configActividad->errores_gramaticales_comunes)): ?>
                                                    <div class="small fw-semibold mt-2 mb-1">Vigila estos errores</div>
                                                    <ul class="small mb-0">
                                                        <?php foreach ((array) $configActividad->errores_gramaticales_comunes as $alerta): ?>
                                                            <li><?php echo htmlspecialchars((string) $alerta); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
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
                                <?php
                                    $normalizeListen = static function(string $text): string {
                                        $text = mb_strtolower(trim($text), 'UTF-8');
                                        $text = strtr($text, [
                                            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
                                            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                                            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
                                            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                                            'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
                                            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                                        ]);
                                        $text = preg_replace('/[^a-z0-9\\s]/u', ' ', $text);
                                        $text = preg_replace('/\\s+/u', ' ', $text);
                                        return trim((string) $text);
                                    };
                                    $tokenizeListen = static function(string $text) use ($normalizeListen): array {
                                        $normalized = $normalizeListen($text);
                                        return $normalized === '' ? [] : array_values(array_filter(explode(' ', $normalized), static fn($token) => $token !== ''));
                                    };
                                    $compareListen = static function(string $target, string $response) use ($normalizeListen, $tokenizeListen): float {
                                        $targetNorm = $normalizeListen($target);
                                        $responseNorm = $normalizeListen($response);
                                        if ($targetNorm === '' || $responseNorm === '') {
                                            return 0.0;
                                        }
                                        $targetTokens = $tokenizeListen($target);
                                        $responseTokens = $tokenizeListen($response);
                                        $targetFreq = array_count_values($targetTokens);
                                        $responseFreq = array_count_values($responseTokens);
                                        $matched = 0;
                                        foreach ($targetFreq as $token => $count) {
                                            $matched += min($count, $responseFreq[$token] ?? 0);
                                        }
                                        similar_text($targetNorm, $responseNorm, $percent);
                                        $maxLen = max(strlen($targetNorm), strlen($responseNorm));
                                        $levScore = $maxLen > 0 ? 1 - (min($maxLen, levenshtein($targetNorm, $responseNorm)) / $maxLen) : 0.0;
                                        $stringScore = (($percent / 100) * 0.60) + ($levScore * 0.40);
                                        $recall = $matched / max(1, count($targetTokens));
                                        $precision = $matched / max(1, count($responseTokens));
                                        $completitud = min(1, count($responseTokens) / max(1, count($targetTokens)));
                                        return max(0.0, min(1.0, ($recall * 0.55) + ($precision * 0.15) + ($stringScore * 0.20) + ($completitud * 0.10)));
                                    };
                                    $listeningPrompts = (isset($configActividad->preguntas) && is_array($configActividad->preguntas)) ? $configActividad->preguntas : [];
                                    $listeningSequence = array_values(array_map(static function($prompt): array {
                                        return [
                                            'text' => (string) ($prompt->texto_tts ?? $prompt->transcripcion ?? ''),
                                            'normal_rate' => (float) ($prompt->tts_rate ?? 0.9),
                                            'slow_rate' => (float) ($prompt->tts_rate_slow ?? 0.75),
                                            'pitch' => (float) ($prompt->tts_pitch ?? 1.0),
                                        ];
                                    }, $listeningPrompts));
                                ?>

                                <script>
                                    let listeningPlaybackToken = 0;

                                    function speakText(text, lang, rate, pitch) {
                                        if ('speechSynthesis' in window) {
                                            window.speechSynthesis.cancel();
                                            const utterance = new SpeechSynthesisUtterance(text);
                                            utterance.lang = lang;
                                            utterance.rate = rate || 0.9;
                                            utterance.pitch = pitch || 1;
                                            window.speechSynthesis.speak(utterance);
                                        } else {
                                            alert('Tu navegador no soporta la sintesis de voz.');
                                        }
                                    }

                                    function playListeningSequence(items, lang, mode) {
                                        if (!('speechSynthesis' in window)) {
                                            alert('Tu navegador no soporta la sintesis de voz.');
                                            return;
                                        }

                                        listeningPlaybackToken += 1;
                                        const token = listeningPlaybackToken;
                                        const sequence = Array.isArray(items) ? items.filter(item => item && item.text) : [];
                                        if (!sequence.length) {
                                            return;
                                        }

                                        window.speechSynthesis.cancel();
                                        const pauseMs = <?php echo json_encode((int) ($configActividad->tts_pause_ms ?? 600)); ?>;
                                        const playNext = function(index) {
                                            if (token !== listeningPlaybackToken || index >= sequence.length) {
                                                return;
                                            }

                                            const item = sequence[index];
                                            const utterance = new SpeechSynthesisUtterance(item.text);
                                            utterance.lang = lang;
                                            utterance.rate = mode === 'slow' ? (item.slow_rate || 0.75) : (item.normal_rate || 0.9);
                                            utterance.pitch = item.pitch || 1;
                                            utterance.onend = function() {
                                                window.setTimeout(function() {
                                                    playNext(index + 1);
                                                }, pauseMs);
                                            };
                                            window.speechSynthesis.speak(utterance);
                                        };

                                        playNext(0);
                                    }
                                </script>

                                <?php if (!empty($listeningPrompts)): ?>
                                    <?php if (!empty($configActividad->intro)): ?>
                                        <div class="alert context-note mb-3"><?php echo htmlspecialchars((string) $configActividad->intro); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($configActividad->practice_goal) || !$showFeedback): ?>
                                        <div class="responsive-actions mb-3">
                                            <?php if (!empty($configActividad->practice_goal)): ?>
                                                <span class="small text-muted align-self-center">Objetivo oral: <?php echo htmlspecialchars((string) $configActividad->practice_goal); ?></span>
                                            <?php endif; ?>
                                            <?php if (!$showFeedback && !empty($listeningSequence)): ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick='playListeningSequence(<?php echo json_encode($listeningSequence, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, "normal")'>
                                                    <i class="bi bi-collection-play"></i> Secuencia
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick='playListeningSequence(<?php echo json_encode($listeningSequence, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, "slow")'>
                                                    <i class="bi bi-hourglass-split"></i> Secuencia lenta
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php foreach ($listeningPrompts as $idx => $prompt): ?>
                                        <?php
                                            $promptId = $prompt->id ?? ('listen_' . ($idx + 1));
                                            $promptText = (string) ($prompt->texto_tts ?? $prompt->transcripcion ?? '');
                                            $promptTranscript = (string) ($prompt->transcripcion ?? $prompt->texto_tts ?? '');
                                            $promptResponse = is_array($respuestasUsuario ?? null) ? (string) ($respuestasUsuario[$promptId] ?? '') : '';
                                            $promptRatio = $showFeedback ? $compareListen($promptTranscript, $promptResponse) : null;
                                        ?>
                                        <div class="card mb-3 border-light shadow-sm">
                                            <div class="card-body">
                                                <div class="split-head">
                                                    <div>
                                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars((string) ($prompt->descripcion ?? ('Bloque ' . ($idx + 1)))); ?></h5>
                                                        <?php if (!empty($prompt->speaker_label)): ?>
                                                            <div class="small text-muted">Referencia: <?php echo htmlspecialchars((string) $prompt->speaker_label); ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($prompt->palabras_clave)): ?>
                                                            <div class="small text-muted">Pistas clave: <?php echo htmlspecialchars(implode(' | ', array_map('strval', (array) $prompt->palabras_clave))); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="btn-group btn-group-sm" role="group" aria-label="Controles de escucha">
                                                        <button type="button" class="btn btn-outline-primary" onclick='speakText(<?php echo json_encode($promptText, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode((float) ($prompt->tts_rate ?? ($configActividad->tts_rate_normal ?? 0.9))); ?>, <?php echo json_encode((float) ($prompt->tts_pitch ?? ($configActividad->tts_pitch ?? 1.0))); ?>)'>
                                                            <i class="bi bi-volume-up-fill"></i> Normal
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary" onclick='speakText(<?php echo json_encode($promptText, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode((float) ($prompt->tts_rate_slow ?? ($configActividad->tts_rate_slow ?? 0.75))); ?>, <?php echo json_encode((float) ($prompt->tts_pitch ?? ($configActividad->tts_pitch ?? 1.0))); ?>)'>
                                                            <i class="bi bi-hourglass-split"></i> Lento
                                                        </button>
                                                    </div>
                                                </div>

                                                <label for="respuesta-<?php echo htmlspecialchars($promptId); ?>" class="form-label mt-3">Escribe lo que escuchaste:</label>
                                                <textarea class="form-control" id="respuesta-<?php echo htmlspecialchars($promptId); ?>" name="respuesta[<?php echo htmlspecialchars($promptId); ?>]" rows="3" <?php echo $showFeedback ? 'readonly' : 'required'; ?> lang="<?php echo $langCode; ?>"><?php echo htmlspecialchars($promptResponse); ?></textarea>

                                                <?php if ($showFeedback): ?>
                                                    <div class="mt-3 p-3 bg-light border rounded">
                                                        <strong>Transcripcion correcta:</strong>
                                                        <p class="mb-0 mt-1 fst-italic"><?php echo htmlspecialchars($promptTranscript); ?></p>
                                                        <?php if ($promptRatio !== null && $promptRatio >= 0.95): ?>
                                                            <div class="mt-2 text-success"><i class="bi bi-check-circle-fill"></i> Transcripcion muy precisa.</div>
                                                        <?php elseif ($promptRatio !== null && $promptRatio >= 0.75): ?>
                                                            <div class="mt-2 text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Buen intento. Ajusta algunas palabras o el orden final.</div>
                                                        <?php elseif ($promptRatio !== null): ?>
                                                            <div class="mt-2 text-danger"><i class="bi bi-x-circle-fill"></i> Aun faltan bloques importantes del audio.</div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php if (isset($configActividad->audio_url) && !empty($configActividad->audio_url)): ?>
                                        <div class="mb-4 text-center">
                                            <audio controls class="w-100">
                                                <source src="<?php echo htmlspecialchars($configActividad->audio_url); ?>" type="audio/mpeg">
                                                Tu navegador no soporta el elemento de audio.
                                            </audio>
                                        </div>
                                    <?php elseif (isset($configActividad->texto_tts) || isset($configActividad->transcripcion)): ?>
                                        <div class="mb-4 text-center">
                                            <?php $textToSpeak = $configActividad->texto_tts ?? $configActividad->transcripcion ?? ''; ?>
                                            <button type="button" class="btn btn-primary btn-lg" onclick='speakText(<?php echo json_encode($textToSpeak, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode($langCode, JSON_UNESCAPED_UNICODE); ?>, <?php echo json_encode((float) ($configActividad->tts_rate_normal ?? 0.9)); ?>, <?php echo json_encode((float) ($configActividad->tts_pitch ?? 1.0)); ?>)'>
                                                <i class="bi bi-volume-up-fill"></i> Reproducir Audio
                                            </button>
                                            <p class="text-muted mt-2"><small>Haz clic para escuchar el texto</small></p>
                                        </div>
                                    <?php endif; ?>

                                    <label for="respuesta" class="form-label">Escribe lo que escuchaste o responde a la pregunta:</label>
                                    <textarea class="form-control" id="respuesta" name="respuesta" rows="4" <?php echo $showFeedback ? 'readonly' : 'required'; ?> lang="<?php echo $langCode; ?>"><?php echo htmlspecialchars($respuestasUsuario[0] ?? ''); ?></textarea>
                                    
                                    <?php if ($showFeedback && isset($configActividad->transcripcion)): ?>
                                        <div class="mt-3 p-3 bg-light border rounded">
                                            <strong>Transcripcion correcta:</strong>
                                            <p class="mb-0 mt-1 fst-italic"><?php echo htmlspecialchars($configActividad->transcripcion); ?></p>
                                            
                                            <?php 
                                                $listeningScore = $activityOutcome['score'] ?? ($respuestaExistente->puntuacion ?? null);
                                                $listeningMax = (float) ($activityOutcome['max_score'] ?? ($actividad->puntos_maximos ?? 0));
                                                $listeningRatio = ($listeningScore !== null && $listeningMax > 0)
                                                    ? ((float) $listeningScore / $listeningMax)
                                                    : null;

                                                if ($listeningRatio !== null && $listeningRatio >= 0.95) {
                                                    echo '<div class="mt-2 text-success"><i class="bi bi-check-circle-fill"></i> Transcripcion muy precisa. Captaste casi toda la frase.</div>';
                                                } elseif ($listeningRatio !== null && $listeningRatio >= 0.75) {
                                                    echo '<div class="mt-2 text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Buen intento. Hay detalles por corregir en algunas palabras o en el orden.</div>';
                                                } elseif ($listeningRatio !== null) {
                                                    echo '<div class="mt-2 text-danger"><i class="bi bi-x-circle-fill"></i> Aun faltan bloques importantes del audio. Revisa la transcripcion y vuelve a escuchar.</div>';
                                                }
                                            ?>
                                        </div>
                                    <?php endif; ?>
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
                            <div class="responsive-actions mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Enviar respuesta
                                </button>
                                <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>

                    <?php if ($showFeedback || !empty($activityOutcome) || (!empty($respuestaExistente) && !empty($respuestaExistente->comentarios))): ?>
                        <section class="surface-card activity-outcome-panel mt-4">
                            <div class="card-body">
                                <div class="split-head">
                                    <div>
                                        <h2 class="h4 mb-1">Cierre de esta practica</h2>
                                        <div class="small text-muted">
                                            <?php
                                            echo htmlspecialchars($activityOutcome['summary']
                                                ?? ($showFeedback
                                                    ? 'Tu respuesta ya quedo registrada. Revisa el resultado y decide si sigues o vuelves a practicar.'
                                                    : 'Cuando envies la actividad, aqui veras un cierre compacto con resultado y siguiente paso.'));
                                            ?>
                                        </div>
                                    </div>
                                    <span class="soft-badge <?php echo htmlspecialchars($activityOutcome['tone'] ?? ($showFeedback ? 'success' : 'info')); ?>">
                                        <?php echo htmlspecialchars($activityOutcome['label'] ?? ($showFeedback ? 'Respuesta guardada' : 'En progreso')); ?>
                                    </span>
                                </div>

                                <div class="activity-outcome-grid">
                                    <article class="activity-outcome-stat">
                                        <div class="activity-outcome-label">Resultado</div>
                                        <div class="activity-outcome-value">
                                            <?php if (($activityOutcome['score'] ?? null) !== null): ?>
                                                <?php echo rtrim(rtrim(number_format((float) $activityOutcome['score'], 2, '.', ''), '0'), '.'); ?>
                                                <?php if (($activityOutcome['max_score'] ?? null) !== null): ?>
                                                    / <?php echo rtrim(rtrim(number_format((float) $activityOutcome['max_score'], 2, '.', ''), '0'), '.'); ?>
                                                <?php endif; ?>
                                                pts
                                            <?php elseif (isset($respuestaExistente->puntuacion)): ?>
                                                <?php echo rtrim(rtrim(number_format((float) $respuestaExistente->puntuacion, 2, '.', ''), '0'), '.'); ?> pts
                                            <?php elseif (($actividad->tipo_actividad === 'escritura' || $actividad->tipo_actividad === 'escucha') && isset($respuestaExistente) && $respuestaExistente): ?>
                                                Pendiente
                                            <?php elseif ($showFeedback): ?>
                                                Guardado
                                            <?php else: ?>
                                                Por calcular
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-outcome-copy">
                                            <?php if (($actividad->tipo_actividad === 'escritura' || $actividad->tipo_actividad === 'escucha') && isset($respuestaExistente) && $respuestaExistente && !isset($respuestaExistente->puntuacion)): ?>
                                                Esta actividad todavia espera revision docente.
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($activityOutcome['headline'] ?? 'Lectura compacta del intento actual.'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </article>

                                    <article class="activity-outcome-stat">
                                        <div class="activity-outcome-label">Registro</div>
                                        <div class="activity-outcome-value">
                                            <?php if (isset($respuestaExistente) && $respuestaExistente): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($respuestaExistente->fecha_respuesta)); ?>
                                            <?php else: ?>
                                                Aun sin envio
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-outcome-copy">
                                            <?php echo $showFeedback ? 'Tu intento quedo almacenado y ya puedes seguir con la leccion.' : 'Todavia no se ha registrado una respuesta en esta sesion.'; ?>
                                        </div>
                                    </article>

                                    <article class="activity-outcome-stat">
                                        <div class="activity-outcome-label">Avance en la leccion</div>
                                        <div class="activity-outcome-value"><?php echo (int) ($activityOutcome['lesson_progress'] ?? 0); ?>%</div>
                                        <div class="activity-outcome-copy">
                                            <?php echo htmlspecialchars($activityOutcome['next_hint'] ?? 'Usa este resultado para decidir si conviene seguir, repasar o volver a practicar.'); ?>
                                        </div>
                                    </article>
                                </div>

                                <?php if (isset($respuestaExistente->comentarios) && !empty($respuestaExistente->comentarios)): ?>
                                    <div class="alert context-note mb-0">
                                        <div class="fw-semibold mb-2"><i class="bi bi-chat-square-text"></i> Comentarios del profesor</div>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($respuestaExistente->comentarios)); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($showFeedback): ?>
                                    <div class="responsive-actions">
                                        <a href="<?php echo htmlspecialchars($nextActionUrl ?? url('/estudiante/lecciones/' . $leccion->id . '/contenido')); ?>" class="btn btn-primary">
                                            <i class="bi bi-arrow-right-circle"></i> <?php echo htmlspecialchars(!empty($siguienteItem['mensaje']) ? $siguienteItem['mensaje'] : $activitySummaryCta); ?>
                                        </a>
                                        <a href="?retry=1" class="btn btn-success">
                                            <i class="bi bi-arrow-repeat"></i> Practicar otra vez
                                        </a>
                                        <a href="<?php echo url('/estudiante/lecciones/' . $leccion->id . '/contenido'); ?>" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left"></i> Volver a la leccion
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
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
