<?php
function getEstadoColor($estado) {
    switch ($estado) {
        case 'preparacion': return 'warning';
        case 'activo': return 'success';
        case 'pausado': return 'info';
        case 'finalizado': return 'secondary';
        case 'archivado': return 'dark';
        default: return 'secondary';
    }
}
?>

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
                            <th>Estado</th>
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
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($curso->titulo); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($curso->descripcion ? substr($curso->descripcion, 0, 70) : 'Sin descripcion'); ?></div>
                                    </td>
                                    <td><?php echo ucfirst($curso->idioma); ?></td>
                                    <td><?php echo $curso->nivel_cefr; ?></td>
                                    <td><?php echo ucfirst($curso->modalidad); ?></td>
                                    <td><span class="badge bg-<?php echo getEstadoColor($curso->estado); ?>"><?php echo ucfirst($curso->estado); ?></span></td>
                                    <td><?php echo (int) ($curso->total_lecciones ?? 0); ?></td>
                                    <td><?php echo (int) ($curso->total_actividades ?? 0); ?></td>
                                    <td><?php echo (int) ($curso->total_estudiantes ?? 0); ?></td>
                                    <td><?php echo (int) $curso->max_estudiantes; ?></td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-sm btn-outline-primary" title="Ver lecciones">
                                                <i class="bi bi-book"></i>
                                            </a>
                                            <a href="<?php echo url('/profesor/cursos/edit/' . $curso->id); ?>" class="btn btn-sm btn-outline-secondary" title="Editar curso">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="<?php echo url('/profesor/cursos/delete/' . $curso->id); ?>" class="d-inline" onsubmit="return confirm('Estas seguro de eliminar este curso?');">
                                                <?php echo csrf_input(); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar curso">
                                                    <i class="bi bi-trash"></i>
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

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
