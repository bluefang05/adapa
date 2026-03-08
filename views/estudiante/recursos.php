<?php
require_once __DIR__ . '/../partials/header.php';
$selectedLanguage = $_GET['idioma'] ?? '';
$spotlightResources = array_slice($resources, 0, min(3, count($resources)));
$videoPolicy = app_media_external_video_policy();
$resourceSources = [];
foreach ($resources as $resource) {
    $sourceLabel = app_url_host_label($resource['url'] ?? '');
    $sourceKey = function_exists('mb_strtolower')
        ? mb_strtolower($sourceLabel, 'UTF-8')
        : strtolower($sourceLabel);
    $resourceSources[$sourceKey] = $sourceLabel;
}
asort($resourceSources);
?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/estudiante'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recursos utiles</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-compass"></i> Caja de herramientas</span>
        <h1 class="page-title">Recursos utiles para salir del atasco.</h1>
        <p class="page-subtitle">Pronunciacion, diccionarios y apoyos externos curados para seguir avanzando sin romper tu flujo.</p>
        <div class="hero-actions">
            <a href="<?php echo url('/estudiante'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al panel
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Idioma filtrado</div>
                <div class="metric-value"><?php echo htmlspecialchars($languageLabel); ?></div>
                <div class="metric-note">Ajusta la curacion segun tu ruta actual.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Recursos listados</div>
                <div class="metric-value"><?php echo count($resources); ?></div>
                <div class="metric-note">Atajos externos para pronunciacion, diccionario y escucha.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Categorias activas</div>
                <div class="metric-value"><?php echo count($groupedResources); ?></div>
                <div class="metric-note">Ayuda a encontrar apoyo rapido sin salirte demasiado del recorrido.</div>
            </div>
        </div>
    </section>

    <section class="panel mb-4 resource-guidance-panel">
        <div class="panel-body">
            <div class="section-title mb-3">
                <h2>Como usar esta caja de herramientas</h2>
                <span class="soft-badge badge-accent"><i class="bi bi-stars"></i> Apoyo externo</span>
            </div>
            <div class="resource-guidance-grid">
                <article class="resource-guidance-card">
                    <div class="resource-guidance-icon"><i class="bi bi-compass"></i></div>
                    <div>
                        <h3>Usalo para desbloquearte</h3>
                        <p>Si una palabra, una conjugacion o una pronunciacion no cae, abre uno de estos recursos, resuelve la duda y vuelve al curso.</p>
                    </div>
                </article>
                <article class="resource-guidance-card">
                    <div class="resource-guidance-icon"><i class="bi bi-youtube"></i></div>
                    <div>
                        <h3>Video externo oficial</h3>
                        <p><?php echo htmlspecialchars($videoPolicy['provider']); ?> es la fuente externa oficial para video dentro de ADAPA. Estas herramientas son apoyo externo, no reemplazo del recorrido.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <?php if (!empty($groupedResources)): ?>
        <section class="panel mb-4">
            <div class="panel-body">
                <div class="section-title mb-3">
                    <h2>Rutas rapidas por necesidad</h2>
                </div>
                <div class="resource-cluster-list">
                    <?php foreach ($groupedResources as $category => $items): ?>
                        <div class="resource-cluster-pill">
                            <span class="resource-cluster-icon">
                                <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($category)); ?>"></i>
                            </span>
                            <span class="resource-cluster-body">
                                <strong><?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?></strong>
                                <small><?php echo count($items); ?> recurso<?php echo count($items) === 1 ? '' : 's'; ?> listo<?php echo count($items) === 1 ? '' : 's'; ?></small>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($resources)): ?>
        <section class="panel mb-4">
            <div class="panel-body">
                <div class="section-title mb-3">
                    <h2>Encuentra el apoyo correcto</h2>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label for="resourceSearchInput" class="form-label">Buscar dentro de esta caja</label>
                        <input type="text" class="form-control" id="resourceSearchInput" placeholder="Pronunciacion, diccionario, nombre de la herramienta o duda concreta">
                    </div>
                    <div class="col-lg-4">
                        <label for="resourceCategoryFilter" class="form-label">Filtrar por necesidad</label>
                        <select id="resourceCategoryFilter" class="form-select">
                            <option value="">Todas las categorias</option>
                            <?php foreach ($resourceCategories as $categoryKey => $categoryLabel): ?>
                                <option value="<?php echo htmlspecialchars($categoryKey); ?>"><?php echo htmlspecialchars($categoryLabel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="resourceSourceFilter" class="form-label">Fuente curada</label>
                        <select id="resourceSourceFilter" class="form-select">
                            <option value="">Todas las fuentes</option>
                            <?php foreach ($resourceSources as $sourceKey => $sourceLabel): ?>
                                <option value="<?php echo htmlspecialchars($sourceKey); ?>"><?php echo htmlspecialchars($sourceLabel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-text mt-3">Usa esta busqueda cuando ya sabes si necesitas pronunciacion, diccionario, escucha o apoyo guiado.</div>
                <?php if (!empty($resourceSources)): ?>
                    <div class="resource-source-strip mt-3">
                        <?php foreach ($resourceSources as $sourceLabel): ?>
                            <span class="resource-source-pill">
                                <i class="bi bi-patch-check"></i>
                                <?php echo htmlspecialchars($sourceLabel); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel mb-4">
        <div class="panel-body">
            <form method="GET" action="<?php echo url('/estudiante/recursos'); ?>" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label for="idioma" class="form-label">Idioma objetivo</label>
                    <select name="idioma" id="idioma" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (app_course_target_languages() as $languageValue => $languageName): ?>
                            <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $selectedLanguage === $languageValue ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($languageName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-8 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Aplicar filtro</button>
                    <a href="<?php echo url('/estudiante/recursos'); ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </section>

    <?php if (empty($resources)): ?>
        <div class="panel empty-state-card">
            <div class="panel-body">
                <span class="empty-state-icon"><i class="bi bi-tools"></i></span>
                <div class="empty-state-copy">Todavia no hay recursos curados para ese idioma. Cambia el filtro o vuelve mas tarde cuando se publique mas apoyo.</div>
            </div>
        </div>
    <?php else: ?>
        <?php if (!empty($spotlightResources)): ?>
            <section class="mb-4" data-resource-section>
                <div class="section-title">
                    <h2>Empieza por aqui</h2>
                </div>
                <div class="resource-spotlight-grid">
                    <?php foreach ($spotlightResources as $resource): ?>
                        <?php $sourceLabel = app_url_host_label($resource['url'] ?? ''); ?>
                        <article
                            class="resource-spotlight-card"
                            data-resource-item
                            data-resource-category="<?php echo htmlspecialchars($resource['category'] ?? 'apoyo', ENT_QUOTES, 'UTF-8'); ?>"
                            data-resource-source="<?php echo htmlspecialchars(mb_strtolower($sourceLabel, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-resource-search="<?php echo htmlspecialchars(mb_strtolower(trim(($resource['title'] ?? '') . ' ' . ($resource['description'] ?? '') . ' ' . ($resource['best_for'] ?? '') . ' ' . ($resource['badge'] ?? '') . ' ' . $sourceLabel), 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <div class="resource-spotlight-head">
                                <span class="resource-category-pill">
                                    <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($resource['category'] ?? 'apoyo')); ?>"></i>
                                    <?php echo htmlspecialchars($resource['badge'] ?? ($resourceCategories[$resource['category']] ?? ucfirst($resource['category'] ?? 'Apoyo'))); ?>
                                </span>
                                <span class="soft-badge"><?php echo htmlspecialchars($resourceCategories[$resource['category']] ?? ucfirst($resource['category'] ?? 'Apoyo')); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($resource['description']); ?></p>
                            <?php if (!empty($resource['best_for'])): ?>
                                <div class="resource-best-for">
                                    <strong>Mejor para:</strong>
                                    <span><?php echo htmlspecialchars($resource['best_for']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="resource-source-meta">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Fuente: <?php echo htmlspecialchars($sourceLabel); ?>
                            </div>
                            <div class="mt-auto">
                                <a href="<?php echo htmlspecialchars($resource['url']); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                    <i class="bi bi-box-arrow-up-right"></i> <?php echo htmlspecialchars($resource['cta_label'] ?? 'Abrir recurso'); ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php foreach ($groupedResources as $category => $items): ?>
            <section class="mb-4" data-resource-section>
                <div class="section-title">
                    <h2>
                        <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($category)); ?>"></i>
                        <?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?>
                    </h2>
                </div>
                <div class="row g-4">
                    <?php foreach ($items as $resource): ?>
                        <?php $sourceLabel = app_url_host_label($resource['url'] ?? ''); ?>
                        <div
                            class="col-lg-4 col-md-6"
                            data-resource-item
                            data-resource-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
                            data-resource-source="<?php echo htmlspecialchars(mb_strtolower($sourceLabel, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>"
                            data-resource-search="<?php echo htmlspecialchars(mb_strtolower(trim(($resource['title'] ?? '') . ' ' . ($resource['description'] ?? '') . ' ' . ($resource['best_for'] ?? '') . ' ' . ($resource['badge'] ?? '') . ' ' . $sourceLabel), 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <article class="surface-card useful-resource-card h-100">
                                <div class="card-body d-flex flex-column gap-3">
                                    <div class="useful-resource-head">
                                        <div>
                                            <div class="resource-kicker">
                                                <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($category)); ?>"></i>
                                                <?php echo htmlspecialchars($resource['badge'] ?? ($resourceCategories[$category] ?? ucfirst($category))); ?>
                                            </div>
                                            <h3 class="h5 mb-1"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                        </div>
                                        <span class="soft-badge"><?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?></span>
                                    </div>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <?php if (!empty($resource['best_for'])): ?>
                                        <div class="resource-best-for">
                                            <strong>Mejor para:</strong>
                                            <span><?php echo htmlspecialchars($resource['best_for']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="resource-source-meta">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        Fuente: <?php echo htmlspecialchars($sourceLabel); ?>
                                    </div>
                                    <div class="course-meta">
                                        <?php foreach (($resource['languages'] ?? []) as $languageKey): ?>
                                            <span><i class="bi bi-translate"></i> <?php echo htmlspecialchars(app_language_label($languageKey, ucfirst($languageKey))); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="<?php echo htmlspecialchars($resource['url']); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-box-arrow-up-right"></i> <?php echo htmlspecialchars($resource['cta_label'] ?? 'Abrir recurso'); ?>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
        <div class="media-filter-empty" id="resourceFilterEmpty" hidden>
            <i class="bi bi-search"></i>
            <div>No hay recursos que coincidan con esa busqueda.</div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($resources)): ?>
<script>
(function () {
    const searchInput = document.getElementById('resourceSearchInput');
    const categoryFilter = document.getElementById('resourceCategoryFilter');
    const sourceFilter = document.getElementById('resourceSourceFilter');
    const items = Array.from(document.querySelectorAll('[data-resource-item]'));
    const sections = Array.from(document.querySelectorAll('[data-resource-section]'));
    const emptyState = document.getElementById('resourceFilterEmpty');

    if (!searchInput || !categoryFilter || !sourceFilter || !items.length) {
        return;
    }

    function applyFilters() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const selectedCategory = categoryFilter.value || '';
        const selectedSource = sourceFilter.value || '';
        let visibleCount = 0;

        items.forEach(function (item) {
            const haystack = (item.getAttribute('data-resource-search') || '').toLowerCase();
            const itemCategory = item.getAttribute('data-resource-category') || '';
            const itemSource = (item.getAttribute('data-resource-source') || '').toLowerCase();
            const matchesQuery = query === '' || haystack.indexOf(query) !== -1;
            const matchesCategory = selectedCategory === '' || itemCategory === selectedCategory;
            const matchesSource = selectedSource === '' || itemSource === selectedSource;
            const isVisible = matchesQuery && matchesCategory && matchesSource;

            item.hidden = !isVisible;
            if (isVisible) {
                visibleCount += 1;
            }
        });

        sections.forEach(function (section) {
            const visibleItems = section.querySelectorAll('[data-resource-item]:not([hidden])').length;
            section.hidden = visibleItems === 0;
        });

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }
    }

    searchInput.addEventListener('input', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    sourceFilter.addEventListener('change', applyFilters);
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
