<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php
function adminTicketRoleLabel($ticket) {
    if (!empty($ticket->es_admin_institucion)) {
        return 'Admin';
    }
    if (!empty($ticket->es_profesor)) {
        return 'Profesor';
    }
    if (!empty($ticket->es_estudiante)) {
        return 'Estudiante';
    }
    return 'Usuario';
}

function adminTicketStatusTone($status) {
    switch ($status) {
        case 'resuelto':
            return 'success';
        case 'en_revision':
            return 'info';
        default:
            return 'warning';
    }
}

$ticketQuery = $_SERVER['QUERY_STRING'] ?? '';
$currentTicketsUrl = '/admin/tickets' . ($ticketQuery !== '' ? '?' . $ticketQuery : '');
$ticketFocusBlocks = (!empty($ticketFocusSummary['by_course']) ? 1 : 0) + (!empty($ticketFocusSummary['by_issue']) ? 1 : 0);
?>

<div class="container">
    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-life-preserver"></i> Soporte institucional</span>
        <h1 class="page-title">Tickets de estudiantes y profesores</h1>
        <p class="page-subtitle">Centraliza fallos reportados desde experiencia alumno o panel docente, priorizalos y resuelvelos sin perder contexto.</p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al dashboard
            </a>
            <a href="<?php echo url('/admin/profesores'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-person-workspace"></i> Supervisar profesores
            </a>
            <a href="<?php echo url('/admin/actividad'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-activity"></i> Ver bitacora
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge warning"><i class="bi bi-bell"></i> <?php echo (int) ($ticketSummary->nuevos ?? 0); ?> nuevos</span>
            <span class="soft-badge info"><i class="bi bi-hourglass-split"></i> <?php echo (int) ($ticketSummary->en_revision ?? 0); ?> en revision</span>
            <span class="soft-badge"><i class="bi bi-camera-video"></i> <?php echo (int) ($ticketSummary->audio_video ?? 0); ?> audio/video</span>
            <span class="soft-badge"><i class="bi bi-file-earmark-text"></i> <?php echo (int) ($ticketSummary->contenido ?? 0); ?> contenido</span>
            <span class="soft-badge <?php echo ((int) ($ticketPrioritySummary->alta ?? 0) > 0) ? 'warning' : 'success'; ?>"><i class="bi bi-exclamation-triangle"></i> <?php echo (int) ($ticketPrioritySummary->alta ?? 0); ?> alta prioridad</span>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <section class="filter-shell mb-4">
        <div class="panel-body">
            <div class="section-title">
                <h2>Filtros</h2>
            </div>
            <form method="GET" action="<?php echo url('/admin/tickets'); ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="status">Estado</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="nuevo" <?php echo $status === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="en_revision" <?php echo $status === 'en_revision' ? 'selected' : ''; ?>>En revision</option>
                        <option value="resuelto" <?php echo $status === 'resuelto' ? 'selected' : ''; ?>>Resuelto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="context">Contexto</label>
                    <select id="context" name="context" class="form-select">
                        <option value="">Todos</option>
                        <option value="general" <?php echo $context === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="leccion" <?php echo $context === 'leccion' ? 'selected' : ''; ?>>Leccion</option>
                        <option value="actividad" <?php echo $context === 'actividad' ? 'selected' : ''; ?>>Actividad</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="role">Origen</label>
                    <select id="role" name="role" class="form-select">
                        <option value="">Todos</option>
                        <option value="profesor" <?php echo $role === 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                        <option value="estudiante" <?php echo $role === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="priority">Prioridad</label>
                    <select id="priority" name="priority" class="form-select">
                        <option value="">Todas</option>
                        <option value="alta" <?php echo ($priority ?? '') === 'alta' ? 'selected' : ''; ?>>Alta</option>
                        <option value="media" <?php echo ($priority ?? '') === 'media' ? 'selected' : ''; ?>>Media</option>
                        <option value="baja" <?php echo ($priority ?? '') === 'baja' ? 'selected' : ''; ?>>Baja</option>
                        <option value="cerrado" <?php echo ($priority ?? '') === 'cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                    </select>
                </div>
                <?php if (!empty($userId)): ?>
                    <input type="hidden" name="user_id" value="<?php echo (int) $userId; ?>">
                <?php endif; ?>
                <?php if (!empty($ownerId)): ?>
                    <input type="hidden" name="owner_id" value="<?php echo (int) $ownerId; ?>">
                <?php endif; ?>
                <?php if (!empty($courseId)): ?>
                    <input type="hidden" name="course_id" value="<?php echo (int) $courseId; ?>">
                <?php endif; ?>
                <div class="col-md-2 responsive-actions">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </section>

    <?php if (!empty($selectedReporter) || !empty($selectedOwner) || !empty($selectedCourse)): ?>
        <div class="alert context-note mb-4">
            <div class="split-head">
                <div class="badge-stack">
                    <?php if (!empty($selectedReporter)): ?>
                        <span class="soft-badge info">
                            <i class="bi bi-send"></i>
                            Reportados por <?php echo htmlspecialchars(trim($selectedReporter->nombre . ' ' . $selectedReporter->apellido)); ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($selectedOwner)): ?>
                        <span class="soft-badge warning">
                            <i class="bi bi-collection"></i>
                            Ligados a cursos de <?php echo htmlspecialchars(trim($selectedOwner->nombre . ' ' . $selectedOwner->apellido)); ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($selectedCourse)): ?>
                        <span class="soft-badge badge-accent">
                            <i class="bi bi-journal-bookmark"></i>
                            Solo tickets del curso <?php echo htmlspecialchars($selectedCourse->titulo); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="responsive-actions">
                    <?php if (!empty($selectedCourse)): ?>
                        <a href="<?php echo url('/admin/cursos/estructura/' . (int) $selectedCourse->id); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-diagram-3"></i> Ver estructura
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Quitar enfoque
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($ticketFocusSummary['by_course']) || !empty($ticketFocusSummary['by_issue'])): ?>
        <details class="panel page-assist-card mb-4">
            <summary class="page-assist-summary">
                <div>
                    <div class="metric-label">Lectura del filtro</div>
                    <div class="fw-semibold mt-1">Concentracion por curso y por tipo de incidencia</div>
                    <div class="small text-muted mt-1">Abre esta seccion si quieres entender donde se acumula el ruido antes de entrar ticket por ticket.</div>
                </div>
                <span class="soft-badge"><?php echo $ticketFocusBlocks; ?> bloques</span>
            </summary>
            <div class="panel-body pt-0 page-assist-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <section>
                            <div class="section-title">
                                <h2>Cursos con mas ruido</h2>
                                <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-primary btn-sm">Ver catalogo</a>
                            </div>
                            <?php if (empty($ticketFocusSummary['by_course'])): ?>
                                <div class="empty-state">El filtro actual no concentra tickets en un curso concreto.</div>
                            <?php else: ?>
                                <div class="stack-list">
                                    <?php foreach ($ticketFocusSummary['by_course'] as $courseLabel => $ticketCount): ?>
                                        <article class="stack-item">
                                            <div class="split-head">
                                                <div>
                                                    <div class="stack-item-title"><?php echo htmlspecialchars($courseLabel); ?></div>
                                                    <div class="stack-item-subtitle">Curso que concentra incidencias dentro del filtro actual.</div>
                                                </div>
                                                <span class="soft-badge warning"><?php echo (int) $ticketCount; ?> tickets</span>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                    <div class="col-lg-6">
                        <section>
                            <div class="section-title">
                                <h2>Tipos que mas se repiten</h2>
                                <span class="soft-badge"><i class="bi bi-bar-chart"></i> Lectura del filtro</span>
                            </div>
                            <?php if (empty($ticketFocusSummary['by_issue'])): ?>
                                <div class="empty-state">No hay tipologias suficientes para destacar.</div>
                            <?php else: ?>
                                <div class="stack-list">
                                    <?php foreach ($ticketFocusSummary['by_issue'] as $issueLabel => $ticketCount): ?>
                                        <article class="stack-item">
                                            <div class="split-head">
                                                <div>
                                                    <div class="stack-item-title"><?php echo htmlspecialchars($issueLabel); ?></div>
                                                    <div class="stack-item-subtitle">Casos del mismo tipo dentro del filtro actual.</div>
                                                </div>
                                                <span class="soft-badge info"><?php echo (int) $ticketCount; ?> casos</span>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                                <div class="course-meta mt-3">
                                    <span><i class="bi bi-person-workspace"></i> <?php echo (int) ($ticketFocusSummary['by_role']['profesor'] ?? 0); ?> de profesor</span>
                                    <span><i class="bi bi-mortarboard"></i> <?php echo (int) ($ticketFocusSummary['by_role']['estudiante'] ?? 0); ?> de estudiante</span>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>
            </div>
        </details>
    <?php endif; ?>

    <section>
        <div class="section-title">
            <h2>Bandeja de tickets</h2>
            <span class="soft-badge"><i class="bi bi-inboxes"></i> <?php echo count($tickets); ?> resultados</span>
        </div>

        <section class="filter-shell ticket-bulk-panel mb-4">
            <div class="panel-body">
                <div class="section-title">
                    <h2>Accion masiva</h2>
                </div>
                <form method="POST" action="<?php echo url('/admin/tickets/bulk-status'); ?>" id="ticket-bulk-form" class="row g-3 align-items-end">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentTicketsUrl); ?>">
                    <div class="col-md-4">
                        <label class="form-label" for="bulk-status">Nuevo estado</label>
                        <select id="bulk-status" name="status" class="form-select">
                            <option value="nuevo">Nuevo</option>
                            <option value="en_revision">En revision</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="ticket-select-all">
                            <label class="form-check-label" for="ticket-select-all">Seleccionar todos los tickets visibles</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-lightning"></i> Aplicar a seleccion</button>
                    </div>
                </form>
            </div>
        </section>

        <?php if (empty($tickets)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-inboxes"></i></span>
                    <div class="empty-state-copy">No hay tickets para el filtro actual.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($tickets as $ticket): ?>
                    <?php $ticketNotes = $notesByTicket[(int) $ticket->id] ?? []; ?>
                    <div class="col-xl-6">
                        <article class="surface-card ticket-card h-100">
                            <div class="card-body">
                                <div class="ticket-card-head">
                                    <div>
                                        <div class="small text-muted mb-1">Ticket #<?php echo (int) $ticket->id; ?></div>
                                        <h3 class="h5 mb-1"><?php echo htmlspecialchars($ticket->issue_type); ?></h3>
                                        <div class="small text-muted ticket-card-origin">
                                            <?php echo htmlspecialchars(trim(($ticket->nombre ?? '') . ' ' . ($ticket->apellido ?? ''))); ?> <span class="ticket-card-dot">&middot;</span> <?php echo htmlspecialchars(adminTicketRoleLabel($ticket)); ?>
                                        </div>
                                    </div>
                                    <div class="ticket-card-badges">
                                        <input class="form-check-input ticket-select-item" type="checkbox" name="ticket_ids[]" value="<?php echo (int) $ticket->id; ?>" form="ticket-bulk-form" aria-label="Seleccionar ticket <?php echo (int) $ticket->id; ?>">
                                        <span class="soft-badge <?php echo $ticket->priority_tone ?? 'info'; ?>">
                                            <?php echo htmlspecialchars($ticket->priority_label ?? 'Media'); ?>
                                        </span>
                                        <span class="soft-badge <?php echo adminTicketStatusTone($ticket->status); ?>">
                                            <?php echo htmlspecialchars($ticket->status); ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="ticket-card-summary mb-3"><?php echo nl2br(htmlspecialchars($ticket->description)); ?></p>

                                <div class="course-meta ticket-card-meta mb-3">
                                    <span><i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($ticket->created_at)); ?></span>
                                    <span><i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($ticket->context_type); ?></span>
                                    <?php if (!empty($ticket->curso_titulo)): ?>
                                        <span><i class="bi bi-book"></i> <?php echo htmlspecialchars($ticket->curso_titulo); ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($ticket->leccion_titulo) || !empty($ticket->actividad_titulo)): ?>
                                    <div class="small text-muted ticket-card-context mb-3">
                                        <?php if (!empty($ticket->leccion_titulo)): ?>
                                            Leccion: <?php echo htmlspecialchars($ticket->leccion_titulo); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($ticket->actividad_titulo)): ?>
                                            Actividad: <?php echo htmlspecialchars($ticket->actividad_titulo); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="alert context-note mb-3">
                                    <div class="fw-semibold">Siguiente accion recomendada</div>
                                    <div class="small text-muted mt-1"><?php echo htmlspecialchars($ticket->recommended_action ?? ''); ?></div>
                                </div>

                                <details class="panel page-assist-card mb-3">
                                    <summary class="page-assist-summary">
                                        <div>
                                            <div class="metric-label">Notas internas</div>
                                            <div class="fw-semibold mt-1">Seguimiento interno del ticket</div>
                                            <div class="small text-muted mt-1">Abre esta seccion solo si necesitas revisar el historial o dejar una decision interna.</div>
                                        </div>
                                        <span class="soft-badge"><?php echo count($ticketNotes); ?></span>
                                    </summary>
                                    <div class="panel-body pt-0 page-assist-body">
                                        <?php if (empty($ticketNotes)): ?>
                                            <div class="small text-muted">Todavia no hay notas internas para este ticket.</div>
                                        <?php else: ?>
                                            <div class="stack-list">
                                                <?php foreach (array_slice($ticketNotes, 0, 3) as $note): ?>
                                                    <article class="stack-item">
                                                        <div class="stack-item-title"><?php echo htmlspecialchars(trim(($note->nombre ?? '') . ' ' . ($note->apellido ?? ''))); ?></div>
                                                        <div class="stack-item-subtitle"><?php echo date('d/m/Y H:i', strtotime($note->created_at)); ?></div>
                                                        <div class="small mt-2"><?php echo nl2br(htmlspecialchars($note->note)); ?></div>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo url('/admin/tickets/note/' . $ticket->id); ?>" class="mt-3">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentTicketsUrl); ?>">
                                            <label class="form-label" for="note-<?php echo (int) $ticket->id; ?>">Agregar nota</label>
                                            <textarea id="note-<?php echo (int) $ticket->id; ?>" name="note" class="form-control" rows="3" placeholder="Contexto interno, seguimiento o decision tomada..."></textarea>
                                            <div class="mt-2">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-journal-plus"></i> Guardar nota
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </details>

                                <div class="responsive-actions ticket-card-actions">
                                    <form method="POST" action="<?php echo url('/admin/tickets/status/' . $ticket->id); ?>" class="responsive-actions ticket-card-status-form">
                                        <?php echo csrf_input(); ?>
                                        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($currentTicketsUrl); ?>">
                                        <select name="status" class="form-select form-select-sm ticket-status-select">
                                            <option value="nuevo" <?php echo $ticket->status === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                                            <option value="en_revision" <?php echo $ticket->status === 'en_revision' ? 'selected' : ''; ?>>En revision</option>
                                            <option value="resuelto" <?php echo $ticket->status === 'resuelto' ? 'selected' : ''; ?>>Resuelto</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Actualizar estado</button>
                                    </form>
                                    <?php if (!empty($ticket->es_profesor)): ?>
                                        <a href="<?php echo url('/admin/profesores?search=' . urlencode(trim(($ticket->nombre ?? '') . ' ' . ($ticket->apellido ?? '')))); ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-person-workspace"></i> Ver profesor
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket->curso_id)): ?>
                                        <a href="<?php echo url('/admin/cursos/estructura/' . (int) $ticket->curso_id); ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-diagram-3"></i> Estructura del curso
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($ticket->reference_url)): ?>
                                        <a href="<?php echo htmlspecialchars(url($ticket->reference_url)); ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-box-arrow-up-right"></i> Abrir contexto
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('ticket-select-all');
    if (!selectAll) {
        return;
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.ticket-select-item').forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });
});
</script>
