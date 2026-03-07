<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-speedometer2"></i> Control institucional</span>
        <h1 class="page-title">Administra usuarios, cursos y crecimiento desde una sola vista.</h1>
        <p class="page-subtitle">
            Este panel resume la salud operativa de tu instancia para que puedas detectar actividad reciente sin entrar a cada modulo.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-primary">
                <i class="bi bi-people"></i> Gestionar usuarios
            </a>
            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-journal-bookmark"></i> Revisar cursos
            </a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Usuarios</div>
                <div class="metric-value"><?php echo (int) $totalUsers; ?></div>
                <div class="metric-note">Personas registradas en la instancia.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Profesores</div>
                <div class="metric-value"><?php echo (int) $totalProfessors; ?></div>
                <div class="metric-note">Docentes con acceso activo al panel.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Estudiantes</div>
                <div class="metric-value"><?php echo (int) $totalStudents; ?></div>
                <div class="metric-note">Base activa de aprendizaje.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Cursos</div>
                <div class="metric-value"><?php echo (int) $totalCourses; ?></div>
                <div class="metric-note">Oferta creada dentro de la plataforma.</div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Usuarios recientes</h2>
                        <a href="<?php echo url('/admin/usuarios'); ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
                    </div>

                    <?php if (empty($recentUsers)): ?>
                        <div class="empty-state">Todavia no hay usuarios recientes para mostrar.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($recentUsers as $user): ?>
                                <article class="stack-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="avatar-token"><?php echo strtoupper(substr($user->nombre, 0, 1)); ?></span>
                                        <div>
                                            <p class="stack-item-title"><?php echo htmlspecialchars(trim($user->nombre . ' ' . $user->apellido)); ?></p>
                                            <div class="stack-item-subtitle"><?php echo htmlspecialchars($user->email); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="soft-badge">
                                            <?php
                                            if ($user->es_admin_institucion) {
                                                echo 'Admin';
                                            } elseif ($user->es_profesor) {
                                                echo 'Profesor';
                                            } else {
                                                echo 'Estudiante';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="panel-body">
                    <div class="section-title">
                        <h2>Cursos recientes</h2>
                        <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
                    </div>

                    <?php if (empty($recentCourses)): ?>
                        <div class="empty-state">Todavia no hay cursos recientes para mostrar.</div>
                    <?php else: ?>
                        <div class="stack-list">
                            <?php foreach ($recentCourses as $course): ?>
                                <article class="stack-item">
                                    <div>
                                        <p class="stack-item-title"><?php echo htmlspecialchars($course->titulo); ?></p>
                                        <div class="stack-item-subtitle">
                                            Creado el <?php echo date('d/m/Y', strtotime($course->fecha_creacion)); ?>
                                        </div>
                                    </div>
                                    <a href="<?php echo url('/estudiante/cursos/' . $course->id . '/lecciones'); ?>" class="btn btn-outline-secondary btn-sm" aria-label="Ver curso <?php echo htmlspecialchars($course->titulo); ?>">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
