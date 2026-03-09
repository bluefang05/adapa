<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>">Actividades</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crear actividad</li>
        </ol>
    </nav>

    <section class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-lightning-charge"></i> Nueva actividad</span>
        <h1 class="page-title">Configura una practica clara para la leccion actual.</h1>
        <p class="page-subtitle">
            Crea la actividad base y luego ajusta su contenido especifico segun el tipo que elijas.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a actividades
            </a>
            <a href="<?php echo url('/profesor/recursos?return_to=' . rawurlencode(url('/profesor/lecciones/' . $leccion->id . '/actividades/create')) . '&context=actividad'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-images"></i> Abrir biblioteca
            </a>
        </div>
        <div class="compact-meta-row">
            <span class="soft-badge info"><i class="bi bi-collection"></i> Leccion <?php echo (int) $leccion->orden; ?></span>
            <span class="soft-badge"><i class="bi bi-journal-richtext"></i> <?php echo htmlspecialchars($leccion->titulo); ?></span>
            <span class="soft-badge"><i class="bi bi-grid"></i> <?php echo count($tipos_actividad); ?> tipos disponibles</span>
        </div>
    </section>

    <?php require __DIR__ . '/../../partials/flash.php'; ?>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert context-note mb-4">
            <strong>Plan gratuito:</strong> esta leccion admite hasta 3 actividades antes de pasar al plan activo.
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['selected_media_id'])): ?>
        <div class="alert context-note mb-4">
            <i class="bi bi-check2-circle"></i>
            Recurso contextual listo: <strong><?php echo htmlspecialchars((string) ($_GET['selected_media_title'] ?? 'Recurso seleccionado')); ?></strong>.
            Si eliges una actividad de escucha o una configuracion con imagen, te recordare usarlo en el siguiente paso.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Datos base</h2>
                                <span class="soft-badge"><i class="bi bi-pencil-square"></i> Configuracion</span>
                            </div>

                            <details class="panel page-assist-card mb-3">
                                <summary class="page-assist-summary">
                                    <div class="section-title mb-3">
                                        <h3 class="h5 mb-0">Elige mejor, crea mas rapido</h3>
                                        <span class="soft-badge"><i class="bi bi-signpost-split"></i> Guía</span>
                                    </div>
                                </summary>
                                <div class="panel-body pt-0 page-assist-body">
                                    <div class="quality-checklist">
                                        <div class="quality-checklist-title">Usa cada tipo para esto:</div>
                                        <ul class="quality-checklist-list">
                                            <li>Opcion multiple o verdadero/falso para comprobar comprension rapida.</li>
                                            <li>Ordenar palabras o completar oracion para fijar estructura.</li>
                                            <li>Escucha y pronunciacion para ritmo, sonido y reconocimiento.</li>
                                            <li>Respuesta larga o proyecto para produccion abierta.</li>
                                        </ul>
                                    </div>
                                    <div class="alert context-note mt-3 mb-0">
                                        <div class="fw-semibold mb-2">Checklist rapido antes de crear</div>
                                        <ul class="quality-checklist-list mb-0">
                                            <li>El tipo de actividad mide exactamente lo que ensena la leccion.</li>
                                            <li>El titulo y la descripcion dicen que debe hacer el alumno.</li>
                                            <li>Los puntos y el tiempo no castigan una tarea simple ni regalan una compleja.</li>
                                        </ul>
                                    </div>
                                </div>
                            </details>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la actividad</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ejemplo: Completa el dialogo en presente simple">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required placeholder="Indica al estudiante que debe hacer y como responder."></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Parametros de evaluacion</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Escala</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tipo_actividad" class="form-label">Tipo de actividad</label>
                                    <select class="form-select" id="tipo_actividad" name="tipo_actividad" required>
                                        <option value="">Seleccione un tipo</option>
                                        <?php foreach ($tipos_actividad as $tipo => $label): ?>
                                            <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="1" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="puntos_maximos" class="form-label">Puntos maximos</label>
                                    <input type="number" class="form-control" id="puntos_maximos" name="puntos_maximos" value="10" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tiempo_limite_minutos" class="form-label">Tiempo limite en minutos</label>
                                    <input type="number" class="form-control" id="tiempo_limite_minutos" name="tiempo_limite_minutos" value="10" min="1" required>
                                </div>
                            </div>
                        </section>

                        <div class="alert context-note mt-4" role="alert">
                            Despues de crear la actividad podras configurar el contenido especifico segun el tipo seleccionado.
                        </div>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/actividades'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear actividad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>

<script>
(function () {
    const typeSelect = document.getElementById('tipo_actividad');
    const titleInput = document.getElementById('titulo');
    const descriptionInput = document.getElementById('descripcion');
    const pointsInput = document.getElementById('puntos_maximos');
    const timeInput = document.getElementById('tiempo_limite_minutos');
    const selectedParams = new URLSearchParams(window.location.search);

    const defaults = {
        opcion_multiple: ['Chequeo rapido de comprension', 'Selecciona la opcion correcta segun lo trabajado en la leccion.', 10, 5],
        verdadero_falso: ['Verdadero o falso en contexto', 'Decide si cada afirmacion coincide con la teoria o el dialogo revisado.', 10, 4],
        completar_oracion: ['Completa la frase clave', 'Rellena los huecos con la palabra o estructura correcta.', 12, 6],
        ordenar_palabras: ['Reconstruye la frase', 'Ordena las palabras hasta formar una frase natural y correcta.', 10, 4],
        respuesta_corta: ['Respuesta breve de precision', 'Responde con una palabra o frase corta segun la consigna.', 10, 4],
        respuesta_larga: ['Produccion guiada', 'Escribe una respuesta corta con tus propias palabras usando la estructura vista.', 20, 12],
        escucha: ['Escucha y detecta la idea clave', 'Escucha el audio y responde segun lo que entiendas.', 15, 6],
        pronunciacion: ['Imita y pronuncia la frase', 'Escucha, repite y comprueba si reproduces la frase con suficiente claridad.', 15, 6],
        arrastrar_soltar: ['Relaciona y organiza', 'Arrastra cada elemento hasta su categoria o lugar correcto.', 12, 6],
        emparejamiento: ['Empareja concepto y significado', 'Relaciona cada elemento con su pareja correcta.', 12, 5],
        proyecto: ['Mini tarea aplicada', 'Entrega una produccion breve que combine vocabulario, estructura y sentido.', 25, 20]
    };

    if (!typeSelect) {
        return;
    }

    typeSelect.addEventListener('change', function () {
        const selected = defaults[typeSelect.value];
        if (!selected) {
            return;
        }

        if (!titleInput.value.trim()) {
            titleInput.value = selected[0];
        }
        if (!descriptionInput.value.trim()) {
            descriptionInput.value = selected[1];
        }
        pointsInput.value = selected[2];
        timeInput.value = selected[3];

        const selectedMediaTitle = selectedParams.get('selected_media_title');
        if (selectedMediaTitle && (typeSelect.value === 'escucha' || typeSelect.value === 'opcion_multiple')) {
            const extra = typeSelect.value === 'escucha'
                ? ' Recurso sugerido para usar al configurar: ' + selectedMediaTitle + '.'
                : ' Recurso sugerido para imagen de pregunta: ' + selectedMediaTitle + '.';
            if (descriptionInput.value.indexOf(selectedMediaTitle) === -1) {
                descriptionInput.value = descriptionInput.value.trim() + extra;
            }
        }
    });
})();
</script>
