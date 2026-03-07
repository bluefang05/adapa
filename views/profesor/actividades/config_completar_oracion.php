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
        <span class="eyebrow"><i class="bi bi-pencil-square"></i> Configurador</span>
        <h1 class="page-title">Actividad de completar oracion</h1>
        <p class="page-subtitle">
            Escribe la frase completa y marca entre corchetes las palabras que el estudiante debe rellenar.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/completar_oracion/' . $leccion->id)) . '&context=actividad_completar_oracion'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir recurso de apoyo
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Encierra entre <strong>[ ]</strong> las palabras que quieres convertir en huecos. Ejemplo: La capital de [Francia] es [Paris].
    </div>

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

                            <div class="mb-4">
                                <label for="oracion_completa" class="form-label">Oracion con huecos</label>
                                <textarea class="form-control" id="oracion_completa" name="oracion_completa" rows="4" placeholder="El [perro] juega en el [parque]." required></textarea>
                                <div class="form-text">Las palabras entre corchetes se convierten en espacios en blanco.</div>
                            </div>

                            <div class="content-block" id="preview-container" style="display:none;">
                                <div class="card-body">
                                    <h3 class="h6 mb-3">Vista previa</h3>
                                    <div id="preview-content"></div>
                                </div>
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
                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="completar_oracion">

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
    const textarea = document.getElementById('oracion_completa');
    const previewContainer = document.getElementById('preview-container');
    const previewContent = document.getElementById('preview-content');
    const selectedMediaParams = new URLSearchParams(window.location.search);

    textarea.addEventListener('input', updatePreview);

    function updatePreview() {
        const text = textarea.value;
        if (!text) {
            previewContainer.style.display = 'none';
            return;
        }

        const parts = text.split(/(\[[^\]]+\])/);
        let html = '';
        let foundHuecos = false;

        parts.forEach(part => {
            if (part.startsWith('[') && part.endsWith(']')) {
                const word = part.slice(1, -1);
                if (word.trim()) {
                    html += `<span class="soft-badge me-2 mb-2">${word}</span>`;
                    foundHuecos = true;
                } else {
                    html += part;
                }
            } else {
                html += part;
            }
        });

        if (foundHuecos) {
            previewContainer.style.display = 'block';
            previewContent.innerHTML = html;
        } else {
            previewContainer.style.display = 'none';
        }
    }

    document.getElementById('config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const texto = textarea.value;
        const matches = texto.match(/\[([^\]]+)\]/g);
        
        if (!matches) {
            alert('Debes definir al menos un espacio en blanco usando corchetes [ ].');
            return;
        }

        const respuestas = matches.map(m => m.slice(1, -1).trim());
        
        const contenido = {
            texto_completo: texto,
            respuestas_correctas: respuestas,
            segmentos: texto.split(/(\[[^\]]+\])/).filter(s => s !== ''),
            recurso_apoyo_media_id: selectedMediaParams.get('selected_media_id') || '',
            recurso_apoyo_titulo: selectedMediaParams.get('selected_media_title') || '',
            recurso_apoyo_url: selectedMediaParams.get('selected_media_url') || '',
            recurso_apoyo_tipo: selectedMediaParams.get('selected_media_type') || ''
        };

        document.getElementById('contenido').value = JSON.stringify(contenido);
        this.submit();
    });

    window.addEventListener('DOMContentLoaded', function() {
        try {
            const contenidoExistente = <?php 
                $contenido = $_SESSION['actividad_temp']['contenido'] ?? '{}';
                echo is_string($contenido) ? $contenido : json_encode($contenido); 
            ?>;

            if (contenidoExistente.texto_completo) {
                textarea.value = contenidoExistente.texto_completo;
                updatePreview();
            }
        } catch (e) {
            console.error('Error cargando contenido existente', e);
        }
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
