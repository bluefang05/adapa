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
?>

<div class="container">
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

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-journal-richtext"></i> Leccion activa</span>
        <h1 class="page-title"><?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">Completa teoria y practica en este orden para avanzar mas rapido.</p>
        <?php if (!empty($leccion->descripcion)): ?>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($leccion->descripcion); ?></p>
        <?php endif; ?>
        <?php if (isset($resumenProgreso) && !empty($resumenProgreso->completada)): ?>
            <div class="mt-3">
                <span class="soft-badge">
                    <i class="bi bi-check-circle-fill"></i> Leccion completada
                </span>
            </div>
        <?php endif; ?>
        <?php if (isset($resumenProgreso)): ?>
            <div class="metric-grid">
                <div class="metric-card">
                    <div class="metric-label">Progreso</div>
                    <div class="metric-value"><?php echo (int) $resumenProgreso->porcentaje; ?>%</div>
                    <div class="metric-note">Avance total de esta leccion.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Teoria</div>
                    <div class="metric-value"><?php echo (int) $resumenProgreso->teorias_completadas; ?>/<?php echo (int) $resumenProgreso->total_teorias; ?></div>
                    <div class="metric-note">Bloques conceptuales ya leidos.</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Actividades</div>
                    <div class="metric-value"><?php echo (int) $resumenProgreso->actividades_completadas; ?>/<?php echo (int) $resumenProgreso->total_actividades; ?></div>
                    <div class="metric-note">Practicas respondidas hasta ahora.</div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <?php if (isset($siguienteItem)): ?>
        <div class="panel mb-4">
            <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="metric-label">Siguiente paso sugerido</div>
                    <div class="fw-semibold mt-1">
                        <?php if ($siguienteItem['tipo'] === 'curso_completado'): ?>
                            Has completado esta leccion. <?php echo htmlspecialchars($siguienteItem['titulo']); ?>
                        <?php else: ?>
                            <?php echo htmlspecialchars($siguienteItem['mensaje']); ?>: <?php echo htmlspecialchars($siguienteItem['titulo']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <?php if ($siguienteItem['tipo'] === 'teoria'): ?>
                        <a href="#teoria-<?php echo $siguienteItem['id']; ?>" class="btn btn-primary">Ir a teoria</a>
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

    <?php if (!empty($teorias) || !empty($actividades)): ?>
        <section class="panel mb-4">
            <div class="panel-body">
                <div class="section-title mb-3">
                    <h2>Ruta de la leccion</h2>
                </div>
                <div class="course-meta d-flex flex-wrap gap-2">
                    <?php if (!empty($teorias)): ?>
                        <span class="soft-badge"><i class="bi bi-book"></i> <?php echo count($teorias); ?> piezas de teoria</span>
                    <?php endif; ?>
                    <?php if (!empty($actividades)): ?>
                        <span class="soft-badge"><i class="bi bi-lightning-charge"></i> <?php echo count($actividades); ?> actividades</span>
                    <?php endif; ?>
                    <?php if (!empty($resumenProgreso->estado)): ?>
                        <span class="soft-badge"><i class="bi bi-graph-up"></i> Estado: <?php echo $resumenProgreso->estado === 'completada' ? 'Completada' : ($resumenProgreso->estado === 'en_progreso' ? 'En progreso' : 'Pendiente'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="mb-4">
        <div class="section-title">
            <h2>Teoria</h2>
        </div>
        <?php if (empty($teorias)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-book"></i></span>
                    <div class="empty-state-copy">No hay contenido teorico para esta leccion.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teorias as $teoria): ?>
                <div class="content-block" id="teoria-<?php echo $teoria->id; ?>">
                    <details class="lesson-theory-details" <?php echo empty($teoria->leido) ? 'open' : ''; ?>>
                        <summary class="lesson-theory-summary">
                            <span><?php echo htmlspecialchars($teoria->titulo); ?></span>
                            <?php if (!empty($teoria->leido)): ?>
                                <span class="soft-badge"><i class="bi bi-check-circle-fill"></i> Completado</span>
                            <?php else: ?>
                                <span class="soft-badge"><i class="bi bi-circle"></i> Pendiente</span>
                            <?php endif; ?>
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
                                    <div class="stack-list">
                                        <?php foreach ($teoria->bloques as $bloque): ?>
                                            <div class="stack-item">
                                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                    <div>
                                                        <div class="stack-item-title">
                                                            <?php echo htmlspecialchars($bloque->titulo ?: ucfirst($bloque->tipo_bloque)); ?>
                                                        </div>
                                                        <div class="stack-item-subtitle"><?php echo htmlspecialchars(ucfirst($bloque->tipo_bloque)); ?></div>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap align-items-center">
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
                                                    </div>
                                                </div>
                                                <?php if (!empty($bloque->contenido)): ?>
                                                    <div class="mt-2">
                                                        <?php echo nl2br(htmlspecialchars($bloque->contenido)); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($bloque->ruta_archivo) && !empty($bloque->tipo_media)): ?>
                                                    <div class="mt-3">
                                                        <?php echo renderLessonBlockMedia($bloque); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
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

    <section>
        <div class="section-title">
            <h2>Actividades</h2>
        </div>
        <?php if (empty($actividades)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-lightning"></i></span>
                    <div class="empty-state-copy">No hay actividades para esta leccion.</div>
                </div>
            </div>
        <?php else: ?>
            <ul class="list-group lesson-stack">
                <?php foreach ($actividades as $actividad): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
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
                            <?php if (!empty($actividad->descripcion)): ?>
                                <div class="small text-muted mt-2"><?php echo htmlspecialchars($actividad->descripcion); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($actividad->completada)): ?>
                                <div class="small text-muted mt-1">
                                    Completada<?php if (isset($actividad->calificacion)): ?> - <?php echo $actividad->calificacion; ?> pts<?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="small text-muted mt-1">Pendiente</div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo url('/estudiante/actividades/' . $actividad->id); ?>" class="btn btn-primary">
                            <?php echo !empty($actividad->completada) ? 'Ver resultados' : 'Realizar actividad'; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <?php if (!empty($courseResources)): ?>
        <section class="panel mb-4">
            <div class="panel-body">
                <div class="section-title mb-3">
                    <h2>Recursos utiles para destrabarte</h2>
                    <a href="<?php echo url('/estudiante/recursos?idioma=' . urlencode(Curso::obtenerIdiomaObjetivo($curso))); ?>" class="btn btn-outline-secondary btn-sm">
                        Ver todos
                    </a>
                </div>
                <div class="row g-3">
                    <?php foreach ($courseResources as $resource): ?>
                        <div class="col-lg-3 col-md-6">
                            <article class="surface-card useful-resource-card h-100">
                                <div class="card-body d-flex flex-column gap-2">
                                    <div class="small text-muted"><?php echo htmlspecialchars($resource['badge'] ?? 'Recurso'); ?></div>
                                    <h3 class="h6 mb-0"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <div class="mt-auto">
                                        <a href="<?php echo htmlspecialchars($resource['url']); ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-box-arrow-up-right"></i> Abrir
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel mt-4 issue-report-panel">
        <div class="panel-body">
            <details class="issue-report-details">
                <summary class="issue-report-summary">
                    <i class="bi bi-bug"></i> Reportar un fallo en esta leccion
                </summary>
                <form action="<?php echo url('/estudiante/reportar-fallo'); ?>" method="POST" class="mt-3">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="context_type" value="leccion">
                    <input type="hidden" name="curso_id" value="<?php echo (int) $leccion->curso_id; ?>">
                    <input type="hidden" name="leccion_id" value="<?php echo (int) $leccion->id; ?>">
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? url('/estudiante'), ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="issue_type_leccion">Tipo de fallo</label>
                            <select id="issue_type_leccion" name="issue_type" class="form-select" required>
                                <option value="error_visual">Error visual</option>
                                <option value="contenido_incorrecto">Contenido incorrecto</option>
                                <option value="boton_no_funciona">Boton no funciona</option>
                                <option value="audio_video">Audio/Video</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="issue_desc_leccion">Describe el problema</label>
                            <textarea id="issue_desc_leccion" name="description" class="form-control" rows="3" minlength="12" maxlength="2000" required placeholder="Que paso, en que parte y como lo reproduces."></textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-send"></i> Enviar reporte
                        </button>
                    </div>
                </form>
            </details>
        </div>
    </section>

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
        button.addEventListener('click', function () {
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

            utterance.lang = languageMap[langKey] || 'es-ES';
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
