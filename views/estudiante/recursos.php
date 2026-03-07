<?php
require_once __DIR__ . '/../partials/header.php';
$selectedLanguage = $_GET['idioma'] ?? '';
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
        </div>
    </section>

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
                <div class="empty-state-copy">Todavia no hay recursos curados para ese idioma.</div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($groupedResources as $category => $items): ?>
            <section class="mb-4">
                <div class="section-title">
                    <h2><?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?></h2>
                </div>
                <div class="row g-4">
                    <?php foreach ($items as $resource): ?>
                        <div class="col-lg-4 col-md-6">
                            <article class="surface-card useful-resource-card h-100">
                                <div class="card-body d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <h3 class="h5 mb-1"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                            <div class="small text-muted"><?php echo htmlspecialchars($resource['badge'] ?? ($resourceCategories[$category] ?? ucfirst($category))); ?></div>
                                        </div>
                                        <span class="soft-badge"><?php echo htmlspecialchars($resourceCategories[$category] ?? ucfirst($category)); ?></span>
                                    </div>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <div class="course-meta">
                                        <?php foreach (($resource['languages'] ?? []) as $languageKey): ?>
                                            <span><i class="bi bi-translate"></i> <?php echo htmlspecialchars(app_language_label($languageKey, ucfirst($languageKey))); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="<?php echo htmlspecialchars($resource['url']); ?>" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-box-arrow-up-right"></i> Abrir recurso
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
