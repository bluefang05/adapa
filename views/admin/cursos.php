<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php
$paidCourses = 0;
$freeCourses = 0;

foreach ($courses as $course) {
    if ((float) $course->precio > 0) {
        $paidCourses++;
    } else {
        $freeCourses++;
    }
}
?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-book-fill"></i> Oferta academica</span>
        <h1 class="page-title">Lee el catalogo completo sin entrar a cada curso por separado.</h1>
        <p class="page-subtitle">
            Revisa responsable, costo y fecha de creacion para entender rapidamente el estado del catalogo institucional.
        </p>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Cursos</div>
                <div class="metric-value"><?php echo count($courses); ?></div>
                <div class="metric-note">Cursos visibles dentro de la instancia.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Gratis</div>
                <div class="metric-value"><?php echo $freeCourses; ?></div>
                <div class="metric-note">Cursos sin costo publicado.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">De pago</div>
                <div class="metric-value"><?php echo $paidCourses; ?></div>
                <div class="metric-note">Catalogo con precio configurado.</div>
            </div>
        </div>
    </section>

    <section>
        <div class="section-title">
            <h2>Cursos de la instancia</h2>
            <span class="soft-badge"><i class="bi bi-grid"></i> Vista administrativa</span>
        </div>

        <div class="data-table-shell">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Curso</th>
                            <th>Profesor</th>
                            <th>Precio</th>
                            <th>Creacion</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">Todavia no hay cursos registrados en la instancia.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>#<?php echo (int) $course->id; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($course->imagen_url)): ?>
                                                <img src="<?php echo htmlspecialchars($course->imagen_url); ?>" class="rounded" style="width: 44px; height: 44px; object-fit: cover;" alt="Imagen del curso">
                                            <?php else: ?>
                                                <span class="avatar-token"><i class="bi bi-book"></i></span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($course->titulo); ?></div>
                                                <div class="small text-muted">Curso visible para seguimiento administrativo.</div>
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
                                    <td>
                                        <?php if ((float) $course->precio > 0): ?>
                                            $<?php echo number_format((float) $course->precio, 2); ?>
                                        <?php else: ?>
                                            <span class="soft-badge">Gratis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($course->fecha_creacion)); ?></td>
                                    <td>
                                        <a href="<?php echo url('/estudiante/curso/' . $course->id); ?>" class="btn btn-sm btn-outline-primary" title="Ver curso">
                                            <i class="bi bi-eye"></i>
                                        </a>
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
