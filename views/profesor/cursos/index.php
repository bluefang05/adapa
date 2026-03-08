<?php require_once __DIR__ . '/../../../models/Curso.php'; ?>

<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-layers"></i> Panel docente</span>
        <h1 class="page-title">Tus cursos, sin tabla ciega.</h1>
        <p class="page-subtitle">
            Mira capacidad, carga de contenido e inscritos desde una sola pantalla antes de entrar a editar.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/create'); ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Crear nuevo curso
            </a>
            <a href="<?php echo url('/profesor/estudiantes'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-people"></i> Ver estudiantes
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Cuenta</div>
                <div class="metric-value"><?php echo htmlspecialchars($planUso['plan_label'] ?? 'Plan gratuito'); ?></div>
                <div class="metric-note"><?php echo !empty($planUso['is_official']) ? 'Cuenta interna o institucional con acceso completo.' : 'Estado actual de tu cuenta docente.'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Cursos</div>
                <div class="metric-value"><?php echo count($cursos); ?></div>
                <div class="metric-note">Catalogo actualmente a tu cargo.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Inscritos</div>
                <div class="metric-value"><?php echo array_reduce($cursos, fn($carry, $curso) => $carry + (int) ($curso->total_estudiantes ?? 0), 0); ?></div>
                <div class="metric-note">Suma total entre todos tus cursos.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Actividades</div>
                <div class="metric-value"><?php echo array_reduce($cursos, fn($carry, $curso) => $carry + (int) ($curso->total_actividades ?? 0), 0); ?></div>
                <div class="metric-note">Practica creada y lista para usarse.</div>
            </div>
        </div>
    </section>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-lightbulb"></i>
            Estas en plan gratuito: 1 curso, hasta 3 lecciones por curso, 3 actividades por leccion y 3 estudiantes por codigo.
        </div>
    <?php endif; ?>

    <section>
        <div class="section-title">
            <h2>Mis cursos</h2>
            <span class="soft-badge"><i class="bi bi-grid"></i> Vista operativa</span>
        </div>

        <div class="data-table-shell">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Titulo</th>
                            <th>Idioma</th>
                            <th>Nivel</th>
                            <th>Modalidad</th>
                            <th>Workflow</th>
                            <th>Lecciones</th>
                            <th>Actividades</th>
                            <th>Inscritos</th>
                            <th>Cupo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cursos)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5">No tienes cursos creados aun.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cursos as $curso): ?>
                                <?php
                                $idiomaObjetivo = Curso::obtenerIdiomaObjetivo($curso);
                                $idiomaBase = Curso::obtenerIdiomaBase($curso);
                                $readiness = app_course_readiness_summary($curso);
                                $editorialState = app_course_editorial_snapshot($curso);
                                $catalogStatus = app_course_catalog_status($curso);
                                $publishedLessons = (int) ($curso->published_lessons ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($curso->portada_url)): ?>
                                                <img src="<?php echo htmlspecialchars(url('/' . ltrim($curso->portada_url, '/'))); ?>" alt="<?php echo htmlspecialchars($curso->portada_alt ?: $curso->titulo); ?>" class="course-thumb-sm">
                                            <?php else: ?>
                                                <span class="avatar-token"><i class="bi bi-book"></i></span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($curso->titulo); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($curso->descripcion ? substr($curso->descripcion, 0, 70) : 'Sin descripcion'); ?></div>
                                                <div class="small text-muted mt-1"><?php echo htmlspecialchars(app_course_production_hint($curso)); ?></div>
                                                <div class="small mt-1 d-flex gap-2 flex-wrap">
                                                    <span class="soft-badge"><?php echo htmlspecialchars($readiness['label']); ?> - <?php echo (int) $readiness['progress']; ?>%</span>
                                                    <span class="soft-badge <?php echo htmlspecialchars($catalogStatus['tone']); ?>">
                                                        <?php echo htmlspecialchars($catalogStatus['label']); ?>
                                                    </span>
                                                </div>
                                                <div class="small text-muted mt-1"><?php echo htmlspecialchars($editorialState['hint']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($catalogStatus['hint']); ?></div>
                                                <div class="small text-muted"><?php echo $publishedLessons; ?> leccion(es) publicadas.</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(app_language_label($idiomaObjetivo, ucfirst($idiomaObjetivo))); ?>
                                        <div class="small text-muted">Desde <?php echo htmlspecialchars(app_language_label($idiomaBase, ucfirst($idiomaBase))); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars(Curso::formatearRangoNivel($curso)); ?></div>
                                        <span class="soft-badge <?php echo Curso::esRutaCompleta($curso) ? 'badge-accent' : ''; ?>">
                                            <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($curso)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst($curso->modalidad); ?></td>
                                    <td>
                                        <div><span class="soft-badge badge-<?php echo htmlspecialchars($editorialState['tone']); ?>"><?php echo htmlspecialchars($editorialState['label']); ?></span></div>
                                        <div class="small text-muted mt-1"><?php echo htmlspecialchars($editorialState['hint']); ?></div>
                                        <div class="small text-muted mt-1">Tecnico: <?php echo htmlspecialchars($curso->estado ?? 'preparacion'); ?></div>
                                    </td>
                                    <td><?php echo (int) ($curso->total_lecciones ?? 0); ?></td>
                                    <td><?php echo (int) ($curso->total_actividades ?? 0); ?></td>
                                    <td><?php echo (int) ($curso->total_estudiantes ?? 0); ?></td>
                                    <td><?php echo (int) $curso->max_estudiantes; ?></td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-sm btn-outline-primary" title="Ver lecciones" aria-label="Ver lecciones de <?php echo htmlspecialchars($curso->titulo); ?>">
                                                <i class="bi bi-book"></i>
                                                <span class="visually-hidden">Ver lecciones</span>
                                            </a>
                                            <a href="<?php echo htmlspecialchars($editorialState['action_url']); ?>" class="btn btn-sm btn-primary" title="<?php echo htmlspecialchars($editorialState['action_label']); ?>" aria-label="<?php echo htmlspecialchars($editorialState['action_label']); ?> de <?php echo htmlspecialchars($curso->titulo); ?>">
                                                <i class="bi bi-arrow-right-circle"></i>
                                                <span class="visually-hidden"><?php echo htmlspecialchars($editorialState['action_label']); ?></span>
                                            </a>
                                            <a href="<?php echo url('/profesor/cursos/edit/' . $curso->id); ?>" class="btn btn-sm btn-outline-secondary" title="Editar curso" aria-label="Editar curso <?php echo htmlspecialchars($curso->titulo); ?>">
                                                <i class="bi bi-pencil"></i>
                                                <span class="visually-hidden">Editar curso</span>
                                            </a>
                                            <form method="POST" action="<?php echo url('/profesor/cursos/duplicate/' . $curso->id); ?>" class="d-inline">
                                                <?php echo csrf_input(); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Duplicar curso" aria-label="Duplicar curso <?php echo htmlspecialchars($curso->titulo); ?>">
                                                    <i class="bi bi-copy"></i>
                                                    <span class="visually-hidden">Duplicar curso</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?php echo url('/profesor/cursos/delete/' . $curso->id); ?>" class="d-inline" onsubmit="return confirm('Estas seguro de eliminar este curso?');">
                                                <?php echo csrf_input(); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar curso" aria-label="Eliminar curso <?php echo htmlspecialchars($curso->titulo); ?>">
                                                    <i class="bi bi-trash"></i>
                                                    <span class="visually-hidden">Eliminar curso</span>
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
    </section>
</div>

<?php
$issueReportTitle = 'Reportar un problema del panel docente';
$issueReportAction = url('/reportar-fallo');
$issueReportContextType = 'general';
$issueReportContextId = 'profesor_cursos';
$issueReportReturnTo = $_SERVER['REQUEST_URI'] ?? url('/profesor/cursos');
$issueReportDescriptionPlaceholder = 'Describe el problema del panel, modulo o flujo que estas intentando usar.';
require __DIR__ . '/../../partials/issue_report_panel.php';
?>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
