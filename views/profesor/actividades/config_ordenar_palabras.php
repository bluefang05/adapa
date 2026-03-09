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
        <span class="eyebrow"><i class="bi bi-sort-alpha-down"></i> Configurador</span>
        <h1 class="page-title">Actividad de ordenar palabras</h1>
        <p class="page-subtitle">
            Define el orden correcto de los elementos para que luego el estudiante los reorganice desde una secuencia mezclada.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/actividades/config/ordenar_palabras/' . $leccion->id)) . '&context=actividad_ordenar_palabras'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Elegir recurso de apoyo
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-sort-alpha-down"></i> Configurador</span>
            <span class="soft-badge"><i class="bi bi-list-ol"></i> Secuencia ordenable</span>
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
                    <form method="POST" onsubmit="return guardarConfiguracion()">
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
                                <label for="descripcion" class="form-label">Instruccion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required><?= htmlspecialchars($_SESSION['actividad_temp']['descripcion'] ?? '') ?></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Elementos en orden correcto</h2>
                                <span class="soft-badge"><i class="bi bi-list-ol"></i> Secuencia</span>
                            </div>

                            <div class="form-text mb-3">Los estudiantes veran estos elementos desordenados y deberan reconstruir la secuencia correcta.</div>
                            <div id="items-container" class="config-builder mb-3"></div>

                            <div class="builder-toolbar">
                                <button type="button" class="btn btn-outline-primary" onclick="agregarItem()">
                                    <i class="bi bi-plus-lg"></i> Anadir elemento
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

                        <input type="hidden" id="items" name="items">

                        <div class="responsive-actions mt-4">
                            <a href="<?= url('/profesor/lecciones/' . $leccion->id . '/actividades') ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar actividad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let itemsCount = 0;
    const selectedMediaParams = new URLSearchParams(window.location.search);

    function agregarItem(valor = '') {
        itemsCount++;
        const container = document.getElementById('items-container');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'builder-item';
        itemDiv.id = `item-${itemsCount}`;
        itemDiv.innerHTML = `
            <div class="split-head">
                <div class="flex-grow-1">
                    <label class="form-label">Elemento ${itemsCount}</label>
                    <input type="text" class="form-control" name="item_text_${itemsCount}" value="${valor}" placeholder="Texto del elemento" required>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarItem(${itemsCount})">Eliminar</button>
            </div>
        `;
        container.appendChild(itemDiv);
    }

    function eliminarItem(id) {
        const element = document.getElementById(`item-${id}`);
        if (element) {
            element.remove();
        }
    }

    function guardarConfiguracion() {
        const items = [];
        for (let i = 1; i <= itemsCount; i++) {
            const itemElement = document.getElementById(`item-${i}`);
            if (itemElement) {
                const texto = itemElement.querySelector(`[name="item_text_${i}"]`).value.trim();
                if (texto) {
                    items.push(texto);
                }
            }
        }

        if (items.length === 0) {
            alert('Debe anadir al menos un elemento a ordenar');
            return false;
        }

        document.getElementById('items').value = JSON.stringify({
            items: items,
            recurso_apoyo_media_id: selectedMediaParams.get('selected_media_id') || '',
            recurso_apoyo_titulo: selectedMediaParams.get('selected_media_title') || '',
            recurso_apoyo_url: selectedMediaParams.get('selected_media_url') || '',
            recurso_apoyo_tipo: selectedMediaParams.get('selected_media_type') || ''
        });
        return true;
    }

    window.addEventListener('DOMContentLoaded', function() {
        try {
            const contenido = <?php 
                $c = $_SESSION['actividad_temp']['contenido'] ?? '[]';
                echo is_string($c) ? $c : json_encode($c); 
            ?>;

            if (Array.isArray(contenido) && contenido.length > 0) {
                contenido.forEach(item => agregarItem(item));
            } else if (contenido.items && Array.isArray(contenido.items) && contenido.items.length > 0) {
                contenido.items.forEach(item => agregarItem(item));
            } else {
                agregarItem();
                agregarItem();
            }
        } catch (e) {
            agregarItem();
            agregarItem();
        }
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
