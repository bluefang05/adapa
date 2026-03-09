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
        <span class="eyebrow"><i class="bi bi-arrows-angle-contract"></i> Configurador</span>
        <h1 class="page-title">Actividad de emparejamiento</h1>
        <p class="page-subtitle">
            Crea pares relacionados que luego el estudiante deba unir correctamente.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/emparejamiento/' . $leccion->id)) . '&context=actividad_emparejamiento'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir recurso de apoyo
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-arrows-angle-contract"></i> Configurador</span>
            <span class="soft-badge"><i class="bi bi-diagram-3"></i> Pares relacionados</span>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="alert context-note">
        <i class="bi bi-info-circle"></i> Ejemplos utiles: pais-capital, palabra-traduccion, termino-definicion.
    </div>

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
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($_SESSION['actividad_temp']['titulo'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required><?= htmlspecialchars($_SESSION['actividad_temp']['descripcion'] ?? '') ?></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Pares</h2>
                                <span class="soft-badge"><i class="bi bi-diagram-3"></i> Builder</span>
                            </div>

                            <div id="pares-container" class="config-builder mb-4"></div>

                            <div class="builder-toolbar">
                                <button type="button" class="btn btn-outline-primary" onclick="agregarPar()">
                                    <i class="bi bi-plus-lg"></i> Anadir par
                                </button>
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
                        <input type="hidden" id="tipo_actividad" name="tipo_actividad" value="emparejamiento">

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

<template id="par-template">
    <div class="builder-item par-item">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <label class="form-label">Elemento A</label>
                <input type="text" class="form-control item-a" placeholder="Ejemplo: Espana">
            </div>
            <div class="col-md-2 text-center">
                <div class="soft-badge justify-content-center"><i class="bi bi-arrow-left-right"></i></div>
            </div>
            <div class="col-md-5">
                <label class="form-label">Elemento B</label>
                <input type="text" class="form-control item-b" placeholder="Ejemplo: Madrid">
            </div>
        </div>
        <div class="builder-toolbar mt-3">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarPar(this)">Eliminar par</button>
        </div>
    </div>
</template>

<script>
    const form = document.getElementById('config-form');
    const paresContainer = document.getElementById('pares-container');
    const parTemplate = document.getElementById('par-template');
    const selectedMediaParams = new URLSearchParams(window.location.search);

    function agregarPar(itemA = '', itemB = '') {
        const clone = parTemplate.content.cloneNode(true);
        const inputA = clone.querySelector('.item-a');
        const inputB = clone.querySelector('.item-b');
        
        inputA.value = itemA;
        inputB.value = itemB;
        
        paresContainer.appendChild(clone);
    }

    function eliminarPar(btn) {
        if (paresContainer.children.length > 1) {
            btn.closest('.par-item').remove();
        } else {
            alert('Debe haber al menos un par.');
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        try {
            const contenido = <?php 
                $c = $_SESSION['actividad_temp']['contenido'] ?? '{}';
                echo is_string($c) ? $c : json_encode($c); 
            ?>;
            
            if (contenido.pares && Array.isArray(contenido.pares) && contenido.pares.length > 0) {
                contenido.pares.forEach(par => agregarPar(par.item, par.match));
            } else {
                agregarPar();
                agregarPar();
            }
        } catch(e) { 
            console.error(e);
            agregarPar();
            agregarPar();
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const pares = [];
        const items = document.querySelectorAll('.par-item');
        let valid = true;

        items.forEach(item => {
            const valA = item.querySelector('.item-a').value.trim();
            const valB = item.querySelector('.item-b').value.trim();
            
            if (!valA || !valB) {
                valid = false;
            }
            
            pares.push({
                item: valA,
                match: valB
            });
        });

        if (!valid) {
            alert('Por favor completa todos los campos de los pares.');
            return;
        }

        if (pares.length < 2) {
            alert('Se necesitan al menos 2 pares para esta actividad.');
            return;
        }

        const contenido = {
            pares: pares,
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
