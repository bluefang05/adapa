<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>">Actividades</a></li>
            <li class="breadcrumb-item active">Configurar actividad</li>
        </ol>
    </nav>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-kanban"></i> Configurador</span>
        <h1 class="page-title">Actividad de proyecto</h1>
        <p class="page-subtitle">
            Define objetivos, instrucciones y entregables para trabajos mas amplios o evaluaciones integradoras.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/proyecto/' . $leccion->id)) . '&context=actividad_proyecto'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir recurso de apoyo
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-kanban"></i> Configurador</span>
            <span class="soft-badge"><i class="bi bi-clipboard-check"></i> Entrega amplia</span>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert context-note">
            <i class="bi bi-check2-circle"></i>
            Recurso de apoyo listo: <strong><?php echo htmlspecialchars((string) ($_GET['selected_media_title'] ?? 'Recurso seleccionado')); ?></strong>.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" id="config-form">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Ficha general</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> <?php echo htmlspecialchars($leccion->titulo); ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo del proyecto</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($_SESSION['actividad_temp']['titulo'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion breve</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required><?= htmlspecialchars($_SESSION['actividad_temp']['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="instrucciones" class="form-label">Instrucciones del proyecto</label>
                                <textarea class="form-control" id="instrucciones" name="instrucciones" rows="6" required placeholder="Detalla pasos, alcance y criterios del proyecto."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="entregables" class="form-label">Entregables requeridos</label>
                                <textarea class="form-control" id="entregables" name="entregables" rows="3" placeholder="Ejemplo: reporte PDF, codigo fuente en ZIP, presentacion final."></textarea>
                                <div class="form-text">Lista lo que el estudiante debe subir o entregar.</div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Parametros</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Evaluacion</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?= htmlspecialchars($_SESSION['actividad_temp']['orden'] ?? '1') ?>" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="puntos_maximos" class="form-label">Puntos maximos</label>
                                    <input type="number" class="form-control" id="puntos_maximos" name="puntos_maximos" value="<?= htmlspecialchars($_SESSION['actividad_temp']['puntos_maximos'] ?? '10') ?>" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="tiempo_limite_minutos" class="form-label">Tiempo limite en minutos</label>
                                    <input type="number" class="form-control" id="tiempo_limite_minutos" name="tiempo_limite_minutos" value="<?= htmlspecialchars($_SESSION['actividad_temp']['tiempo_limite_minutos'] ?? '0') ?>" min="0" required>
                                    <div class="form-text">Usa 0 si el proyecto no tiene limite estricto.</div>
                                </div>
                            </div>
                        </section>

                        <input type="hidden" id="contenido" name="contenido">
                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="proyecto">

                        <div class="responsive-actions mt-4">
                            <a href="<?= url('/profesor/lecciones/' . $leccion->id . '/actividades') ?>" class="btn btn-outline-secondary">Volver</a>
                            <button type="submit" class="btn btn-primary">Guardar actividad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('config-form');
    const selectedMediaParams = new URLSearchParams(window.location.search);
    
    window.addEventListener('DOMContentLoaded', () => {
        try {
            const contenido = <?php 
                $c = $_SESSION['actividad_temp']['contenido'] ?? '{}';
                echo is_string($c) ? $c : json_encode($c); 
            ?>;
            if (contenido.instrucciones) document.getElementById('instrucciones').value = contenido.instrucciones;
            if (contenido.entregables) document.getElementById('entregables').value = contenido.entregables;
        } catch(e) { console.error(e); }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const contenido = {
            instrucciones: document.getElementById('instrucciones').value,
            entregables: document.getElementById('entregables').value,
            recurso_apoyo_media_id: selectedMediaParams.get('selected_media_id') || '',
            recurso_apoyo_titulo: selectedMediaParams.get('selected_media_title') || '',
            recurso_apoyo_url: selectedMediaParams.get('selected_media_url') || '',
            recurso_apoyo_tipo: selectedMediaParams.get('selected_media_type') || ''
        };
        
        document.getElementById('contenido').value = JSON.stringify(contenido);
        form.submit();
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
