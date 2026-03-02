<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../models/Curso.php';
?>

<?php
$publicCourses = 0;
$fullRoutes = 0;

foreach ($courses as $course) {
    if (!empty($course->es_publico)) {
        $publicCourses++;
    }

    if (Curso::esRutaCompleta($course)) {
        $fullRoutes++;
    }
}
?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-book-fill"></i> Oferta academica</span>
        <h1 class="page-title">Lee el catalogo completo sin entrar a cada curso por separado.</h1>
        <p class="page-subtitle">
            Revisa responsable, visibilidad y alcance formativo para entender rapidamente el estado del catalogo institucional.
        </p>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Cursos</div>
                <div class="metric-value"><?php echo count($courses); ?></div>
                <div class="metric-note">Cursos visibles dentro de la instancia.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Publicos</div>
                <div class="metric-value"><?php echo $publicCourses; ?></div>
                <div class="metric-note">Cursos abiertos a exploracion directa.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Rutas completas</div>
                <div class="metric-value"><?php echo $fullRoutes; ?></div>
                <div class="metric-note">Cursos que cubren mas de un tramo CEFR.</div>
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
                            <th>Rango</th>
                            <th>Visibilidad</th>
                            <th>Creacion</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="7" class="empty-state">Todavia no hay cursos registrados en la instancia.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
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
                                                    <?php echo htmlspecialchars(Curso::formatearRangoNivel($course)); ?> · <?php echo htmlspecialchars(Curso::obtenerEtiquetaNivel($course)); ?>
                                                </div>
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
                                        <span class="soft-badge <?php echo !empty($course->es_publico) ? 'badge-accent' : ''; ?>">
                                            <?php echo !empty($course->es_publico) ? 'Publico' : 'Privado'; ?>
                                        </span>
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
