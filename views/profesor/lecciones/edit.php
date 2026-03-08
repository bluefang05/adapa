<?php
require_once __DIR__ . '/../../partials/header.php';
$lessonEditorialStates = app_lesson_editorial_states();
$lessonEditorialMeta = app_lesson_editorial_state_meta($leccion);
?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-pencil-square"></i> Edicion de leccion</span>
        <h1 class="page-title">Actualiza la leccion sin perder el orden del curso.</h1>
        <p class="page-subtitle">
            Ajusta titulo, estado y duracion desde una version mas clara y responsive del formulario.
        </p>
        <div class="d-flex gap-2 flex-wrap mt-3">
            <span class="soft-badge"><i class="bi bi-clipboard-check"></i> Preparacion <?php echo (int) ($lessonPublishSummary['percentage'] ?? 0); ?>%</span>
            <span class="soft-badge badge-<?php echo htmlspecialchars($lessonEditorialMeta['tone']); ?>">
                <i class="bi bi-eye"></i> <?php echo htmlspecialchars($lessonEditorialMeta['label']); ?>
            </span>
        </div>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a lecciones
            </a>
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/builder'); ?>" class="btn btn-outline-primary">
                <i class="bi bi-diagram-3"></i> Constructor
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/profesor/lecciones/edit/' . $leccion->id); ?>" id="formEditarLeccion">
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Checklist de publicacion</h2>
                                <span class="soft-badge"><i class="bi bi-clipboard-check"></i> Control rapido</span>
                            </div>

                            <div class="production-hint-card tone-info mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <div class="production-hint-title">Preparacion actual: <?php echo (int) ($lessonPublishSummary['percentage'] ?? 0); ?>%</div>
                                        <div class="text-muted mt-1"><?php echo htmlspecialchars($lessonPublishHint ?? ''); ?></div>
                                    </div>
                                    <div class="soft-badge"><?php echo (int) ($lessonPublishSummary['ok'] ?? 0); ?>/<?php echo (int) ($lessonPublishSummary['total'] ?? 0); ?> puntos</div>
                                </div>
                                <div class="readiness-meter mt-3"><span style="width: <?php echo (int) ($lessonPublishSummary['percentage'] ?? 0); ?>%"></span></div>
                                <div class="course-meta mt-3">
                                    <span><i class="bi bi-book"></i> <?php echo (int) ($lessonPublishSummary['theories'] ?? 0); ?> teorias</span>
                                    <span><i class="bi bi-lightning"></i> <?php echo (int) ($lessonPublishSummary['activities'] ?? 0); ?> actividades</span>
                                </div>
                            </div>

                            <div class="publish-checklist-grid">
                                <?php foreach (($lessonPublishChecklist ?? []) as $item): ?>
                                    <article class="publish-check-card <?php echo !empty($item['ok']) ? 'is-ready' : 'is-missing'; ?>">
                                        <div class="publish-check-head">
                                            <div class="publish-check-title"><?php echo htmlspecialchars($item['label']); ?></div>
                                            <span class="soft-badge"><?php echo !empty($item['ok']) ? 'OK' : 'Falta'; ?></span>
                                        </div>
                                        <div class="publish-check-copy"><?php echo htmlspecialchars($item['hint']); ?></div>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div id="lessonPublishHint" class="alert alert-warning mt-3 mb-0" <?php echo app_lesson_editorial_state_value($leccion) === 'publicado' && (int) ($lessonPublishSummary['percentage'] ?? 0) < 100 ? '' : 'hidden'; ?>>
                                <i class="bi bi-exclamation-triangle"></i>
                                La leccion esta publicada, pero todavia tiene huecos que el alumno puede notar.
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Base de la leccion</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> Curso: <?php echo htmlspecialchars($curso->titulo); ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la leccion *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($leccion->titulo); ?>" placeholder="Ejemplo: Introduccion a los verbos en ingles">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe brevemente el contenido de esta leccion."><?php echo htmlspecialchars($leccion->descripcion); ?></textarea>
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
                                    <input type="number" class="form-control" id="orden" name="orden" value="<?php echo htmlspecialchars($leccion->orden); ?>" min="1" required>
                                    <div class="form-text">Posicion de la leccion dentro del curso.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="duracion_minutos" class="form-label">Duracion estimada en minutos</label>
                                    <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="1" value="<?php echo htmlspecialchars($leccion->duracion_minutos); ?>" placeholder="Ejemplo: 45">
                                    <div class="form-text">Tiempo estimado para completar la leccion.</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="es_obligatoria" name="es_obligatoria" value="1" <?php echo $leccion->es_obligatoria ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="es_obligatoria">Leccion obligatoria</label>
                                    </div>
                                    <div class="form-text">El estudiante debe completarla para avanzar.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="estado_editorial" class="form-label">Estado editorial *</label>
                                    <select class="form-select" id="estado_editorial" name="estado_editorial" required>
                                        <?php foreach ($lessonEditorialStates as $stateValue => $stateMeta): ?>
                                            <option value="<?php echo htmlspecialchars($stateValue); ?>" <?php echo app_lesson_editorial_state_value($leccion) === $stateValue ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($stateMeta['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text" id="lessonEditorialEditDescription"><?php echo htmlspecialchars($lessonEditorialMeta['description']); ?></div>
                                </div>
                            </div>
                        </section>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/cursos/' . $curso->id . '/lecciones'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const lessonStateSelect = document.getElementById('estado_editorial');
const lessonPublishHint = document.getElementById('lessonPublishHint');
const lessonEditorialEditDescription = document.getElementById('lessonEditorialEditDescription');
const lessonEditorialStates = <?php echo json_encode($lessonEditorialStates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const lessonPublishPercentage = <?php echo (int) ($lessonPublishSummary['percentage'] ?? 0); ?>;
const lessonEditForm = document.getElementById('formEditarLeccion');

function syncLessonPublishHint() {
    if (!lessonStateSelect || !lessonPublishHint) {
        return;
    }

    lessonPublishHint.hidden = !(lessonStateSelect.value === 'publicado' && lessonPublishPercentage < 100);
}

if (lessonStateSelect) {
    lessonStateSelect.addEventListener('change', function () {
        const current = lessonEditorialStates[lessonStateSelect.value] || lessonEditorialStates.borrador;
        lessonEditorialEditDescription.textContent = current.description || '';
        syncLessonPublishHint();
    });
    syncLessonPublishHint();
}

if (lessonEditForm) {
    lessonEditForm.addEventListener('submit', function (event) {
        if (lessonStateSelect && lessonStateSelect.value === 'publicado' && lessonPublishPercentage < 100) {
            const shouldContinue = window.confirm('Esta leccion se publicara aunque todavia tenga huecos editoriales. Quieres continuar de todos modos?');
            if (!shouldContinue) {
                event.preventDefault();
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
