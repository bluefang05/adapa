<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../models/Curso.php';
?>

<div class="container">
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <?php
    $cursoContinuar = $dashboardFocus['course'] ?? null;
    $dashboardFocusUrl = $dashboardFocus['url'] ?? ($cursoContinuar ? url('/estudiante/cursos/' . $cursoContinuar->id . '/continuar') : null);
    $dashboardFocusSummary = $dashboardFocus['summary'] ?? 'Entra directo a lo siguiente que debes completar.';
    $dashboardFocusHeadline = $dashboardFocus['headline'] ?? ($cursoContinuar ? $cursoContinuar->titulo : 'Sin cursos activos');
    $dashboardFocusCta = $dashboardFocus['cta_label'] ?? 'Continuar ahora';
    ?>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-stars"></i> Panel de aprendizaje</span>
        <h1 class="page-title">Continua tu aprendizaje.</h1>
        <p class="page-subtitle">
            Entra directo a lo siguiente que debes completar.
        </p>
        <div class="hero-actions">
            <?php if ($cursoContinuar): ?>
                <a href="<?php echo htmlspecialchars($dashboardFocusUrl); ?>" class="btn btn-primary">
                    <i class="bi bi-play-fill"></i> <?php echo htmlspecialchars($dashboardFocusCta); ?>
                </a>
            <?php endif; ?>
            <a href="<?php echo url('/estudiante/progreso'); ?>" class="btn btn-outline-secondary"><i class="bi bi-graph-up-arrow"></i> Progreso</a>
        </div>
        <div class="compact-meta-row">
            <?php
            $avgProgress = 0;
            if (!empty($cursosInscritos)) {
                $sum = array_reduce($cursosInscritos, function ($carry, $curso) {
                    return $carry + (int) ($curso->porcentaje ?? 0);
                }, 0);
                $avgProgress = (int) round($sum / count($cursosInscritos));
            }
            ?>
            <span class="soft-badge info"><i class="bi bi-journal-bookmark"></i> <?php echo count($cursosInscritos); ?> cursos inscritos</span>
            <span class="soft-badge"><i class="bi bi-graph-up-arrow"></i> <?php echo $avgProgress; ?>% promedio de avance</span>
            <?php if ($cursoContinuar): ?>
                <span class="soft-badge badge-accent"><i class="bi bi-play-circle"></i> Listo para continuar</span>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($cursoContinuar): ?>
        <div class="alert context-note mb-4">
            <div class="split-head">
                <div>
                    <div class="metric-label">Siguiente accion recomendada</div>
                    <div class="fw-semibold mt-1"><?php echo htmlspecialchars($dashboardFocusHeadline); ?></div>
                    <div class="small text-muted mt-1"><?php echo htmlspecialchars($dashboardFocusSummary); ?></div>
                    <div class="small text-muted mt-1"><?php echo (int) ($cursoContinuar->porcentaje ?? 0); ?>% completado en <?php echo htmlspecialchars($cursoContinuar->titulo); ?></div>
                </div>
                <div class="responsive-actions">
                    <a href="<?php echo htmlspecialchars($dashboardFocusUrl); ?>" class="btn btn-primary"><?php echo htmlspecialchars($dashboardFocusCta); ?></a>
                    <a href="<?php echo url('/estudiante/cursos/' . $cursoContinuar->id . '/lecciones'); ?>" class="btn btn-outline-primary">Ver lecciones</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section class="mb-4">
        <div class="panel">
            <div class="panel-body">
                <div class="section-title mb-3">
                    <h2>Entrar con codigo</h2>
                </div>
                <form method="POST" action="<?php echo url('/estudiante/codigo'); ?>" class="form-shell border-0 shadow-none bg-transparent p-0">
                    <?php echo csrf_input(); ?>
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            name="codigo_acceso"
                            maxlength="255"
                            placeholder="Codigo del profesor"
                            aria-label="Codigo de acceso del profesor"
                            required
                        >
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-unlock"></i> Activar curso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="section-title">
            <h2>Mis cursos</h2>
        </div>
        <div class="row g-4">
            <?php if (!empty($cursosInscritos)): ?>
                <?php foreach ($cursosInscritos as $curso): ?>
                    <?php
                    $idiomaObjetivo = Curso::obtenerIdiomaObjetivo($curso);
                    $idiomaBase = Curso::obtenerIdiomaBase($curso);
                    $courseAction = $courseActionMap[$curso->id] ?? null;
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="course-card">
                            <?php if (!empty($curso->portada_url)): ?>
                                <img src="<?php echo htmlspecialchars(url('/' . ltrim($curso->portada_url, '/'))); ?>" alt="<?php echo htmlspecialchars($curso->portada_alt ?: $curso->titulo); ?>" class="course-cover-media">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="split-head mb-2">
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
                                <?php
                                $descripcionCurso = trim((string) ($curso->descripcion ?? ''));
                                $descripcionCorta = substr($descripcionCurso, 0, 100);
                                $descripcionTruncada = strlen($descripcionCurso) > 100;
                                ?>
                                <p class="card-text mb-0">
                                    <?php echo htmlspecialchars($descripcionCorta); ?><?php echo $descripcionTruncada ? '...' : ''; ?>
                                </p>
                                <div class="course-meta">
                                    <span><i class="bi bi-journal-text"></i> <?php echo (int) ($curso->total_lecciones ?? 0); ?> lecciones</span>
                                    <span><i class="bi bi-check2-circle"></i> <?php echo (int) ($curso->completados ?? 0); ?>/<?php echo (int) ($curso->total_items ?? 0); ?> items</span>
                                </div>
                                <?php if ($courseAction): ?>
                                    <div class="small text-muted mt-3">
                                        <strong><?php echo htmlspecialchars($courseAction['label'] ?? 'Siguiente paso'); ?>:</strong>
                                        <?php echo htmlspecialchars($courseAction['summary'] ?? 'Abre el curso para seguir.'); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="course-progress">
                                    <div class="progress progress-slim">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo (int) ($curso->porcentaje ?? 0); ?>%" aria-valuenow="<?php echo (int) ($curso->porcentaje ?? 0); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="responsive-actions">
                                    <a href="<?php echo htmlspecialchars($courseAction['url'] ?? url('/estudiante/cursos/' . $curso->id . '/continuar')); ?>" class="btn btn-success">
                                        <?php echo htmlspecialchars($courseAction['cta_label'] ?? 'Continuar'); ?>
                                    </a>
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
                    <div class="col-lg-12 responsive-actions">
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
                                <?php
                                $descripcionDisponible = trim((string) ($curso->descripcion ?? ''));
                                $descripcionDisponibleCorta = substr($descripcionDisponible, 0, 130);
                                $descripcionDisponibleTruncada = strlen($descripcionDisponible) > 130;
                                ?>
                                <p class="card-text text-muted mb-3">
                                    <?php echo htmlspecialchars($descripcionDisponibleCorta); ?><?php echo $descripcionDisponibleTruncada ? '...' : ''; ?>
                                </p>
                                <div class="course-meta">
                                    <span><i class="bi bi-translate"></i> <?php echo htmlspecialchars(app_language_label($idiomaObjetivo, strtoupper($idiomaObjetivo))); ?></span>
                                    <span><i class="bi bi-chat-left-text"></i> Desde <?php echo htmlspecialchars(app_language_label($idiomaBase, ucfirst($idiomaBase))); ?></span>
                                    <span><i class="bi bi-ladder"></i> <?php echo htmlspecialchars(Curso::formatearRangoNivel($curso)); ?></span>
                                    <span><i class="bi bi-signpost-split"></i> <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($curso)); ?></span>
                                </div>
                                <form method="POST" action="<?php echo url('/estudiante/inscribir/' . $curso->id); ?>">
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
