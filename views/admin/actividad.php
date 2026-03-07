<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-activity"></i> Bitacora administrativa</span>
        <h1 class="page-title">Lee quien toco que, sobre cual entidad y en que momento.</h1>
        <p class="page-subtitle">
            Esta bitacora deja rastro de acciones criticas del panel admin para que no operes a ciegas cuando varios administradores mueven piezas.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver al dashboard
            </a>
            <a href="<?php echo url('/admin/tickets'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-life-preserver"></i> Ver tickets
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Eventos</div>
                <div class="metric-value"><?php echo count($activity); ?></div>
                <div class="metric-note">Resultados visibles en esta consulta.</div>
            </div>
        </div>
    </section>

    <section class="filter-shell mb-4">
        <div class="panel-body">
            <div class="section-title">
                <h2>Filtros</h2>
            </div>
            <form method="GET" action="<?php echo url('/admin/actividad'); ?>" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label" for="action">Accion</label>
                    <select id="action" name="action" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ([
                            'ticket_status_updated',
                            'ticket_note_added',
                            'user_created',
                            'user_updated',
                            'user_deleted',
                            'user_access_toggled',
                            'user_email_verified',
                            'course_created',
                            'course_updated',
                            'course_deleted',
                            'course_duplicated',
                            'course_visibility_toggled',
                            'course_enrollment_toggled',
                            'course_state_changed',
                        ] as $actionValue): ?>
                            <option value="<?php echo htmlspecialchars($actionValue); ?>" <?php echo ($action ?? '') === $actionValue ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($actionValue); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="target">Objetivo</label>
                    <select id="target" name="target" class="form-select">
                        <option value="">Todos</option>
                        <option value="ticket" <?php echo ($target ?? '') === 'ticket' ? 'selected' : ''; ?>>Ticket</option>
                        <option value="usuario" <?php echo ($target ?? '') === 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                        <option value="curso" <?php echo ($target ?? '') === 'curso' ? 'selected' : ''; ?>>Curso</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="<?php echo url('/admin/actividad'); ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Actividad reciente</h2>
            <span class="soft-badge"><i class="bi bi-clock-history"></i> Ultimos eventos</span>
        </div>

        <?php if (empty($activity)): ?>
            <div class="panel empty-state-card">
                <div class="panel-body">
                    <span class="empty-state-icon"><i class="bi bi-activity"></i></span>
                    <div class="empty-state-copy">Todavia no hay eventos registrados para este filtro.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($activity as $entry): ?>
                    <div class="col-xl-6">
                        <article class="surface-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                                    <div>
                                        <div class="small text-muted mb-1"><?php echo htmlspecialchars($entry->action_type); ?></div>
                                        <h3 class="h5 mb-1"><?php echo htmlspecialchars($entry->description); ?></h3>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars(trim(($entry->nombre ?? '') . ' ' . ($entry->apellido ?? ''))); ?>
                                        </div>
                                    </div>
                                    <span class="soft-badge info"><?php echo htmlspecialchars($entry->target_type); ?></span>
                                </div>

                                <div class="course-meta mb-3">
                                    <span><i class="bi bi-hash"></i> <?php echo (int) ($entry->target_id ?? 0); ?></span>
                                    <span><i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?></span>
                                </div>

                                <?php if (!empty($entry->metadata_json)): ?>
                                    <div class="small text-muted">
                                        <strong>Metadata:</strong> <?php echo htmlspecialchars($entry->metadata_json); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
