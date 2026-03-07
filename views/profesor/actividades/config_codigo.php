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

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-code-slash"></i> Configurador</span>
        <h1 class="page-title">Actividad de codigo</h1>
        <p class="page-subtitle">
            Define lenguaje, enunciado, boilerplate y solucion de referencia para la revision docente.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/codigo/' . $leccion->id)) . '&context=actividad_codigo'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir recurso de apoyo
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert alert-success">
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
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($_SESSION['actividad_temp']['titulo'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required><?= htmlspecialchars($_SESSION['actividad_temp']['descripcion'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="lenguaje" class="form-label">Lenguaje de programacion</label>
                                <select class="form-select" id="lenguaje" name="lenguaje" required>
                                    <option value="javascript">JavaScript</option>
                                    <option value="php">PHP</option>
                                    <option value="python">Python</option>
                                    <option value="html">HTML</option>
                                    <option value="css">CSS</option>
                                    <option value="sql">SQL</option>
                                    <option value="java">Java</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="instrucciones" class="form-label">Instrucciones detalladas</label>
                                <textarea class="form-control" id="instrucciones" name="instrucciones" rows="4" required placeholder="Describe que debe programar el estudiante."></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Soporte de desarrollo</h2>
                                <span class="soft-badge"><i class="bi bi-braces"></i> Referencia</span>
                            </div>

                            <div class="mb-3">
                                <label for="codigo_inicial" class="form-label">Codigo inicial</label>
                                <textarea class="form-control font-monospace" id="codigo_inicial" name="codigo_inicial" rows="6" placeholder="// Codigo base para el estudiante"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="solucion_esperada" class="form-label">Solucion esperada</label>
                                <textarea class="form-control font-monospace" id="solucion_esperada" name="solucion_esperada" rows="6" placeholder="// Solucion de referencia para el profesor"></textarea>
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
                                    <input type="number" class="form-control" id="tiempo_limite_minutos" name="tiempo_limite_minutos" value="<?= htmlspecialchars($_SESSION['actividad_temp']['tiempo_limite_minutos'] ?? '5') ?>" min="1" required>
                                </div>
                            </div>
                        </section>

                        <input type="hidden" id="contenido" name="contenido">
                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="codigo">

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
            if (contenido.lenguaje) document.getElementById('lenguaje').value = contenido.lenguaje;
            if (contenido.instrucciones) document.getElementById('instrucciones').value = contenido.instrucciones;
            if (contenido.codigo_inicial) document.getElementById('codigo_inicial').value = contenido.codigo_inicial;
            if (contenido.solucion_esperada) document.getElementById('solucion_esperada').value = contenido.solucion_esperada;
        } catch(e) { console.error(e); }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const contenido = {
            lenguaje: document.getElementById('lenguaje').value,
            instrucciones: document.getElementById('instrucciones').value,
            codigo_inicial: document.getElementById('codigo_inicial').value,
            solucion_esperada: document.getElementById('solucion_esperada').value,
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
