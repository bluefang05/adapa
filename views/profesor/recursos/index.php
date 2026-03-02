<?php
require_once __DIR__ . '/../../partials/header.php';

function renderMediaPreview($recurso) {
    $assetUrl = url('/' . ltrim($recurso->ruta_archivo, '/'));
    $alt = htmlspecialchars($recurso->alt_text ?: $recurso->titulo, ENT_QUOTES, 'UTF-8');

    if ($recurso->tipo_media === 'imagen') {
        return '<img src="' . htmlspecialchars($assetUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt . '" class="media-preview-thumb">';
    }

    if ($recurso->tipo_media === 'audio') {
        return '<audio controls preload="none" class="media-preview-player"><source src="' . htmlspecialchars($assetUrl, ENT_QUOTES, 'UTF-8') . '"></audio>';
    }

    if ($recurso->tipo_media === 'video') {
        return '<video controls preload="metadata" class="media-preview-player"><source src="' . htmlspecialchars($assetUrl, ENT_QUOTES, 'UTF-8') . '"></video>';
    }

    if ($recurso->tipo_media === 'pdf') {
        return '<div class="media-preview-file"><i class="bi bi-file-earmark-pdf"></i><span>PDF listo para abrir</span></div>';
    }

    return '<div class="media-preview-file"><i class="bi bi-paperclip"></i><span>Documento adjunto</span></div>';
}
?>

<div class="container">
    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-images"></i> Biblioteca</span>
        <h1 class="page-title">Centraliza los recursos del profesor.</h1>
        <p class="page-subtitle">
            Sube imagenes, audios, videos o documentos para reutilizarlos luego en bloques de teoria y otros contenidos.
        </p>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Recursos</div>
                <div class="metric-value"><?php echo count($recursos); ?></div>
                <div class="metric-note">Elementos listos para reutilizar.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Imagenes</div>
                <div class="metric-value"><?php echo count(array_filter($recursos, fn($item) => $item->tipo_media === 'imagen')); ?></div>
                <div class="metric-note">Apoyo visual del curso.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Audios</div>
                <div class="metric-value"><?php echo count(array_filter($recursos, fn($item) => $item->tipo_media === 'audio')); ?></div>
                <div class="metric-note">Material reutilizable para escucha o TTS.</div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="form-shell">
                <div class="card-body">
                    <div class="section-title">
                        <h2 class="form-section-title">Subir recurso</h2>
                        <span class="soft-badge"><i class="bi bi-cloud-upload"></i> Nuevo</span>
                    </div>
                    <form method="POST" action="<?php echo url('/profesor/recursos'); ?>" enctype="multipart/form-data">
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Titulo</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ejemplo: Imagen de restaurante">
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Contexto o uso sugerido"></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="idioma" class="form-label">Idioma relacionado</label>
                                <select class="form-select" id="idioma" name="idioma">
                                    <option value="">Sin idioma</option>
                                    <option value="espanol">Espanol</option>
                                    <option value="ingles">Ingles</option>
                                    <option value="frances">Frances</option>
                                    <option value="aleman">Aleman</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="alt_text" class="form-label">Texto alternativo</label>
                                <input type="text" class="form-control" id="alt_text" name="alt_text" placeholder="Describe el recurso">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="archivo" class="form-label">Archivo</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" required>
                            <div class="form-text">Formatos comunes: imagen, audio, video, PDF o documento.</div>
                        </div>
                        <div class="responsive-actions mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload"></i> Subir recurso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="panel mb-3">
                <div class="panel-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label for="mediaSearchInput" class="form-label">Buscar en la biblioteca</label>
                            <input type="text" class="form-control" id="mediaSearchInput" placeholder="Titulo, descripcion o nombre del archivo">
                        </div>
                        <div class="col-md-5">
                            <label for="mediaTypeFilter" class="form-label">Filtrar por tipo</label>
                            <select class="form-select" id="mediaTypeFilter">
                                <option value="">Todos los tipos</option>
                                <option value="imagen">Imagen</option>
                                <option value="audio">Audio</option>
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                                <option value="documento">Documento</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-text mt-3">Filtra rapidamente antes de reutilizar recursos en bloques, portadas o material de apoyo.</div>
                </div>
            </div>
            <div class="data-table-shell">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="mediaLibraryTable">
                        <thead>
                            <tr>
                                <th>Recurso</th>
                                <th>Tipo</th>
                                <th>Idioma</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recursos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">Todavia no has subido recursos.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recursos as $recurso): ?>
                                    <tr
                                        data-media-row
                                        data-media-type="<?php echo htmlspecialchars($recurso->tipo_media, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-media-search="<?php echo htmlspecialchars(mb_strtolower(trim(($recurso->titulo ?? '') . ' ' . ($recurso->descripcion ?? '') . ' ' . basename($recurso->ruta_archivo ?? '')), 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($recurso->titulo); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($recurso->descripcion ?: basename($recurso->ruta_archivo)); ?></div>
                                            <div class="mt-3">
                                                <?php echo renderMediaPreview($recurso); ?>
                                            </div>
                                        </td>
                                        <td><span class="soft-badge"><?php echo htmlspecialchars(ucfirst($recurso->tipo_media)); ?></span></td>
                                        <td><?php echo htmlspecialchars($recurso->idioma ? ucfirst($recurso->idioma) : 'General'); ?></td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a href="<?php echo htmlspecialchars(url('/' . ltrim($recurso->ruta_archivo, '/'))); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <form method="POST" action="<?php echo url('/profesor/recursos/delete/' . $recurso->id); ?>" onsubmit="return confirm('Estas seguro de eliminar este recurso?');">
                                                    <?php echo csrf_input(); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('mediaSearchInput');
    const typeFilter = document.getElementById('mediaTypeFilter');
    const rows = Array.from(document.querySelectorAll('[data-media-row]'));

    if (!searchInput || !typeFilter || !rows.length) {
        return;
    }

    function applyFilters() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const selectedType = typeFilter.value || '';

        rows.forEach(function (row) {
            const rowType = row.getAttribute('data-media-type') || '';
            const rowSearch = (row.getAttribute('data-media-search') || '').toLowerCase();
            const matchesQuery = query === '' || rowSearch.indexOf(query) !== -1;
            const matchesType = selectedType === '' || rowType === selectedType;

            row.style.display = matchesQuery && matchesType ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
})();
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
