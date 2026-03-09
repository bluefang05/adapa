<?php
require_once __DIR__ . '/../partials/header.php';
$selectedLanguage = $_GET['idioma'] ?? '';
$resourceSources = [];
$resourceSourceCounts = [];
$normalizeResourceText = static function (string $value): string {
    return function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);
};
foreach ($resources as $resource) {
    $sourceLabel = app_url_host_label($resource['url'] ?? '');
    $sourceKey = $normalizeResourceText($sourceLabel);
    $resourceSources[$sourceKey] = $sourceLabel;
    $resourceSourceCounts[$sourceKey] = ($resourceSourceCounts[$sourceKey] ?? 0) + 1;
}
asort($resourceSources);
$totalCategories = count($groupedResources ?? []);
$relatedCourseCount = count($relatedCourses ?? []);
$resourceContextCourseTitle = trim((string) (($resourceContextCourse->titulo ?? '') ?: ''));
$resourceContextCourseLabel = $resourceContextCourseTitle;
if ($resourceContextCourseLabel !== '') {
    if (function_exists('mb_strimwidth')) {
        $resourceContextCourseLabel = mb_strimwidth($resourceContextCourseLabel, 0, 42, '...');
    } elseif (strlen($resourceContextCourseLabel) > 42) {
        $resourceContextCourseLabel = substr($resourceContextCourseLabel, 0, 39) . '...';
    }
}
?>

<div class="container resources-page">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/estudiante'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Recursos utiles</li>
        </ol>
    </nav>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-compass"></i> Caja de herramientas</span>
        <h1 class="page-title">Recursos utiles</h1>
        <p class="page-subtitle">
            <?php if ($resourceContextCourseTitle !== ''): ?>
                Apoyos externos curados para avanzar en <?php echo htmlspecialchars($resourceContextCourseTitle); ?> sin salirte del estudio.
            <?php elseif ($relatedCourseCount > 1): ?>
                Apoyos externos curados para tus cursos de <?php echo htmlspecialchars($languageLabel); ?> sin salirte del estudio.
            <?php else: ?>
                Pronunciacion, diccionarios y apoyos externos curados para destrabar una duda sin salirte del estudio.
            <?php endif; ?>
        </p>
        <div class="hero-actions">
            <a href="<?php echo $resourceContextCourse ? url('/estudiante/cursos/' . (int) $resourceContextCourse->id . '/lecciones') : url('/estudiante'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> <?php echo $resourceContextCourse ? 'Volver al curso' : 'Volver al panel'; ?>
            </a>
            <?php if (!empty($resources)): ?>
                <a href="#resource-filters" class="btn btn-outline-primary">
                    <i class="bi bi-funnel"></i> Ir a filtros
                </a>
            <?php endif; ?>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-translate"></i> <?php echo htmlspecialchars($languageLabel); ?></span>
            <span class="soft-badge"><i class="bi bi-tools"></i> <?php echo count($resources); ?> recursos listados</span>
            <span class="soft-badge"><i class="bi bi-diagram-3"></i> <?php echo $totalCategories; ?> categorias activas</span>
            <?php if ($resourceContextCourseLabel !== ''): ?>
                <span class="soft-badge badge-accent"><i class="bi bi-journal-bookmark"></i> <?php echo htmlspecialchars($resourceContextCourseLabel); ?></span>
            <?php elseif ($relatedCourseCount > 1): ?>
                <span class="soft-badge badge-accent"><i class="bi bi-journal-bookmark"></i> <?php echo $relatedCourseCount; ?> cursos relacionados</span>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($resources)): ?>
        <section class="filter-shell resource-filter-shell mb-4" id="resource-filters">
            <div class="panel-body">
                <?php if (!empty($groupedResources)): ?>
                    <div class="resource-jump-bar">
                        <div class="resource-jump-copy">
                            <div class="metric-label">Accesos rapidos</div>
                            <div class="small text-muted">Salta directo a la necesidad que quieres resolver.</div>
                        </div>
                        <div class="resource-cluster-list resource-jump-strip">
                            <?php foreach ($groupedResources as $category => $items): ?>
                                <?php $categoryId = 'resource-category-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string) $category); ?>
                                <a href="#<?php echo htmlspecialchars($categoryId); ?>" class="resource-cluster-pill resource-cluster-link">
                                    <span class="resource-cluster-icon">
                                        <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($category)); ?>"></i>
                                    </span>
                                    <span class="resource-cluster-body">
                                        <strong><?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?></strong>
                                        <small><?php echo count($items); ?> recurso<?php echo count($items) === 1 ? '' : 's'; ?></small>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="GET" action="<?php echo url('/estudiante/recursos'); ?>">
                    <div class="split-head mb-3">
                        <div>
                            <h2 class="h5 mb-1">Encuentra el apoyo correcto</h2>
                            <div class="small text-muted">Busqueda, necesidad y fuente filtran al instante. El idioma recarga toda la caja.</div>
                        </div>
                        <div class="badge-stack">
                            <span class="soft-badge info"><i class="bi bi-tools"></i> <span id="resourceVisibleCount"><?php echo count($resources); ?></span> visibles</span>
                            <?php if ($selectedLanguage !== ''): ?>
                                <span class="soft-badge"><i class="bi bi-translate"></i> <?php echo htmlspecialchars($languageLabel); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-lg-5">
                            <label for="resourceSearchInput" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="resourceSearchInput" placeholder="Herramienta, fuente o necesidad concreta">
                        </div>
                        <div class="col-xl-3 col-lg-3">
                            <label for="resourceCategoryFilter" class="form-label">Necesidad</label>
                            <select id="resourceCategoryFilter" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($resourceCategories as $categoryKey => $categoryLabel): ?>
                                    <?php $categoryCount = count($groupedResources[$categoryKey] ?? []); ?>
                                    <option value="<?php echo htmlspecialchars($categoryKey); ?>"><?php echo htmlspecialchars($categoryLabel); ?><?php echo $categoryCount > 0 ? ' (' . $categoryCount . ')' : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4">
                            <label for="resourceSourceFilter" class="form-label">Fuente</label>
                            <select id="resourceSourceFilter" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($resourceSources as $sourceKey => $sourceLabel): ?>
                                    <option value="<?php echo htmlspecialchars($sourceKey); ?>"><?php echo htmlspecialchars($sourceLabel); ?> (<?php echo (int) ($resourceSourceCounts[$sourceKey] ?? 0); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-4">
                            <label for="idioma" class="form-label">Idioma</label>
                            <select name="idioma" id="idioma" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach (app_course_target_languages() as $languageValue => $languageName): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $selectedLanguage === $languageValue ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="resource-filter-footer mt-3">
                        <div class="small text-muted">Si solo estas explorando, usa la busqueda rapida. Cambia idioma solo cuando realmente quieras otra biblioteca.</div>
                        <div class="responsive-actions">
                            <button type="button" class="btn btn-outline-secondary" id="resourceClientReset">Restablecer busqueda</button>
                            <button type="submit" class="btn btn-primary">Aplicar idioma</button>
                            <a href="<?php echo url('/estudiante/recursos'); ?>" class="btn btn-outline-secondary">Limpiar todo</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($resources)): ?>
        <div class="panel empty-state-card">
            <div class="panel-body">
                <span class="empty-state-icon"><i class="bi bi-tools"></i></span>
                <div class="empty-state-copy">Todavia no hay recursos curados para ese idioma. Cambia el filtro o vuelve mas tarde cuando se publique mas apoyo.</div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($groupedResources as $category => $items): ?>
            <?php $categoryId = 'resource-category-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string) $category); ?>
            <section class="resource-section mb-4" id="<?php echo htmlspecialchars($categoryId); ?>" data-resource-section>
                <div class="split-head resource-section-head mb-3">
                    <div>
                        <div class="resource-kicker">
                            <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($category)); ?>"></i>
                            <?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?>
                        </div>
                        <h2 class="h5 mb-0"><?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?></h2>
                    </div>
                    <span class="soft-badge" data-resource-section-count data-resource-section-total="<?php echo count($items); ?>">
                        <?php echo count($items); ?> recurso<?php echo count($items) === 1 ? '' : 's'; ?>
                    </span>
                </div>
                <div class="resource-results-grid">
                    <?php foreach ($items as $resource): ?>
                        <?php $sourceLabel = app_url_host_label($resource['url'] ?? ''); ?>
                        <?php $sourceKey = $normalizeResourceText($sourceLabel); ?>
                        <?php
                        $searchText = trim(
                            ($resource['title'] ?? '') . ' ' .
                            ($resource['description'] ?? '') . ' ' .
                            ($resource['best_for'] ?? '') . ' ' .
                            ($resource['badge'] ?? '') . ' ' .
                            $sourceLabel
                        );
                        ?>
                        <article
                            class="surface-card useful-resource-card resource-result-card"
                            data-resource-item
                            data-resource-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
                            data-resource-source="<?php echo htmlspecialchars($sourceKey, ENT_QUOTES, 'UTF-8'); ?>"
                            data-resource-search="<?php echo htmlspecialchars($normalizeResourceText($searchText), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <div class="card-body resource-card-body">
                                <div class="useful-resource-head">
                                    <div>
                                        <div class="resource-kicker">
                                            <i class="bi <?php echo htmlspecialchars(app_useful_resource_category_icon($category)); ?>"></i>
                                            <?php echo htmlspecialchars($resource['badge'] ?? ($resourceCategories[$category] ?? ucfirst($category))); ?>
                                        </div>
                                        <h3 class="h5 mb-0"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    </div>
                                </div>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($resource['description']); ?></p>
                                <?php if (!empty($resource['best_for'])): ?>
                                    <p class="resource-best-for mb-0"><strong>Mejor para:</strong> <?php echo htmlspecialchars($resource['best_for']); ?></p>
                                <?php endif; ?>
                                <div class="resource-card-meta">
                                    <div class="resource-source-meta">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        Fuente: <?php echo htmlspecialchars($sourceLabel); ?>
                                    </div>
                                    <?php if (!empty($resource['languages'])): ?>
                                        <div class="resource-language-list">
                                            <?php foreach (array_slice((array) ($resource['languages'] ?? []), 0, 2) as $languageKey): ?>
                                                <span class="resource-language-pill"><?php echo htmlspecialchars(app_language_label($languageKey, ucfirst($languageKey))); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-auto">
                                    <a href="<?php echo htmlspecialchars($resource['url']); ?>" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-box-arrow-up-right"></i> <?php echo htmlspecialchars($resource['cta_label'] ?? 'Abrir recurso'); ?>
                                    </a>
                                </div>
                            </div>
                        </article>
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
    const resetButton = document.getElementById('resourceClientReset');
    const items = Array.from(document.querySelectorAll('[data-resource-item]'));
    const sections = Array.from(document.querySelectorAll('[data-resource-section]'));
    const emptyState = document.getElementById('resourceFilterEmpty');
    const visibleCountEl = document.getElementById('resourceVisibleCount');

    if (!searchInput || !categoryFilter || !sourceFilter || !items.length) {
        return;
    }

    function resourceLabel(count) {
        return count === 1 ? 'recurso' : 'recursos';
    }

    function applyFilters() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const selectedCategory = categoryFilter.value || '';
        const selectedSource = sourceFilter.value || '';
        const hasClientFilters = query !== '' || selectedCategory !== '' || selectedSource !== '';
        let visibleItemsCount = 0;

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
                visibleItemsCount += 1;
            }
        });

        sections.forEach(function (section) {
            const visibleItems = section.querySelectorAll('[data-resource-item]:not([hidden])').length;
            const countBadge = section.querySelector('[data-resource-section-count]');
            const total = Number((countBadge && countBadge.getAttribute('data-resource-section-total')) || visibleItems);

            section.hidden = visibleItems === 0;

            if (countBadge) {
                countBadge.textContent = hasClientFilters
                    ? visibleItems + ' visible' + (visibleItems === 1 ? '' : 's')
                    : total + ' ' + resourceLabel(total);
            }
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = hasClientFilters
                ? visibleItemsCount + ' de <?php echo count($resources); ?>'
                : String(visibleItemsCount);
        }

        if (emptyState) {
            emptyState.hidden = visibleItemsCount !== 0;
        }
    }

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyFilters();
        }
    });
    searchInput.addEventListener('input', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    sourceFilter.addEventListener('change', applyFilters);
    if (resetButton) {
        resetButton.addEventListener('click', function () {
            searchInput.value = '';
            categoryFilter.value = '';
            sourceFilter.value = '';
            applyFilters();
            searchInput.focus();
        });
    }
    applyFilters();
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
