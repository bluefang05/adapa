<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../models/Curso.php';
?>

<div class="container">
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-stars"></i> Panel de aprendizaje</span>
        <h1 class="page-title">Tu progreso ya tiene una vista util.</h1>
        <p class="page-subtitle">
            Continua cursos activos, revisa avance real y detecta rapidamente que contenido te falta por cerrar.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/estudiante/progreso'); ?>" class="btn btn-primary">
                <i class="bi bi-graph-up-arrow"></i> Ver progreso
            </a>
            <a href="<?php echo url('/estudiante/calificaciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-award"></i> Ver calificaciones
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Cursos inscritos</div>
                <div class="metric-value"><?php echo count($cursosInscritos); ?></div>
                <div class="metric-note">Tu espacio activo de estudio.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Disponibles</div>
                <div class="metric-value"><?php echo count($cursosDisponibles); ?></div>
                <div class="metric-note">Cursos gratis que puedes iniciar ahora.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Promedio de avance</div>
                <div class="metric-value">
                    <?php
                    $avgProgress = 0;
                    if (!empty($cursosInscritos)) {
                        $sum = array_reduce($cursosInscritos, function ($carry, $curso) {
                            return $carry + (int) ($curso->porcentaje ?? 0);
                        }, 0);
                        $avgProgress = (int) round($sum / count($cursosInscritos));
                    }
                    echo $avgProgress;
                    ?>%
                </div>
                <div class="metric-note">Promedio entre todos tus cursos.</div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="panel">
                    <div class="panel-body">
                        <div class="section-title mb-3">
                            <h2>Tengo un codigo</h2>
                            <span class="soft-badge"><i class="bi bi-key-fill"></i> Acceso privado</span>
                        </div>
                        <p class="page-subtitle mb-3">
                            Si tu profesor te compartio un codigo, pegalo aqui para activar su clase sin buscarla en el catalogo abierto.
                        </p>
                        <form method="POST" action="<?php echo url('/estudiante/codigo'); ?>" class="form-shell border-0 shadow-none bg-transparent p-0">
                            <?php echo csrf_input(); ?>
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control"
                                    name="codigo_acceso"
                                    maxlength="255"
                                    placeholder="Ejemplo: TEACH-ENGLISH-2026"
                                    aria-label="Codigo de acceso del profesor"
                                    required
                                >
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-unlock"></i> Activar curso
                                </button>
                            </div>
                            <div class="form-text mt-2">Este acceso es ideal para grupos privados, cohortes o clases dirigidas por profesor.</div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel">
                    <div class="panel-body">
                        <div class="section-title mb-3">
                            <h2>Como funciona</h2>
                            <span class="soft-badge"><i class="bi bi-signpost-2"></i> Doble via</span>
                        </div>
                        <div class="stack-list">
                            <div class="stack-item">
                                <strong>Catalogo abierto</strong>
                                <span>Te inscribes directamente en cursos gratis y publicos para explorar la plataforma.</span>
                            </div>
                            <div class="stack-item">
                                <strong>Curso del profesor</strong>
                                <span>Usas un codigo para entrar a una clase privada, una cohorte o un grupo guiado.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="section-title">
            <h2>Mis cursos</h2>
            <span class="soft-badge"><i class="bi bi-lightning-charge"></i> En marcha</span>
        </div>
        <div class="row g-4">
            <?php if (!empty($cursosInscritos)): ?>
                <?php foreach ($cursosInscritos as $curso): ?>
                    <?php
                    $idiomaObjetivo = Curso::obtenerIdiomaObjetivo($curso);
                    $idiomaBase = Curso::obtenerIdiomaBase($curso);
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="course-card">
                            <?php if (!empty($curso->portada_url)): ?>
                                <img src="<?php echo htmlspecialchars(url('/' . ltrim($curso->portada_url, '/'))); ?>" alt="<?php echo htmlspecialchars($curso->portada_alt ?: $curso->titulo); ?>" class="course-cover-media">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($curso->titulo); ?></h5>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars(app_language_label($idiomaObjetivo, strtoupper($idiomaObjetivo))); ?> |
                                            <?php echo htmlspecialchars(Curso::formatearRangoNivel($curso)); ?> |
                                            Desde <?php echo htmlspecialchars(app_language_label($idiomaBase, ucfirst($idiomaBase))); ?>
                                        </div>
                                    </div>
                                    <span class="soft-badge"><?php echo (int) ($curso->porcentaje ?? 0); ?>%</span>
                                </div>
                                <div class="mb-2">
                                    <span class="soft-badge <?php echo Curso::esRutaCompleta($curso) ? 'badge-accent' : ''; ?>">
                                        <i class="bi bi-signpost-split"></i> <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($curso)); ?>
                                    </span>
                                    <?php if (($curso->estado_progreso ?? '') === 'completado'): ?>
                                        <span class="soft-badge">
                                            <i class="bi bi-check-circle-fill"></i> Completado
                                        </span>
                                    <?php elseif (($curso->estado_progreso ?? '') === 'en_progreso'): ?>
                                        <span class="soft-badge">
                                            <i class="bi bi-arrow-repeat"></i> En progreso
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text mb-0"><?php echo htmlspecialchars(substr($curso->descripcion, 0, 140)); ?>...</p>
                                <div class="course-meta">
                                    <span><i class="bi bi-journal-text"></i> <?php echo (int) ($curso->total_lecciones ?? 0); ?> lecciones</span>
                                    <span><i class="bi bi-check2-circle"></i> <?php echo (int) ($curso->completados ?? 0); ?>/<?php echo (int) ($curso->total_items ?? 0); ?> items</span>
                                </div>
                                <div class="course-progress">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo (int) ($curso->porcentaje ?? 0); ?>%" aria-valuenow="<?php echo (int) ($curso->porcentaje ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?php echo url('/estudiante/cursos/' . $curso->id . '/continuar'); ?>" class="btn btn-success">Continuar</a>
                                    <a href="<?php echo url('/estudiante/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-primary">Explorar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="panel empty-state-card">
                        <div class="panel-body">
                            <span class="empty-state-icon"><i class="bi bi-compass"></i></span>
                            <div class="empty-state-copy">Aun no te has inscrito en ningun curso. Elige uno disponible para empezar.</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Cursos disponibles</h2>
            <span class="soft-badge"><i class="bi bi-compass"></i> Nuevas rutas</span>
        </div>
        <div class="panel mb-4">
            <div class="panel-body">
                <form method="GET" action="<?php echo url('/estudiante'); ?>" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label for="idioma_objetivo" class="form-label">Idioma objetivo</label>
                        <select name="idioma_objetivo" id="idioma_objetivo" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach (app_course_target_languages() as $languageValue => $languageLabel): ?>
                                <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo (($filtrosCatalogo['idioma_objetivo'] ?? '') === $languageValue) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($languageLabel); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="idioma_base" class="form-label">Explicado desde</label>
                        <select name="idioma_base" id="idioma_base" class="form-select">
                            <option value="">Cualquier idioma base</option>
                            <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                                <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo (($filtrosCatalogo['idioma_base'] ?? '') === $languageValue) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($languageLabel); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="nivel_objetivo" class="form-label">Nivel que buscas</label>
                        <select name="nivel_objetivo" id="nivel_objetivo" class="form-select">
                            <option value="">Cualquier nivel</option>
                            <?php foreach ($opcionesCefr as $nivel): ?>
                                <option value="<?php echo htmlspecialchars($nivel); ?>" <?php echo (($filtrosCatalogo['nivel_objetivo'] ?? '') === $nivel) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nivel); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="tipo_recorrido" class="form-label">Tipo de recorrido</label>
                        <select name="tipo_recorrido" id="tipo_recorrido" class="form-select">
                            <option value="">Todos</option>
                            <option value="nivel_unico" <?php echo (($filtrosCatalogo['tipo_recorrido'] ?? '') === 'nivel_unico') ? 'selected' : ''; ?>>Nivel unico</option>
                            <option value="ruta_completa" <?php echo (($filtrosCatalogo['tipo_recorrido'] ?? '') === 'ruta_completa') ? 'selected' : ''; ?>>Ruta completa</option>
                        </select>
                    </div>
                    <div class="col-lg-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
                        <a href="<?php echo url('/estudiante'); ?>" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="row g-4">
            <?php if (!empty($cursosDisponibles)): ?>
                <?php foreach ($cursosDisponibles as $curso): ?>
                    <?php
                    $idiomaObjetivo = Curso::obtenerIdiomaObjetivo($curso);
                    $idiomaBase = Curso::obtenerIdiomaBase($curso);
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="surface-card">
                            <?php if (!empty($curso->portada_url)): ?>
                                <img src="<?php echo htmlspecialchars(url('/' . ltrim($curso->portada_url, '/'))); ?>" alt="<?php echo htmlspecialchars($curso->portada_alt ?: $curso->titulo); ?>" class="course-cover-media">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($curso->titulo); ?></h5>
                                <p class="card-text text-muted mb-3"><?php echo htmlspecialchars(substr($curso->descripcion, 0, 130)); ?>...</p>
                                <div class="course-meta">
                                    <span><i class="bi bi-translate"></i> <?php echo htmlspecialchars(app_language_label($idiomaObjetivo, strtoupper($idiomaObjetivo))); ?></span>
                                    <span><i class="bi bi-chat-left-text"></i> Desde <?php echo htmlspecialchars(app_language_label($idiomaBase, ucfirst($idiomaBase))); ?></span>
                                    <span><i class="bi bi-ladder"></i> <?php echo htmlspecialchars(Curso::formatearRangoNivel($curso)); ?></span>
                                    <span><i class="bi bi-signpost-split"></i> <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($curso)); ?></span>
                                </div>
                                <form method="POST" action="<?php echo url('/estudiante/inscribir/' . $curso->id); ?>" class="d-inline">
                                    <?php echo csrf_input(); ?>
                                    <button type="submit" class="btn btn-primary">Inscribirse</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="panel empty-state-card">
                        <div class="panel-body">
                            <span class="empty-state-icon"><i class="bi bi-journal-x"></i></span>
                            <div class="empty-state-copy">No hay mas cursos publicos disponibles en este momento.</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
