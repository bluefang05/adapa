<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../models/Curso.php';

function renderLessonBlockMedia($bloque) {
    if (empty($bloque->ruta_archivo) || empty($bloque->tipo_media)) {
        return '';
    }

    $assetUrl = app_media_public_url($bloque->ruta_archivo);
    $label = htmlspecialchars($bloque->media_titulo ?: $bloque->tipo_media, ENT_QUOTES, 'UTF-8');
    $escapedUrl = htmlspecialchars($assetUrl, ENT_QUOTES, 'UTF-8');
    $metadata = app_media_metadata($bloque->metadata ?? null);

    if (!empty($metadata['embed_url'])) {
        $embedUrl = htmlspecialchars($metadata['embed_url'], ENT_QUOTES, 'UTF-8');
        $frameClass = htmlspecialchars(app_media_embed_frame_class($bloque->ruta_archivo, $metadata), ENT_QUOTES, 'UTF-8');
        return '<div class="' . $frameClass . '"><iframe src="' . $embedUrl . '" title="' . $label . '" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>'
            . '<div class="mt-2"><a href="' . $escapedUrl . '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> Abrir video</a></div>';
    }

    if ($bloque->tipo_media === 'imagen') {
        $alt = htmlspecialchars($bloque->alt_text ?: $bloque->media_titulo ?: 'Recurso visual', ENT_QUOTES, 'UTF-8');
        return '<img src="' . $escapedUrl . '" alt="' . $alt . '" class="media-preview-thumb lesson-media-thumb">';
    }

    if ($bloque->tipo_media === 'audio') {
        return '<audio controls preload="none" class="media-preview-player"><source src="' . $escapedUrl . '"></audio>';
    }

    if ($bloque->tipo_media === 'video') {
        return '<video controls preload="metadata" class="media-preview-player"><source src="' . $escapedUrl . '"></video>';
    }

    if ($bloque->tipo_media === 'pdf') {
        return '<a href="' . $escapedUrl . '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"><i class="bi bi-file-earmark-pdf"></i> Abrir ' . $label . '</a>';
    }

    return '<a href="' . $escapedUrl . '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i> Abrir ' . $label . '</a>';
}

function lessonActivityTypeLabel($tipo) {
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
        'respuesta_corta' => 'Respuesta corta',
    ];

    return $labels[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
}

$lessonProgressStateLabel = $lessonJourney['state_label'] ?? 'Pendiente';
$lessonProgressTone = $lessonJourney['state_tone'] ?? 'warning';
$lessonRemainingTheory = $lessonJourney['remaining_theory'] ?? 0;
$lessonRemainingActivities = $lessonJourney['remaining_activities'] ?? 0;
$lessonNextActionCopy = $lessonJourney['next_copy'] ?? 'Sigue con la teoria para ganar contexto antes de responder.';
if (isset($resumenProgreso)) {
    $lessonRemainingTheory = max(0, (int) $resumenProgreso->total_teorias - (int) $resumenProgreso->teorias_completadas);
    $lessonRemainingActivities = max(0, (int) $resumenProgreso->total_actividades - (int) $resumenProgreso->actividades_completadas);

    if (($resumenProgreso->estado ?? '') === 'completada') {
        $lessonProgressStateLabel = 'Completada';
        $lessonProgressTone = 'success';
        $lessonNextActionCopy = 'Ya cerraste esta leccion. Aprovecha para repasar o avanzar al siguiente bloque.';
    } elseif (($resumenProgreso->estado ?? '') === 'en_progreso') {
        $lessonProgressStateLabel = 'En progreso';
        $lessonProgressTone = 'accent';
        $lessonNextActionCopy = $lessonRemainingTheory > 0
            ? 'Te conviene cerrar la teoria pendiente antes de entrar a la siguiente actividad.'
            : 'La teoria ya esta. Este es buen momento para rematar la practica pendiente.';
    }
}

if (isset($lessonJourney['next_copy'])) {
    $lessonNextActionCopy = $lessonJourney['next_copy'];
}

$isLessonStarting = isset($resumenProgreso)
    && (int) ($resumenProgreso->teorias_completadas ?? 0) === 0
    && (int) ($resumenProgreso->actividades_completadas ?? 0) === 0;
$nextTheoryId = isset($siguienteItem) && ($siguienteItem['tipo'] ?? '') === 'teoria'
    ? (int) $siguienteItem['id']
    : 0;
?>

<div class="container student-lesson-page">
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante'); ?>">Mis Cursos</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('/estudiante/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($leccion->titulo); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-journal-richtext"></i> Tu leccion de hoy</span>
        <h1 class="page-title"><?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">
            <?php echo $isLessonStarting
                ? 'Empieza con una lectura corta. Te iremos mostrando el resto paso a paso.'
                : 'Retoma exactamente donde lo dejaste.'; ?>
        </p>
        <?php if (!empty($leccion->descripcion)): ?>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($leccion->descripcion); ?></p>
        <?php endif; ?>
        <div class="compact-meta-row">
            <?php if (isset($resumenProgreso)): ?>
                <span class="soft-badge info"><i class="bi bi-graph-up"></i> <?php echo (int) $resumenProgreso->porcentaje; ?>% progreso</span>
                <span class="soft-badge"><i class="bi bi-list-check"></i> <?php echo (int) $resumenProgreso->completados; ?>/<?php echo (int) $resumenProgreso->total_items; ?> pasos</span>
                <span class="soft-badge badge-<?php echo htmlspecialchars($lessonProgressTone); ?>"><i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($lessonProgressStateLabel); ?></span>
            <?php else: ?>
                <span class="soft-badge"><i class="bi bi-book"></i> <?php echo count($teorias); ?> piezas de teoria</span>
                <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo count($actividades); ?> actividades</span>
            <?php endif; ?>
            <?php if (isset($resumenProgreso) && !empty($resumenProgreso->completada)): ?>
                <span class="soft-badge success"><i class="bi bi-check-circle-fill"></i> Leccion completada</span>
            <?php endif; ?>
        </div>
    </section>

    <?php if (isset($siguienteItem)): ?>
        <div class="alert context-note mb-4">
            <div class="split-head">
                <div>
                    <div class="metric-label">Haz esto ahora</div>
                    <div class="fw-semibold mt-1">
                        <?php if ($siguienteItem['tipo'] === 'curso_completado'): ?>
                            Has completado esta leccion. <?php echo htmlspecialchars($siguienteItem['titulo']); ?>
                        <?php else: ?>
                            <?php echo htmlspecialchars($siguienteItem['mensaje']); ?>: <?php echo htmlspecialchars($siguienteItem['titulo']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1"><?php echo htmlspecialchars($lessonNextActionCopy); ?></div>
                </div>
                <div class="responsive-actions">
                    <?php if ($siguienteItem['tipo'] === 'teoria'): ?>
                        <a href="#teoria-<?php echo $siguienteItem['id']; ?>" class="btn btn-primary">Empezar lectura</a>
                    <?php elseif ($siguienteItem['tipo'] === 'actividad'): ?>
                        <a href="<?php echo url('/estudiante/actividades/' . $siguienteItem['id']); ?>" class="btn btn-primary">Realizar actividad</a>
                    <?php elseif ($siguienteItem['tipo'] === 'leccion'): ?>
                        <a href="<?php echo url('/estudiante/lecciones/' . $siguienteItem['id'] . '/contenido'); ?>" class="btn btn-success">Ir a la siguiente leccion</a>
                    <?php elseif ($siguienteItem['tipo'] === 'curso_completado'): ?>
                        <a href="<?php echo url('/estudiante/cursos/' . $siguienteItem['id'] . '/lecciones'); ?>" class="btn btn-success">Curso completado</a>
                    <?php else: ?>
                        <a href="<?php echo url('/estudiante/cursos/' . $siguienteItem['id'] . '/lecciones'); ?>" class="btn btn-success">Ver curso completo</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section class="mb-4">
        <div class="section-title">
            <div>
                <div class="metric-label">Primero</div>
                <h2>Lecturas guiadas</h2>
            </div>
            <span class="small text-muted">Una a la vez, a tu ritmo.</span>
        </div>
        <?php if (empty($teorias)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-book"></i></span>
                    <div class="empty-state-copy">No hay contenido teorico para esta leccion.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teorias as $teoriaIndex => $teoria): ?>
                <?php
                $isCurrentTheory = !empty($teoria->is_next)
                    || ($nextTheoryId > 0 && (int) $teoria->id === $nextTheoryId)
                    || ($nextTheoryId === 0 && $teoriaIndex === 0 && empty($teoria->leido));
                ?>
                <div class="content-block <?php echo $isCurrentTheory ? 'is-current-step' : ''; ?>" id="teoria-<?php echo $teoria->id; ?>">
                    <details class="lesson-theory-details" <?php echo $isCurrentTheory ? 'open' : ''; ?>>
                        <summary class="lesson-theory-summary">
                            <span><?php echo htmlspecialchars($teoria->titulo); ?></span>
                            <div class="badge-row">
                            <?php if (!empty($teoria->is_next)): ?>
                                <span class="soft-badge badge-accent"><i class="bi bi-stars"></i> Ahora</span>
                            <?php endif; ?>
                            <?php if (!empty($teoria->leido)): ?>
                                <span class="soft-badge"><i class="bi bi-check-circle-fill"></i> Completado</span>
                            <?php else: ?>
                                <span class="soft-badge"><i class="bi bi-circle"></i> Pendiente</span>
                            <?php endif; ?>
                            </div>
                        </summary>
                        <div class="card-body pt-0">
                            <div class="card-text">
                                <?php if (!empty($teoria->duracion_minutos)): ?>
                                    <div class="course-meta mb-3">
                                        <span><i class="bi bi-clock"></i> <?php echo (int) $teoria->duracion_minutos; ?> min</span>
                                        <?php if (!empty($teoria->bloques) && is_array($teoria->bloques)): ?>
                                            <span><i class="bi bi-layers"></i> <?php echo count($teoria->bloques); ?> bloques</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($teoria->tiene_bloques) && !empty($teoria->bloques)): ?>
                                    <div class="stack-list lesson-block-list">
                                        <?php foreach ($teoria->bloques as $bloqueIndex => $bloque): ?>
                                            <details class="stack-item lesson-block-details" <?php echo $bloqueIndex === 0 ? 'open' : ''; ?>>
                                                <summary class="split-head lesson-block-summary">
                                                    <div>
                                                        <div class="stack-item-title">
                                                            <?php echo htmlspecialchars($bloque->titulo ?: ucfirst($bloque->tipo_bloque)); ?>
                                                        </div>
                                                        <div class="stack-item-subtitle"><?php echo htmlspecialchars(ucfirst($bloque->tipo_bloque)); ?></div>
                                                    </div>
                                                    <div class="badge-row lesson-block-actions">
                                                        <?php if ((int) ($bloque->tts_habilitado ?? 0) === 1 && !empty($bloque->contenido)): ?>
                                                            <button
                                                                type="button"
                                                                class="btn btn-sm btn-outline-primary tts-play-btn"
                                                                data-tts-text="<?php echo htmlspecialchars($bloque->contenido, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-tts-lang="<?php echo htmlspecialchars($bloque->idioma_bloque ?: 'espanol', ENT_QUOTES, 'UTF-8'); ?>"
                                                            >
                                                                <i class="bi bi-volume-up"></i> Escuchar
                                                            </button>
                                                        <?php endif; ?>
                                                        <i class="bi bi-chevron-down lesson-block-chevron" aria-hidden="true"></i>
                                                    </div>
                                                </summary>
                                                <div class="lesson-block-content">
                                                    <?php if (!empty($bloque->contenido)): ?>
                                                        <div>
                                                            <?php echo nl2br(htmlspecialchars($bloque->contenido)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($bloque->ruta_archivo) && !empty($bloque->tipo_media)): ?>
                                                        <div class="mt-3">
                                                            <?php echo renderLessonBlockMedia($bloque); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </details>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <?php echo sanitize_rich_html((string) $teoria->contenido); ?>
                                <?php endif; ?>
                            </div>
                            <?php if (empty($teoria->leido)): ?>
                                <form action="<?php echo url('/estudiante/teoria/' . $teoria->id . '/leer'); ?>" method="POST" class="mt-3">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-check2-circle"></i> Marcar como leido y seguir
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </details>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="<?php echo $lessonRemainingTheory > 0 ? 'practice-is-upcoming' : ''; ?>">
        <div class="section-title">
            <div>
                <div class="metric-label">Despues</div>
                <h2>Practica</h2>
            </div>
            <span class="small text-muted"><?php echo count($actividades); ?> ejercicios cortos</span>
        </div>
        <?php if (empty($actividades)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-lightning"></i></span>
                    <div class="empty-state-copy">No hay actividades para esta leccion.</div>
                </div>
            </div>
        <?php else: ?>
            <details class="lesson-practice-details" <?php echo $lessonRemainingTheory === 0 ? 'open' : ''; ?>>
                <summary class="panel lesson-practice-summary">
                    <div>
                        <div class="fw-semibold"><?php echo $lessonRemainingTheory > 0 ? 'La practica viene despues de las lecturas' : 'Tus ejercicios'; ?></div>
                        <div class="small text-muted mt-1">
                            <?php echo $lessonRemainingTheory > 0
                                ? 'Puedes verla ahora si quieres, pero no necesitas resolverla todavia.'
                                : 'Continua con el ejercicio recomendado.'; ?>
                        </div>
                    </div>
                    <span class="soft-badge"><?php echo count($actividades); ?> ejercicios</span>
                </summary>
                <ul class="list-group lesson-stack lesson-activity-list">
                <?php foreach ($actividades as $actividad): ?>
                    <li class="list-group-item lesson-stack-item <?php echo !empty($actividad->is_next) ? 'is-current-step' : ''; ?>">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($actividad->titulo); ?></div>
                            <div class="course-meta mt-2">
                                <span><?php echo htmlspecialchars(lessonActivityTypeLabel($actividad->tipo_actividad ?? '')); ?></span>
                                <?php if (!empty($actividad->puntos_maximos)): ?>
                                    <span><?php echo (int) $actividad->puntos_maximos; ?> pts</span>
                                <?php endif; ?>
                                <?php if (!empty($actividad->tiempo_limite_minutos)): ?>
                                    <span><?php echo (int) $actividad->tiempo_limite_minutos; ?> min</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($actividad->is_next)): ?>
                                <div class="small mt-2"><i class="bi bi-stars"></i> Siguiente ejercicio recomendado</div>
                            <?php elseif (!empty($actividad->completada)): ?>
                                <div class="small text-muted mt-2"><i class="bi bi-check-circle-fill"></i> Completado</div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo url('/estudiante/actividades/' . $actividad->id); ?>" class="btn <?php echo !empty($actividad->is_next) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <?php echo !empty($actividad->completada) ? 'Revisar actividad' : (!empty($actividad->is_next) ? 'Seguir aqui' : 'Realizar actividad'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
    </section>

    <?php
    $issueReportTitle = 'Reportar un fallo en esta leccion';
    $issueReportAction = url('/reportar-fallo');
    $issueReportContextType = 'leccion';
    $issueReportContextId = 'leccion_' . (int) $leccion->id;
    $issueReportReturnTo = $_SERVER['REQUEST_URI'] ?? url('/estudiante');
    $issueReportCourseId = (int) $leccion->curso_id;
    $issueReportLessonId = (int) $leccion->id;
    $issueReportDescriptionPlaceholder = 'Que paso, en que parte y como lo reproduces.';
    require __DIR__ . '/../partials/issue_report_panel.php';
    ?>

    <div class="mt-4 mb-2">
        <a href="<?php echo url('/estudiante/cursos/' . $leccion->curso_id . '/lecciones'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a lecciones
        </a>
    </div>
</div>

<script>
(function () {
    if (!('speechSynthesis' in window)) {
        return;
    }

    const languageMap = <?php echo json_encode(app_tts_language_map(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    let activeButton = null;

    function resetButton(button) {
        if (!button) {
            return;
        }

        button.classList.remove('is-speaking');
        button.innerHTML = '<i class="bi bi-volume-up"></i> Escuchar';
    }

    document.querySelectorAll('.tts-play-btn').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const text = button.getAttribute('data-tts-text') || '';
            const langKey = button.getAttribute('data-tts-lang') || 'espanol';
            const utterance = new SpeechSynthesisUtterance(text);

            if (!text.trim()) {
                return;
            }

            if (activeButton === button) {
                window.speechSynthesis.cancel();
                resetButton(button);
                activeButton = null;
                return;
            }

            if (activeButton) {
                window.speechSynthesis.cancel();
                resetButton(activeButton);
            }

            const resolvedLocale = languageMap[String(langKey).toLocaleLowerCase()] || langKey;
            if (window.AdapaTTS) {
                window.AdapaTTS.applyVoice(utterance, resolvedLocale);
            } else {
                utterance.lang = resolvedLocale || 'es-ES';
            }
            utterance.rate = 0.95;

            utterance.onend = function () {
                resetButton(button);
                activeButton = null;
            };

            utterance.onerror = function () {
                resetButton(button);
                activeButton = null;
            };

            activeButton = button;
            button.classList.add('is-speaking');
            button.innerHTML = '<i class="bi bi-stop-circle"></i> Detener';
            window.speechSynthesis.speak(utterance);
        });
    });

    window.addEventListener('beforeunload', function () {
        window.speechSynthesis.cancel();
    });
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
