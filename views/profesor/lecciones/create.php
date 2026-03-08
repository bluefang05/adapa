<?php
require_once __DIR__ . '/../../partials/header.php';
$lessonEditorialStates = app_lesson_editorial_states();
?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-plus-circle"></i> Nueva leccion</span>
        <h1 class="page-title">Agrega una leccion clara y bien ordenada dentro del curso.</h1>
        <p class="page-subtitle">
            Define orden, duracion y visibilidad desde una vista mas legible para escritorio y movil.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($planUso['is_free'])): ?>
        <div class="alert alert-info">
            <i class="bi bi-lightbulb"></i>
            Plan gratuito: puedes crear hasta 3 lecciones dentro de este curso piloto.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones/create'); ?>" id="formCrearLeccion">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Base de la leccion</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> Curso: <?php echo htmlspecialchars($curso->titulo); ?></span>
                            </div>

                            <div class="production-hint-card tone-info mb-3">
                                <div class="production-hint-title">Checklist rapido antes de crear</div>
                                <ul class="quality-checklist-list mb-0">
                                    <li>El titulo deja claro que habilidad, tema o situacion cubre la leccion.</li>
                                    <li>La descripcion ayuda al alumno a entender que lograra al completarla.</li>
                                    <li>El orden, la duracion y el estado coinciden con el momento real del curso.</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la leccion *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ejemplo: Introduccion a los verbos en ingles">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe brevemente el contenido de esta leccion."></textarea>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Orden y estado</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Secuencia</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="orden" class="form-label">Orden *</label>
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?php echo (int) $siguiente_orden; ?>" min="1" required>
                                    <div class="form-text">Posicion de la leccion dentro del curso.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="duracion_minutos" class="form-label">Duracion estimada en minutos</label>
                                    <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="1" placeholder="Ejemplo: 45">
                                    <div class="form-text">Tiempo estimado para completar la leccion.</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="es_obligatoria" name="es_obligatoria" value="1" checked>
                                        <label class="form-check-label" for="es_obligatoria">Leccion obligatoria</label>
                                    </div>
                                    <div class="form-text">El estudiante debe completarla para avanzar.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="estado_editorial" class="form-label">Estado editorial *</label>
                                    <select class="form-select" id="estado_editorial" name="estado_editorial" required>
                                        <?php foreach ($lessonEditorialStates as $stateValue => $stateMeta): ?>
                                            <option value="<?php echo htmlspecialchars($stateValue); ?>" <?php echo $stateValue === 'borrador' ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($stateMeta['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text" id="lessonEditorialCreateDescription">La leccion sigue interna hasta que marques un estado mas avanzado.</div>
                                </div>
                            </div>
                        </section>

                        <div class="alert alert-info mt-4">
                            <i class="bi bi-lightbulb"></i>
                            Despues de guardar entraras directo al constructor de la leccion para anadir teoria, practica y recursos sin perder el hilo.
                        </div>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear leccion
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const lessonCreateForm = document.getElementById('formCrearLeccion');
const lessonCreateState = document.getElementById('estado_editorial');
const lessonEditorialStates = <?php echo json_encode($lessonEditorialStates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const lessonEditorialCreateDescription = document.getElementById('lessonEditorialCreateDescription');

if (lessonCreateForm && lessonCreateState) {
    lessonCreateForm.addEventListener('submit', function (event) {
        if (lessonCreateState.value === 'publicado') {
            const shouldContinue = window.confirm('La leccion se creara como Publicada desde el inicio. Todavia faltara completar teoria y practica despues de guardarla. ¿Quieres continuar?');
            if (!shouldContinue) {
                event.preventDefault();
            }
        }
    });
}

if (lessonCreateState && lessonEditorialCreateDescription) {
    const syncLessonCreateDescription = function () {
        const current = lessonEditorialStates[lessonCreateState.value] || lessonEditorialStates.borrador;
        lessonEditorialCreateDescription.textContent = current.description || '';
    };

    lessonCreateState.addEventListener('change', syncLessonCreateDescription);
    syncLessonCreateDescription();
}
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
