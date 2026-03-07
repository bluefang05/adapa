<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . ($leccion->curso_id ?? '') . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . ($leccion->id ?? $teoria->leccion_id) . '/teoria'); ?>">Teoria</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar teoria</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-pencil-square"></i> Ajuste teorico</span>
        <h1 class="page-title">Edita la teoria y sus bloques sin romper el orden de la leccion.</h1>
        <p class="page-subtitle">
            Puedes mantener el contenido clasico y, al mismo tiempo, estructurar partes del material en bloques con idioma y TTS.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/lecciones/' . ($leccion->id ?? $teoria->leccion_id) . '/teoria'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a teoria
            </a>
            <a href="<?php echo url('/profesor/recursos'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Abrir biblioteca
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check2-circle"></i>
            Recurso listo para insertar: <strong><?php echo htmlspecialchars((string) ($_GET['selected_media_title'] ?? 'Recurso seleccionado')); ?></strong>.
            Lo asignare al primer bloque sin recurso de esta teoria.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Ficha teorica</h2>
                                <span class="soft-badge">Orden <?php echo (int) $teoria->orden; ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la teoria</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($teoria->titulo); ?>" required>
                                <div class="invalid-feedback">Por favor ingrese un titulo.</div>
                            </div>

                            <div class="mb-3">
                                <label for="contenido" class="form-label">Contenido clasico</label>
                                <textarea class="form-control" id="contenido" name="contenido" rows="10"><?php echo htmlspecialchars($teoria->contenido); ?></textarea>
                                <div class="form-text">Si usas bloques, este contenido puede servir como respaldo o version legible completa.</div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Bloques de contenido</h2>
                                <span class="soft-badge"><i class="bi bi-collection"></i> Autoría guiada</span>
                            </div>

                            <div class="panel mb-3">
                                <div class="panel-body">
                                    <div class="builder-toolbar">
                                        <button type="button" class="btn btn-outline-primary" id="addBlockBtn">
                                            <i class="bi bi-plus-circle"></i> Anadir bloque
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="generateBlocksBtn">
                                            <i class="bi bi-magic"></i> Regenerar desde parrafos
                                        </button>
                                    </div>
                                    <div class="form-text mt-3">
                                        Los bloques existentes pueden editarse, quitarse o regenerarse desde el contenido clasico.
                                    </div>
                                    <div class="production-hint-card tone-info mt-3">
                                        <div class="production-hint-title">Checklist rapido antes de actualizar</div>
                                        <ul class="quality-checklist-list mb-0">
                                            <li>La teoria sigue teniendo una idea central clara.</li>
                                            <li>Los bloques multimedia apoyan, no distraen.</li>
                                            <li>La secuencia se entiende sin que el alumno adivine el siguiente paso.</li>
                                        </ul>
                                    </div>
                                    <div class="template-chip-group mt-3">
                                        <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/teoria/edit/' . $teoria->id)) . '&context=teoria'); ?>" class="template-chip template-chip-link">Elegir recurso en biblioteca</a>
                                    </div>
                                </div>
                            </div>

                            <div id="blocksBuilder" class="config-builder"></div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Parametros</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Publicacion</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="tipo_contenido" class="form-label">Tipo de contenido</label>
                                    <select class="form-select" id="tipo_contenido" name="tipo_contenido" required>
                                        <option value="texto" <?php echo ($teoria->tipo_contenido === 'texto') ? 'selected' : ''; ?>>Texto</option>
                                        <option value="video" <?php echo ($teoria->tipo_contenido === 'video') ? 'selected' : ''; ?>>Video</option>
                                        <option value="imagen" <?php echo ($teoria->tipo_contenido === 'imagen') ? 'selected' : ''; ?>>Imagen</option>
                                        <option value="audio" <?php echo ($teoria->tipo_contenido === 'audio') ? 'selected' : ''; ?>>Audio</option>
                                        <option value="mixto" <?php echo ($teoria->tipo_contenido === 'mixto') ? 'selected' : ''; ?>>Mixto</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="1" value="<?php echo htmlspecialchars($teoria->orden); ?>" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="duracion_minutos" class="form-label">Duracion en minutos</label>
                                    <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="1" value="<?php echo htmlspecialchars($teoria->duracion_minutos); ?>" required>
                                </div>
                            </div>
                        </section>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/lecciones/' . ($leccion->id ?? $teoria->leccion_id) . '/teoria'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar teoria
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="blockTemplate">
    <div class="builder-item content-block-item">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
            <div>
                <div class="stack-item-title">Bloque de teoria</div>
                <div class="stack-item-subtitle">Explicacion estructurada con idioma y audio opcional.</div>
            </div>
            <div class="block-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary move-up-block-btn" title="Mover arriba">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary move-down-block-btn" title="Mover abajo">
                    <i class="bi bi-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-block-btn">
                    <i class="bi bi-trash"></i> Quitar
                </button>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select class="form-select" data-field="tipo">
                    <option value="explicacion">Explicacion</option>
                    <option value="ejemplo">Ejemplo</option>
                    <option value="traduccion">Traduccion</option>
                    <option value="vocabulario">Vocabulario</option>
                    <option value="dialogo">Dialogo</option>
                    <option value="instruccion">Instruccion</option>
                    <option value="recurso">Recurso</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Idioma del bloque</label>
                <select class="form-select" data-field="idioma">
                    <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                        <option value="<?php echo htmlspecialchars($languageValue); ?>"><?php echo htmlspecialchars($languageLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" data-field="tts">
                    <label class="form-check-label">Habilitar TTS</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Recurso multimedia</label>
                <select class="form-select" data-field="media">
                    <option value="">Sin recurso asociado</option>
                    <?php foreach ($recursos as $recurso): ?>
                        <option value="<?php echo (int) $recurso->id; ?>">
                            <?php echo htmlspecialchars($recurso->titulo . ' (' . $recurso->tipo_media . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="block-media-gallery mt-3" data-role="media-gallery"></div>
                <div class="block-media-preview is-empty mt-3" data-role="media-preview">
                    <span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin recurso seleccionado</span>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Titulo opcional</label>
                <input type="text" class="form-control" data-field="titulo" placeholder="Ejemplo: Frase clave o nota cultural">
            </div>
            <div class="col-12">
                <label class="form-label">Contenido</label>
                <textarea class="form-control" rows="4" data-field="contenido" placeholder="Escribe aqui el texto del bloque"></textarea>
            </div>
        </div>
    </div>
</template>

<script>
window.initialTheoryBlocks = <?php
echo json_encode(array_map(function ($bloque) {
    return [
        'tipo' => $bloque->tipo_bloque ?? 'explicacion',
        'titulo' => $bloque->titulo ?? '',
        'contenido' => $bloque->contenido ?? '',
        'idioma' => $bloque->idioma_bloque ?? 'espanol',
        'tts' => !empty($bloque->tts_habilitado),
        'media_id' => $bloque->media_id ?? '',
    ];
}, $teoria->bloques ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;
window.availableMediaResources = <?php
echo json_encode(array_reduce($recursos, function ($carry, $recurso) {
    $metadata = app_media_metadata($recurso->metadata ?? null);
    $carry[$recurso->id] = [
        'id' => (int) $recurso->id,
        'titulo' => $recurso->titulo,
        'tipo_media' => $recurso->tipo_media,
        'ruta_archivo' => app_media_public_url($recurso->ruta_archivo),
        'alt_text' => $recurso->alt_text ?: $recurso->titulo,
        'embed_url' => $metadata['embed_url'] ?? null,
        'is_vertical_embed' => strpos((string) $recurso->ruta_archivo, '/shorts/') !== false || (($metadata['layout'] ?? null) === 'vertical'),
    ];
    return $carry;
}, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#contenido',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    language: 'es',
    height: 360,
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});

(function () {
    const builder = document.getElementById('blocksBuilder');
    const template = document.getElementById('blockTemplate');
    const addBlockBtn = document.getElementById('addBlockBtn');
    const generateBlocksBtn = document.getElementById('generateBlocksBtn');
    const initialBlocks = Array.isArray(window.initialTheoryBlocks) ? window.initialTheoryBlocks : [];
    const mediaResources = window.availableMediaResources || {};
    const selectedMediaIdFromQuery = new URLSearchParams(window.location.search).get('selected_media_id');

    function renderMediaPreview(resource) {
        if (!resource) {
            return '<span class="course-cover-placeholder"><i class="bi bi-image"></i> Sin recurso seleccionado</span>';
        }

        if (resource.tipo_media === 'imagen') {
            return '<img src="' + resource.ruta_archivo + '" alt="' + resource.alt_text + '" class="block-media-thumb">';
        }

        if (resource.embed_url) {
            const frameClass = resource.is_vertical_embed ? 'media-embed-frame is-vertical' : 'media-embed-frame';
            return '<div class="' + frameClass + '"><iframe src="' + resource.embed_url + '" title="' + resource.alt_text + '" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>'
                + '<div class="mt-2"><a href="' + resource.ruta_archivo + '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> Abrir video</a></div>';
        }

        if (resource.tipo_media === 'audio') {
            return '<audio controls preload="none" class="media-preview-player"><source src="' + resource.ruta_archivo + '"></audio>';
        }

        if (resource.tipo_media === 'video') {
            return '<video controls preload="metadata" class="media-preview-player"><source src="' + resource.ruta_archivo + '"></video>';
        }

        const icon = resource.tipo_media === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-paperclip';
        return '<div class="media-preview-file"><i class="bi ' + icon + '"></i><span>' + resource.titulo + '</span></div>';
    }

    function renderMediaGallery(block) {
        const gallery = block.querySelector('[data-role="media-gallery"]');
        const select = block.querySelector('[data-field="media"]');
        const selectedId = select.value || '';
        const items = Object.values(mediaResources);

        if (!items.length) {
            gallery.innerHTML = '';
            return;
        }

        gallery.innerHTML = items.map(function (resource) {
            const isActive = String(resource.id) === selectedId;
            const iconMap = {
                imagen: 'bi-image',
                audio: 'bi-mic',
                video: 'bi-camera-video',
                pdf: 'bi-file-earmark-pdf',
                documento: 'bi-paperclip'
            };

            return '<button type="button" class="media-chip' + (isActive ? ' is-active' : '') + '" data-media-chip="' + resource.id + '">' +
                '<i class="bi ' + (iconMap[resource.tipo_media] || 'bi-paperclip') + '"></i>' +
                '<span>' + resource.titulo + '</span>' +
                '</button>';
        }).join('');

        gallery.querySelectorAll('[data-media-chip]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                select.value = chip.getAttribute('data-media-chip');
                updateMediaPreview(block);
                renderMediaGallery(block);
            });
        });
    }

    function updateMediaPreview(block) {
        const select = block.querySelector('[data-field="media"]');
        const preview = block.querySelector('[data-role="media-preview"]');
        const resource = mediaResources[select.value] || null;

        preview.classList.toggle('is-empty', !resource);
        preview.innerHTML = renderMediaPreview(resource);
    }

    function bindBlock(block) {
        block.querySelector('.remove-block-btn').addEventListener('click', function () {
            block.remove();
        });

        block.querySelector('.move-up-block-btn').addEventListener('click', function () {
            const previous = block.previousElementSibling;
            if (previous) {
                builder.insertBefore(block, previous);
            }
        });

        block.querySelector('.move-down-block-btn').addEventListener('click', function () {
            const next = block.nextElementSibling;
            if (next) {
                builder.insertBefore(next, block);
            }
        });

        block.querySelector('[data-field="media"]').addEventListener('change', function () {
            updateMediaPreview(block);
            renderMediaGallery(block);
        });

        renderMediaGallery(block);
        updateMediaPreview(block);
    }

    function addBlock(data) {
        const fragment = template.content.cloneNode(true);
        const block = fragment.querySelector('.content-block-item');

        block.querySelector('[data-field="tipo"]').name = 'bloque_tipo[]';
        block.querySelector('[data-field="titulo"]').name = 'bloque_titulo[]';
        block.querySelector('[data-field="contenido"]').name = 'bloque_contenido[]';
        block.querySelector('[data-field="idioma"]').name = 'bloque_idioma[]';
        block.querySelector('[data-field="tts"]').name = 'bloque_tts[]';
        block.querySelector('[data-field="media"]').name = 'bloque_media_id[]';
        block.querySelector('[data-field="tts"]').value = '1';

        if (data) {
            block.querySelector('[data-field="tipo"]').value = data.tipo || 'explicacion';
            block.querySelector('[data-field="titulo"]').value = data.titulo || '';
            block.querySelector('[data-field="contenido"]').value = data.contenido || '';
            block.querySelector('[data-field="idioma"]').value = data.idioma || 'espanol';
            block.querySelector('[data-field="media"]').value = data.media_id || '';
            block.querySelector('[data-field="tts"]').checked = !!data.tts;
        }

        bindBlock(block);
        builder.appendChild(block);
    }

    if (initialBlocks.length) {
        initialBlocks.forEach(addBlock);
    } else {
        addBlock();
    }

    if (selectedMediaIdFromQuery && mediaResources[selectedMediaIdFromQuery]) {
        const firstEmptySelect = Array.from(builder.querySelectorAll('[data-field="media"]')).find(function (select) {
            return !select.value;
        });
        const targetSelect = firstEmptySelect || builder.querySelector('[data-field="media"]');
        if (targetSelect) {
            targetSelect.value = selectedMediaIdFromQuery;
            updateMediaPreview(targetSelect.closest('.content-block-item'));
            renderMediaGallery(targetSelect.closest('.content-block-item'));
        }
    }

    addBlockBtn.addEventListener('click', function () {
        addBlock();
    });

    generateBlocksBtn.addEventListener('click', function () {
        if (tinymce.get('contenido')) {
            tinymce.triggerSave();
        }

        const source = document.getElementById('contenido').value
            .split(/\n\s*\n/)
            .map(function (part) { return part.replace(/\s+/g, ' ').trim(); })
            .filter(Boolean);

        if (!source.length) {
            return;
        }

        builder.innerHTML = '';
        source.forEach(function (paragraph) {
            addBlock({ tipo: 'explicacion', contenido: paragraph, idioma: 'espanol', tts: false });
        });
    });
})();

(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (tinymce.get('contenido')) {
                    tinymce.triggerSave();
                }

                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
