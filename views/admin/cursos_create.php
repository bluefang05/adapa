<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-plus-circle-fill"></i> Nuevo curso</span>
        <h1 class="page-title">Crea cursos de forma centralizada desde administracion.</h1>
        <p class="page-subtitle">Define idioma, nivel, acceso y workflow editorial del curso.</p>
        <div class="hero-actions">
            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a cursos
            </a>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" action="<?php echo url('/admin/cursos/create'); ?>" class="row g-3" id="adminCreateCourseForm">
                        <?php echo csrf_input(); ?>

                        <div class="col-12">
                            <label for="titulo" class="form-label">Titulo *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required maxlength="255">
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" maxlength="1000"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="idioma_objetivo" class="form-label">Idioma objetivo *</label>
                            <select class="form-select" id="idioma_objetivo" name="idioma_objetivo" required>
                                <?php foreach (app_course_target_languages() as $languageValue => $languageLabel): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'ingles' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="idioma_base" class="form-label">Explicado desde *</label>
                            <select class="form-select" id="idioma_base" name="idioma_base" required>
                                <?php foreach (app_supported_languages() as $languageValue => $languageLabel): ?>
                                    <option value="<?php echo htmlspecialchars($languageValue); ?>" <?php echo $languageValue === 'espanol' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($languageLabel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="creado_por" class="form-label">Responsable del curso *</label>
                            <select class="form-select" id="creado_por" name="creado_por" required>
                                <?php foreach (($teachers ?? []) as $teacher): ?>
                                    <option value="<?php echo (int) $teacher->id; ?>" <?php echo (int) $teacher->id === (int) ($selectedTeacherId ?? Auth::getUserId()) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(trim($teacher->nombre . ' ' . $teacher->apellido)); ?> &middot; <?php echo !empty($teacher->es_admin_institucion) ? 'Admin' : 'Profesor'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Define quien quedara como responsable principal del curso dentro de la instancia.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="nivel_cefr" class="form-label">Nivel principal *</label>
                            <select class="form-select" id="nivel_cefr" name="nivel_cefr" required>
                                <?php foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $nivel): ?>
                                    <option value="<?php echo $nivel; ?>" <?php echo $nivel === 'A1' ? 'selected' : ''; ?>><?php echo $nivel; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="nivel_cefr_desde" class="form-label">Rango desde *</label>
                            <select class="form-select" id="nivel_cefr_desde" name="nivel_cefr_desde" required>
                                <?php foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $nivel): ?>
                                    <option value="<?php echo $nivel; ?>" <?php echo $nivel === 'A1' ? 'selected' : ''; ?>><?php echo $nivel; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="nivel_cefr_hasta" class="form-label">Rango hasta *</label>
                            <select class="form-select" id="nivel_cefr_hasta" name="nivel_cefr_hasta" required>
                                <?php foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $nivel): ?>
                                    <option value="<?php echo $nivel; ?>" <?php echo $nivel === 'A1' ? 'selected' : ''; ?>><?php echo $nivel; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="modalidad" class="form-label">Modalidad *</label>
                            <select class="form-select" id="modalidad" name="modalidad" required>
                                <option value="perpetuo" selected>Perpetuo</option>
                                <option value="ciclo">Ciclo</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="max_estudiantes" class="form-label">Maximo estudiantes</label>
                            <input type="number" class="form-control" id="max_estudiantes" name="max_estudiantes" min="0" value="500">
                        </div>

                        <div class="col-md-4">
                            <label for="estado_editorial" class="form-label">Workflow editorial *</label>
                            <select class="form-select" id="estado_editorial" name="estado_editorial" required>
                                <?php foreach (app_course_editorial_states() as $stateValue => $stateMeta): ?>
                                    <option value="<?php echo htmlspecialchars($stateValue); ?>" <?php echo $stateValue === 'borrador' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($stateMeta['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Define si el curso sigue interno, en revision, listo para abrirse o ya publicado.</div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" id="es_publico" name="es_publico" value="1">
                                <label class="form-check-label" for="es_publico">Marcar para catalogo</label>
                            </div>
                            <div class="form-text">La visibilidad real solo se activa cuando el workflow quede en Publicado y exista al menos una leccion publicada.</div>
                            <div class="small text-muted mt-2" id="adminCourseCatalogOutcomeCopy">Resultado actual: oculto del catalogo hasta que lo marques para publicacion.</div>
                            <div class="mt-2"><span class="soft-badge" id="adminCourseCatalogOutcomeBadge">Oculto</span></div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" id="inscripcion_abierta" name="inscripcion_abierta" value="1" checked>
                                <label class="form-check-label" for="inscripcion_abierta">Inscripcion abierta</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mt-md-4">
                                <input class="form-check-input" type="checkbox" id="requiere_codigo" name="requiere_codigo" value="1">
                                <label class="form-check-label" for="requiere_codigo">Requiere codigo</label>
                            </div>
                        </div>

                        <div class="col-md-6" id="codigoWrap" style="display:none;">
                            <label for="codigo_acceso" class="form-label">Codigo de acceso</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codigo_acceso" name="codigo_acceso" maxlength="255">
                                <button class="btn btn-outline-secondary" type="button" id="generarCodigoBtn">Generar</button>
                            </div>
                        </div>

                        <div class="col-md-6" id="tipoCodigoWrap" style="display:none;">
                            <label for="tipo_codigo" class="form-label">Tipo de codigo</label>
                            <select class="form-select" id="tipo_codigo" name="tipo_codigo">
                                <option value="unico_curso">Unico para el curso</option>
                                <option value="por_estudiante">Uno por estudiante</option>
                                <option value="combo_grupo">Combo para grupo</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear curso
                            </button>
                            <a href="<?php echo url('/admin/cursos'); ?>" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const requiereCodigo = document.getElementById('requiere_codigo');
    const codigoWrap = document.getElementById('codigoWrap');
    const tipoCodigoWrap = document.getElementById('tipoCodigoWrap');
    const codigoInput = document.getElementById('codigo_acceso');
    const generarCodigoBtn = document.getElementById('generarCodigoBtn');
    const nivelPrincipal = document.getElementById('nivel_cefr');
    const nivelDesde = document.getElementById('nivel_cefr_desde');
    const nivelHasta = document.getElementById('nivel_cefr_hasta');
    const visibilityCheckbox = document.getElementById('es_publico');
    const workflowSelect = document.getElementById('estado_editorial');
    const catalogOutcomeBadge = document.getElementById('adminCourseCatalogOutcomeBadge');
    const catalogOutcomeCopy = document.getElementById('adminCourseCatalogOutcomeCopy');
    const cefrOrder = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    function toggleCodigo() {
        const active = requiereCodigo.checked;
        codigoWrap.style.display = active ? 'block' : 'none';
        tipoCodigoWrap.style.display = active ? 'block' : 'none';
        codigoInput.required = active;
        if (!active) {
            codigoInput.value = '';
        }
    }

    function generarCodigo() {
        const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 8; i += 1) {
            code += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        codigoInput.value = code;
    }

    function validarRango() {
        const fromIndex = cefrOrder.indexOf(nivelDesde.value);
        const toIndex = cefrOrder.indexOf(nivelHasta.value);
        if (fromIndex > toIndex) {
            nivelHasta.value = nivelDesde.value;
        }
        if (!nivelPrincipal.value) {
            nivelPrincipal.value = nivelDesde.value;
        }
    }

    function syncCatalogOutcome() {
        let label = 'Oculto';
        let tone = '';
        let hint = 'Resultado actual: oculto del catalogo hasta que lo marques para publicacion.';

        if (visibilityCheckbox.checked && workflowSelect.value === 'publicado') {
            label = 'En espera';
            tone = 'warning';
            hint = 'Resultado actual: publicado y marcado para catalogo, pero seguira fuera de la vista del estudiante hasta que exista una leccion publicada.';
        } else if (visibilityCheckbox.checked) {
            label = 'En espera';
            tone = 'info';
            hint = 'Resultado actual: marcado para catalogo, pero el workflow todavia no permite visibilidad real.';
        }

        catalogOutcomeBadge.className = 'soft-badge' + (tone ? ' ' + tone : '');
        catalogOutcomeBadge.textContent = label;
        catalogOutcomeCopy.textContent = hint;
    }

    requiereCodigo.addEventListener('change', toggleCodigo);
    generarCodigoBtn.addEventListener('click', generarCodigo);
    nivelDesde.addEventListener('change', validarRango);
    nivelHasta.addEventListener('change', validarRango);
    visibilityCheckbox.addEventListener('change', syncCatalogOutcome);
    workflowSelect.addEventListener('change', syncCatalogOutcome);

    toggleCodigo();
    syncCatalogOutcome();
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
