<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../models/Curso.php';
?>

<?php
$publicCourses = 0;
$fullRoutes = 0;
$readyToReviewCourses = 0;
$publicCoursesWithGaps = 0;
$courseQuery = $_SERVER['QUERY_STRING'] ?? '';
$currentCoursesUrl = '/admin/cursos' . ($courseQuery !== '' ? '?' . $courseQuery : '');

foreach ($courses as $course) {
    $editorialState = app_course_editorial_snapshot($course);
    $publishedLessons = (int) ($course->published_lessons ?? 0);
    $catalogStatus = app_course_catalog_status($course);
    if (($catalogStatus['short_label'] ?? '') === 'Visible') {
        $publicCourses++;
    }

    if (Curso::esRutaCompleta($course)) {
        $fullRoutes++;
    }

    if (($editorialState['label'] ?? '') === 'Listo para revisar') {
        $readyToReviewCourses++;
    }

    if (($editorialState['label'] ?? '') === 'Visible con ajustes') {
        $publicCoursesWithGaps++;
    }
}
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-book-fill"></i> Oferta academica</span>
        <h1 class="page-title">Revisa el catalogo completo sin entrar a cada curso por separado.</h1>
        <p class="page-subtitle">
            Revisa responsable, visibilidad y alcance formativo para entender rapidamente el estado del catalogo institucional.
        </p>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-journal-bookmark"></i> <?php echo count($courses); ?> cursos</span>
            <span class="soft-badge"><i class="bi bi-broadcast"></i> <?php echo $publicCourses; ?> visibles en catalogo</span>
            <span class="soft-badge"><i class="bi bi-signpost-split"></i> <?php echo $fullRoutes; ?> rutas completas</span>
            <span class="soft-badge <?php echo $readyToReviewCourses > 0 ? 'badge-accent' : 'success'; ?>"><i class="bi bi-patch-check"></i> <?php echo $readyToReviewCourses; ?> listos para revisar</span>
            <span class="soft-badge <?php echo $publicCoursesWithGaps > 0 ? 'warning' : 'success'; ?>"><i class="bi bi-exclamation-circle"></i> <?php echo $publicCoursesWithGaps; ?> visibles con ajustes</span>
            <span class="soft-badge <?php echo ((int) ($catalogSummary['with_open_tickets'] ?? 0) > 0) ? 'warning' : 'success'; ?>"><i class="bi bi-life-preserver"></i> <?php echo (int) ($catalogSummary['with_open_tickets'] ?? 0); ?> con tickets abiertos</span>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <section class="filter-shell mb-4">
        <div class="panel-body">
            <div class="section-title">
                <h2>Filtros</h2>
            </div>
            <form method="GET" action="<?php echo url('/admin/cursos'); ?>" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label" for="teacher">Responsable</label>
                    <select id="teacher" name="teacher" class="form-select">
                        <option value="0">Todos</option>
                        <?php foreach (($teachers ?? []) as $teacher): ?>
                            <option value="<?php echo (int) $teacher->id; ?>" <?php echo ((int) ($teacherFilter ?? 0) === (int) $teacher->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(trim($teacher->nombre . ' ' . $teacher->apellido)); ?> &middot; <?php echo !empty($teacher->es_admin_institucion) ? 'Admin' : 'Profesor'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="publico">Visibilidad</label>
                    <select id="publico" name="publico" class="form-select">
                        <option value="">Todas</option>
                        <option value="publico" <?php echo ($visibilityFilter ?? '') === 'publico' ? 'selected' : ''; ?>>Visibles en catalogo</option>
                        <option value="privado" <?php echo ($visibilityFilter ?? '') === 'privado' ? 'selected' : ''; ?>>Ocultos o en espera</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="estado">Operativo</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (['preparacion', 'activo', 'pausado', 'finalizado', 'archivado'] as $estado): ?>
                            <option value="<?php echo htmlspecialchars($estado); ?>" <?php echo ($estadoFilter ?? '') === $estado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($estado)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="editorial">Workflow</label>
                    <select id="editorial" name="editorial" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (app_course_editorial_states() as $stateValue => $stateMeta): ?>
                            <option value="<?php echo htmlspecialchars($stateValue); ?>" <?php echo ($editorialFilter ?? '') === $stateValue ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($stateMeta['label']); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="listo_para_revisar" <?php echo ($editorialFilter ?? '') === 'listo_para_revisar' ? 'selected' : ''; ?>>
                            Listo para revisar
                        </option>
                        <option value="visible_con_ajustes" <?php echo ($editorialFilter ?? '') === 'visible_con_ajustes' ? 'selected' : ''; ?>>
                            Visible con ajustes
                        </option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 responsive-actions">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Cursos de la instancia</h2>
            <div class="section-actions">
                <span class="soft-badge"><i class="bi bi-grid"></i> Vista administrativa</span>
                <?php if (!empty($teacherFilter) && !empty($teachers)): ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <?php if ((int) $teacher->id === (int) $teacherFilter): ?>
                            <span class="soft-badge info"><i class="bi bi-person-workspace"></i> <?php echo htmlspecialchars(trim($teacher->nombre . ' ' . $teacher->apellido)); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="<?php echo url('/admin/cursos/create'); ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear curso
                </a>
            </div>
        </div>

        <details class="panel page-assist-card mb-4">
            <summary class="page-assist-summary">
                <div>
                    <div class="metric-label">Herramientas de catalogo</div>
                    <div class="fw-semibold mt-1">Acciones masivas sobre los cursos visibles</div>
                    <div class="small text-muted mt-1">Abre esta seccion cuando necesites publicar, ocultar o mover de estado varios cursos a la vez.</div>
                </div>
                <span class="soft-badge">1 bloque</span>
            </summary>
            <div class="panel-body pt-0 page-assist-body">
                <form method="POST" action="<?php echo url('/admin/cursos/bulk-action'); ?>" id="course-bulk-form" class="row g-3 align-items-end">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentCoursesUrl); ?>">
                    <div class="col-lg-5">
                        <label class="form-label" for="bulk-course-action">Accion</label>
                        <select id="bulk-course-action" name="action" class="form-select">
                            <option value="make_public">Marcar publicado y preparar catalogo</option>
                            <option value="make_private">Ocultar del catalogo</option>
                            <option value="open_enrollment">Abrir inscripcion</option>
                            <option value="close_enrollment">Cerrar inscripcion</option>
                            <option value="set_borrador">Marcar borrador</option>
                            <option value="set_en_revision">Marcar en revision</option>
                            <option value="set_publicable">Marcar publicable</option>
                            <option value="set_publicado">Marcar publicado (queda visible con lecciones publicadas)</option>
                            <option value="set_archivado">Archivar</option>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="course-select-all">
                            <label class="form-check-label" for="course-select-all">Seleccionar cursos visibles</label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-lightning"></i> Aplicar a seleccion</button>
                    </div>
                </form>
            </div>
        </details>

        <div class="data-table-shell">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Curso</th>
                            <th>Profesor</th>
                            <th>Rango</th>
                            <th>Visibilidad</th>
                            <th>Creacion</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="8" class="empty-state">Todavia no hay cursos registrados en la instancia.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <?php $editorialState = app_course_editorial_snapshot($course); ?>
                                <?php $publishedLessons = (int) ($course->published_lessons ?? 0); ?>
                                <?php $catalogStatus = app_course_catalog_status($course); ?>
                                <tr>
                                    <td>
                                        <input class="form-check-input course-select-item" type="checkbox" name="course_ids[]" value="<?php echo (int) $course->id; ?>" form="course-bulk-form" aria-label="Seleccionar curso <?php echo (int) $course->id; ?>">
                                    </td>
                                    <td>#<?php echo (int) $course->id; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($course->portada_url)): ?>
                                                <img src="<?php echo htmlspecialchars(url('/' . ltrim($course->portada_url, '/'))); ?>" class="course-thumb-sm" alt="<?php echo htmlspecialchars($course->portada_alt ?: $course->titulo); ?>">
                                            <?php else: ?>
                                                <span class="avatar-token"><i class="bi bi-book"></i></span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($course->titulo); ?></div>
                                                <div class="small text-muted">
                                                    <?php echo htmlspecialchars(Curso::formatearRangoNivel($course)); ?> &middot; <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($course)); ?>
                                                </div>
                                                <div class="small mt-1 badge-row">
                                                    <span class="soft-badge <?php echo !empty($course->inscripcion_abierta) ? 'info' : ''; ?>">
                                                        <?php echo !empty($course->inscripcion_abierta) ? 'Inscripcion abierta' : 'Inscripcion cerrada'; ?>
                                                    </span>
                                                    <span class="soft-badge badge-<?php echo htmlspecialchars($editorialState['tone'] ?? 'info'); ?>">
                                                        <?php echo htmlspecialchars($editorialState['label'] ?? 'En progreso'); ?>
                                                    </span>
                                                    <span class="soft-badge <?php echo htmlspecialchars($course->admin_focus_tone ?? 'info'); ?>">
                                                        <?php echo htmlspecialchars($course->admin_focus_label ?? 'Controlado'); ?>
                                                    </span>
                                                    <?php if ((int) ($course->open_tickets ?? 0) > 0): ?>
                                                        <span class="soft-badge warning"><?php echo (int) ($course->open_tickets ?? 0); ?> tickets abiertos</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-muted mt-1"><?php echo htmlspecialchars($course->admin_focus_hint ?? ($editorialState['hint'] ?? '')); ?></div>
                                                <div class="small text-muted"><?php echo $publishedLessons; ?> leccion(es) publicadas.</div>
                                                <div class="small text-muted">Workflow base: <?php echo htmlspecialchars(app_course_editorial_state_meta($course)['label'] ?? 'Borrador'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($course->profesor_nombre): ?>
                                            <?php echo htmlspecialchars(trim($course->profesor_nombre . ' ' . $course->profesor_apellido)); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(Curso::formatearRangoNivel($course)); ?></td>
                                    <td>
                                        <span class="soft-badge <?php echo htmlspecialchars($catalogStatus['tone']); ?>">
                                            <?php echo htmlspecialchars($catalogStatus['label']); ?>
                                        </span>
                                        <div class="small text-muted mt-1"><?php echo htmlspecialchars($catalogStatus['hint']); ?></div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($course->fecha_creacion)); ?></td>
                                    <td>
                                        <div class="responsive-actions table-actions">
                                            <a href="<?php echo url('/admin/cursos/estructura/' . $course->id); ?>" class="btn btn-sm btn-outline-primary" title="Ver estructura" aria-label="Ver estructura de <?php echo htmlspecialchars($course->titulo); ?>">
                                                <i class="bi bi-diagram-3"></i>
                                            </a>
                                            <a href="<?php echo url('/admin/tickets?course_id=' . $course->id); ?>" class="btn btn-sm btn-outline-primary" title="Ver tickets del curso" aria-label="Ver tickets del curso <?php echo htmlspecialchars($course->titulo); ?>">
                                                <i class="bi bi-life-preserver"></i>
                                            </a>
                                            <a href="<?php echo url('/admin/cursos/edit/' . $course->id); ?>" class="btn btn-sm btn-outline-secondary" title="Editar curso" aria-label="Editar curso <?php echo htmlspecialchars($course->titulo); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="<?php echo url('/admin/cursos/duplicate/' . $course->id); ?>">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentCoursesUrl); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Duplicar curso" aria-label="Duplicar curso <?php echo htmlspecialchars($course->titulo); ?>">
                                                    <i class="bi bi-copy"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?php echo url('/admin/cursos/toggle-publico/' . $course->id); ?>">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentCoursesUrl); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="<?php echo ($catalogStatus['short_label'] ?? '') === 'Visible' ? 'Ocultar del catalogo' : 'Marcar para catalogo'; ?>" aria-label="Cambiar visibilidad de <?php echo htmlspecialchars($course->titulo); ?>">
                                                    <i class="bi <?php echo ($catalogStatus['short_label'] ?? '') === 'Visible' ? 'bi-eye-slash' : 'bi-broadcast'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?php echo url('/admin/cursos/toggle-inscripcion/' . $course->id); ?>">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentCoursesUrl); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="<?php echo !empty($course->inscripcion_abierta) ? 'Cerrar inscripcion' : 'Abrir inscripcion'; ?>" aria-label="Cambiar inscripcion de <?php echo htmlspecialchars($course->titulo); ?>">
                                                    <i class="bi <?php echo !empty($course->inscripcion_abierta) ? 'bi-door-closed' : 'bi-door-open'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?php echo url('/admin/cursos/cycle-estado/' . $course->id); ?>">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentCoursesUrl); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Avanzar workflow editorial" aria-label="Avanzar workflow editorial de <?php echo htmlspecialchars($course->titulo); ?>">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?php echo url('/admin/cursos/delete/' . $course->id); ?>" onsubmit="return confirm('Estas seguro de eliminar este curso? Esta accion no se puede deshacer.');">
                                                <?php echo csrf_input(); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar curso" aria-label="Eliminar curso <?php echo htmlspecialchars($course->titulo); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <?php if ((int) ($course->ticket_total ?? 0) > 0): ?>
                                            <div class="small text-muted mt-2">
                                                <?php echo (int) ($course->new_tickets ?? 0); ?> nuevos, <?php echo (int) ($course->lesson_tickets ?? 0); ?> de leccion, <?php echo (int) ($course->activity_tickets ?? 0); ?> de actividad.
                                            </div>
                                        <?php endif; ?>
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('course-select-all');
    if (!selectAll) {
        return;
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.course-select-item').forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });
});
</script>
