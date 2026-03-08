<?php
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../../models/Curso.php';
require_once __DIR__ . '/../../../models/Teoria.php';
require_once __DIR__ . '/../../../models/Actividad.php';

function renderProfessorLessonBlockMedia($bloque) {
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

    return '<a href="' . $escapedUrl . '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"><i class="bi bi-paperclip"></i> Abrir ' . $label . '</a>';
}

function professorLessonActivityTypeLabel($tipo) {
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
        'respuesta_larga' => 'Respuesta larga',
        'proyecto' => 'Proyecto',
        'codigo' => 'Codigo',
    ];

    return $labels[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
}

function renderProfessorSupportResourcePreview($resource) {
    if (!$resource || empty($resource['url'])) {
        return '';
    }

    $title = htmlspecialchars($resource['title'] ?? 'Recurso de apoyo', ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars($resource['url'], ENT_QUOTES, 'UTF-8');
    $kind = $resource['kind'] ?? 'link';
    $kindLabels = [
        'video' => 'Video de apoyo',
        'audio' => 'Audio de apoyo',
        'image' => 'Imagen de apoyo',
        'pdf' => 'Documento de apoyo',
        'link' => 'Enlace de apoyo',
    ];
    $kindLabel = htmlspecialchars($kindLabels[$kind] ?? 'Recurso de apoyo', ENT_QUOTES, 'UTF-8');

    $html = '<div class="support-resource-panel mt-3">';
    $html .= '<div class="support-resource-header">';
    $html .= '<div><div class="support-resource-eyebrow">' . $kindLabel . '</div><div class="support-resource-title">' . $title . '</div></div>';
    $html .= '<a href="' . $url . '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer">Abrir recurso</a>';
    $html .= '</div>';

    if ($kind === 'video' && !empty($resource['embed_url'])) {
        $frameClass = htmlspecialchars($resource['frame_class'] ?? 'media-embed-frame', ENT_QUOTES, 'UTF-8');
        $embedUrl = htmlspecialchars($resource['embed_url'], ENT_QUOTES, 'UTF-8');
        $html .= '<div class="' . $frameClass . '"><iframe src="' . $embedUrl . '" title="' . $title . '" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
    } elseif ($kind === 'audio') {
        $html .= '<audio controls preload="none" class="media-preview-player"><source src="' . $url . '"></audio>';
    } elseif ($kind === 'image') {
        $html .= '<img src="' . $url . '" alt="' . $title . '" class="media-preview-thumb lesson-media-thumb">';
    } else {
        $html .= '<div class="support-resource-fallback">Este apoyo se abrira en una pestana aparte para el alumno.</div>';
    }

    $html .= '</div>';
    return $html;
}

function renderProfessorActivityPreviewSummary($actividad) {
    $contenido = json_decode((string) ($actividad->contenido ?? ''), true);
    if (!is_array($contenido)) {
        return '<div class="small text-muted mt-2">Sin configuracion avanzada visible todavia.</div>';
    }

    $tipo = (string) ($actividad->tipo_actividad ?? '');
    $supportResource = app_activity_support_resource($contenido);
    $supportResourceHtml = renderProfessorSupportResourcePreview($supportResource);

    if ($tipo === 'opcion_multiple' || $tipo === 'verdadero_falso') {
        $preguntas = $contenido['preguntas'] ?? [];
        if (!empty($preguntas)) {
            $html = '<div class="activity-preview-stack mt-3">';
            foreach (array_slice($preguntas, 0, 2) as $index => $pregunta) {
                $texto = htmlspecialchars((string) ($pregunta['texto'] ?? $pregunta['pregunta'] ?? ('Pregunta ' . ($index + 1))));
                $html .= '<div class="activity-preview-card"><div class="fw-semibold">' . $texto . '</div>';
                if (!empty($pregunta['image_url'])) {
                    $html .= '<div class="small text-muted mt-1">Incluye imagen asociada.</div>';
                }
                if (!empty($pregunta['opciones'])) {
                    $html .= '<ul class="quality-checklist-list mt-2 mb-0">';
                    foreach (array_slice((array) $pregunta['opciones'], 0, 3) as $opcion) {
                        $textoOpcion = htmlspecialchars((string) ($opcion['texto'] ?? ''));
                        $marca = !empty($opcion['es_correcta']) || !empty($opcion['correcta']) ? ' <strong>(correcta)</strong>' : '';
                        $html .= '<li>' . $textoOpcion . $marca . '</li>';
                    }
                    $html .= '</ul>';
                }
                $html .= '</div>';
            }
            $html .= '</div>' . $supportResourceHtml;
            return $html;
        }

        if ($tipo === 'verdadero_falso' && !empty($contenido['afirmacion'])) {
            return '<div class="activity-preview-card mt-3"><div class="fw-semibold">' . htmlspecialchars((string) $contenido['afirmacion']) . '</div><div class="small text-muted mt-1">Respuesta correcta: ' . htmlspecialchars((string) ($contenido['respuesta_correcta'] ?? '')) . '</div></div>' . $supportResourceHtml;
        }
    }

    if ($tipo === 'escucha') {
        $preguntas = $contenido['preguntas'] ?? [];
        $audioUrl = htmlspecialchars((string) ($contenido['audio_url'] ?? ''));
        $transcripcion = trim((string) ($contenido['transcripcion'] ?? ''));
        $html = '<div class="activity-preview-card mt-3">';
        $html .= '<div class="fw-semibold">Audio configurado</div>';
        $html .= $audioUrl !== '' ? '<div class="small text-muted mt-1">Fuente: ' . $audioUrl . '</div>' : '<div class="small text-muted mt-1">Sin audio vinculado.</div>';
        if ($transcripcion !== '') {
            $html .= '<div class="small mt-2">Transcripcion disponible.</div>';
        }
        $html .= '<div class="small mt-2">Preguntas de comprension: ' . count((array) $preguntas) . '</div>';
        $html .= '</div>' . $supportResourceHtml;
        return $html;
    }

    if ($tipo === 'ordenar_palabras' || $tipo === 'arrastrar_soltar' || $tipo === 'emparejamiento') {
        $items = $contenido['items'] ?? [];
        $targets = $contenido['targets'] ?? [];
        $html = '<div class="activity-preview-card mt-3"><div class="fw-semibold">Vista de estructura</div>';
        if (!empty($items)) {
            $firstItem = $items[0] ?? '';
            $sampleItem = is_array($firstItem) ? ($firstItem['texto'] ?? $firstItem['content'] ?? $firstItem['left'] ?? '') : (string) $firstItem;
            $html .= '<div class="small text-muted mt-1">Items configurados: ' . count((array) $items) . '</div>';
            if ($sampleItem !== '') {
                $html .= '<div class="small mt-2">Ejemplo: ' . htmlspecialchars($sampleItem) . '</div>';
            }
        }
        if (!empty($targets)) {
            $firstTarget = $targets[0] ?? '';
            $sampleTarget = is_array($firstTarget) ? ($firstTarget['texto'] ?? $firstTarget['content'] ?? $firstTarget['right'] ?? '') : (string) $firstTarget;
            $html .= '<div class="small text-muted mt-1">Destinos o categorias: ' . count((array) $targets) . '</div>';
            if ($sampleTarget !== '') {
                $html .= '<div class="small mt-2">Destino: ' . htmlspecialchars($sampleTarget) . '</div>';
            }
        }
        $html .= '</div>' . $supportResourceHtml;
        return $html;
    }

    if ($tipo === 'respuesta_corta' || $tipo === 'respuesta_larga' || $tipo === 'completar_oracion') {
        $html = '<div class="activity-preview-card mt-3">';
        if (!empty($contenido['pregunta'])) {
            $html .= '<div class="fw-semibold">' . htmlspecialchars((string) $contenido['pregunta']) . '</div>';
        } elseif (!empty($contenido['texto_completo'])) {
            $html .= '<div class="fw-semibold">' . htmlspecialchars((string) $contenido['texto_completo']) . '</div>';
        } else {
            $html .= '<div class="fw-semibold">Consigna abierta</div>';
        }
        if (!empty($contenido['respuestas_correctas'])) {
            $html .= '<div class="small text-muted mt-1">Respuestas esperadas: ' . count((array) $contenido['respuestas_correctas']) . '</div>';
        }
        $html .= '</div>' . $supportResourceHtml;
        return $html;
    }

    if ($tipo === 'proyecto') {
        $html = '<div class="activity-preview-card mt-3">';
        if (!empty($contenido['instrucciones'])) {
            $html .= '<div class="fw-semibold">Proyecto configurado</div><div class="small mt-1">' . htmlspecialchars((string) $contenido['instrucciones']) . '</div>';
        }
        if (!empty($contenido['entregables'])) {
            $html .= '<div class="small text-muted mt-2">Entregables: ' . htmlspecialchars((string) $contenido['entregables']) . '</div>';
        }
        $html .= '</div>' . $supportResourceHtml;
        return $html;
    }

    if ($tipo === 'codigo') {
        $html = '<div class="activity-preview-card mt-3">';
        $html .= '<div class="fw-semibold">Actividad tecnica</div>';
        if (!empty($contenido['lenguaje'])) {
            $html .= '<div class="small text-muted mt-1">Lenguaje: ' . htmlspecialchars((string) $contenido['lenguaje']) . '</div>';
        }
        if (!empty($contenido['instrucciones'])) {
            $html .= '<div class="small mt-2">' . htmlspecialchars((string) $contenido['instrucciones']) . '</div>';
        }
        if (!empty($contenido['codigo_inicial'])) {
            $html .= '<div class="small text-muted mt-2">Incluye codigo inicial.</div>';
        }
        $html .= '</div>' . $supportResourceHtml;
        return $html;
    }

    if ($supportResourceHtml !== '') {
        return '<div class="activity-preview-card mt-3"><div class="fw-semibold">Recurso de apoyo vinculado</div></div>' . $supportResourceHtml;
    }

    return '<div class="small text-muted mt-2">Sin resumen especifico para este tipo todavia.</div>';
}

$previewReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/lecciones/' . $leccion->id . '/preview');
$previewQuickFixes = [];

if (empty($teorias)) {
    $previewQuickFixes[] = [
        'label' => 'Crear teoria',
        'copy' => 'La leccion necesita contexto antes de que la practica tenga sentido.',
        'url' => url('/profesor/lecciones/' . $leccion->id . '/teoria/create?return_to=' . rawurlencode($previewReturnTo)),
    ];
}

if (!empty($teorias)) {
    foreach ($teorias as $teoriaItem) {
        $summary = Teoria::resumenDocente($teoriaItem);
        if (empty($summary['ready_for_practice'])) {
            $previewQuickFixes[] = [
                'label' => 'Pulir teoria',
                'copy' => $summary['message'],
                'url' => url('/profesor/teoria/edit/' . $teoriaItem->id . '?return_to=' . rawurlencode($previewReturnTo)),
            ];
            break;
        }
    }
}

if (empty($actividades)) {
    $previewQuickFixes[] = [
        'label' => 'Crear actividad',
        'copy' => 'Todavia no existe practica que convierta la teoria en algo medible.',
        'url' => url('/profesor/lecciones/' . $leccion->id . '/actividades/create?return_to=' . rawurlencode($previewReturnTo)),
    ];
}

if (!empty($actividades)) {
    foreach ($actividades as $actividadItem) {
        $summary = Actividad::resumenDocente($actividadItem);
        if (empty($summary['config_ready'])) {
            $previewQuickFixes[] = [
                'label' => 'Configurar actividad',
                'copy' => $summary['message'],
                'url' => url('/profesor/actividad/' . $actividadItem->id . '/configurar?return_to=' . rawurlencode($previewReturnTo)),
            ];
            break;
        }
    }
}

if (empty($previewQuickFixes)) {
    $previewQuickFixes[] = [
        'label' => 'Abrir constructor',
        'copy' => 'La base esta lista. Conviene revisar orden final y publicacion.',
        'url' => url('/profesor/lecciones/' . $leccion->id . '/builder'),
    ];
}
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item active" aria-current="page">Vista completa</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-eye"></i> Vista completa de leccion</span>
        <h1 class="page-title"><?php echo htmlspecialchars($leccion->titulo); ?></h1>
        <p class="page-subtitle">Aqui revisas la experiencia completa antes de poner a un alumno enfrente.</p>
        <?php if (!empty($leccion->descripcion)): ?>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($leccion->descripcion); ?></p>
        <?php endif; ?>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <a href="<?php echo url('/profesor/lecciones/edit/' . $leccion->id); ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Editar leccion
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Teoria</div>
                <div class="metric-value"><?php echo count($teorias); ?></div>
                <div class="metric-note">Piezas teoricas cargadas.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Actividades</div>
                <div class="metric-value"><?php echo count($actividades); ?></div>
                <div class="metric-note">Practicas visibles para el alumno.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Estado</div>
                <div class="metric-value"><?php echo htmlspecialchars(ucfirst($leccion->estado)); ?></div>
                <div class="metric-note">Control actual de publicacion.</div>
            </div>
        </div>
    </section>

    <section class="panel mb-4">
        <div class="panel-body">
            <div class="section-title mb-3">
                <h2>Checklist editorial</h2>
            </div>
            <div class="row g-3">
                <?php foreach ($previewChecklist as $item): ?>
                    <div class="col-lg-3 col-md-6">
                        <article class="surface-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($item['label']); ?></div>
                                    <span class="soft-badge"><?php echo $item['ok'] ? 'OK' : 'Falta'; ?></span>
                                </div>
                                <p class="text-muted mt-2 mb-0"><?php echo htmlspecialchars($item['hint']); ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel mb-4">
        <div class="panel-body">
            <div class="section-title mb-3">
                <h2>Correccion rapida</h2>
                <span class="soft-badge"><i class="bi bi-tools"></i> Sin perder contexto</span>
            </div>
            <div class="publish-checklist-grid">
                <?php foreach ($previewQuickFixes as $quickFix): ?>
                    <article class="publish-check-card">
                        <div class="publish-check-head">
                            <div class="publish-check-title"><?php echo htmlspecialchars($quickFix['label']); ?></div>
                            <span class="soft-badge">Ahora</span>
                        </div>
                        <div class="publish-check-copy"><?php echo htmlspecialchars($quickFix['copy']); ?></div>
                        <div class="mt-3">
                            <a href="<?php echo $quickFix['url']; ?>" class="btn btn-sm btn-outline-primary">Abrir</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="section-title">
            <h2>Teoria</h2>
        </div>
        <?php if (empty($teorias)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-book"></i></span>
                    <div class="empty-state-copy">Esta leccion todavia no tiene teoria.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teorias as $teoria): ?>
                <div class="content-block">
                    <details class="lesson-theory-details" open>
                        <summary class="lesson-theory-summary">
                            <span><?php echo htmlspecialchars($teoria->titulo); ?></span>
                            <span class="soft-badge">Orden <?php echo (int) $teoria->orden; ?></span>
                        </summary>
                        <div class="card-body pt-0">
                            <div class="course-meta mb-3">
                                <span><i class="bi bi-clock"></i> <?php echo (int) $teoria->duracion_minutos; ?> min</span>
                                <span><i class="bi bi-layers"></i> <?php echo !empty($teoria->bloques) ? count($teoria->bloques) : 0; ?> bloques</span>
                            </div>
                            <?php if (!empty($teoria->tiene_bloques) && !empty($teoria->bloques)): ?>
                                <div class="stack-list">
                                    <?php foreach ($teoria->bloques as $bloque): ?>
                                        <div class="stack-item">
                                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                <div>
                                                    <div class="stack-item-title"><?php echo htmlspecialchars($bloque->titulo ?: ucfirst($bloque->tipo_bloque)); ?></div>
                                                    <div class="stack-item-subtitle"><?php echo htmlspecialchars(ucfirst($bloque->tipo_bloque)); ?></div>
                                                </div>
                                                <?php if ((int) ($bloque->tts_habilitado ?? 0) === 1): ?>
                                                    <span class="soft-badge"><i class="bi bi-volume-up"></i> TTS</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($bloque->contenido)): ?>
                                                <div class="mt-2"><?php echo nl2br(htmlspecialchars($bloque->contenido)); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($bloque->ruta_archivo) && !empty($bloque->tipo_media)): ?>
                                                <div class="mt-3">
                                                    <?php echo renderProfessorLessonBlockMedia($bloque); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <?php echo sanitize_rich_html((string) $teoria->contenido); ?>
                            <?php endif; ?>
                        </div>
                    </details>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="mb-4">
        <div class="section-title">
            <h2>Actividades</h2>
        </div>
        <?php if (empty($actividades)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-lightning"></i></span>
                    <div class="empty-state-copy">Esta leccion todavia no tiene actividades.</div>
                </div>
            </div>
        <?php else: ?>
            <ul class="list-group lesson-stack">
                <?php foreach ($actividades as $actividad): ?>
                    <?php $activitySummary = Actividad::resumenDocente($actividad); ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($actividad->titulo); ?></div>
                            <div class="course-meta mt-2">
                                <span><?php echo htmlspecialchars(professorLessonActivityTypeLabel($actividad->tipo_actividad ?? '')); ?></span>
                                <?php if (!empty($actividad->puntos_maximos)): ?>
                                    <span><?php echo (int) $actividad->puntos_maximos; ?> pts</span>
                                <?php endif; ?>
                                <?php if (!empty($actividad->tiempo_limite_minutos)): ?>
                                    <span><?php echo (int) $actividad->tiempo_limite_minutos; ?> min</span>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($activitySummary['label']); ?></span>
                            </div>
                            <?php if (!empty($actividad->descripcion)): ?>
                                <div class="small text-muted mt-2"><?php echo htmlspecialchars($actividad->descripcion); ?></div>
                            <?php endif; ?>
                            <div class="small text-muted mt-2"><?php echo htmlspecialchars($activitySummary['message']); ?></div>
                            <?php echo renderProfessorActivityPreviewSummary($actividad); ?>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/configurar?return_to=' . rawurlencode($previewReturnTo)); ?>" class="btn btn-outline-secondary btn-sm">Configurar</a>
                            <a href="<?php echo url('/profesor/actividad/edit/' . $actividad->id . '?return_to=' . rawurlencode($previewReturnTo)); ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <a href="<?php echo url('/profesor/actividad/' . $actividad->id . '/preview'); ?>" class="btn btn-outline-secondary btn-sm">Vista actividad</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
