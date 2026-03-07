<?php
$issueReportTitle = $issueReportTitle ?? 'Reportar un fallo';
$issueReportAction = $issueReportAction ?? url('/reportar-fallo');
$issueReportContextType = $issueReportContextType ?? 'general';
$issueReportContextId = $issueReportContextId ?? uniqid('issue_', false);
$issueReportReturnTo = $issueReportReturnTo ?? ($_SERVER['REQUEST_URI'] ?? '/');
$issueReportCourseId = $issueReportCourseId ?? null;
$issueReportLessonId = $issueReportLessonId ?? null;
$issueReportActivityId = $issueReportActivityId ?? null;
$issueReportDescriptionPlaceholder = $issueReportDescriptionPlaceholder ?? 'Que paso, en que parte y como lo reproduces.';
?>
<section class="panel mt-4 issue-report-panel">
    <div class="panel-body">
        <details class="issue-report-details">
            <summary class="issue-report-summary">
                <i class="bi bi-bug"></i> <?php echo htmlspecialchars($issueReportTitle); ?>
            </summary>
            <form action="<?php echo htmlspecialchars($issueReportAction); ?>" method="POST" class="mt-3">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="context_type" value="<?php echo htmlspecialchars($issueReportContextType); ?>">
                <?php if ($issueReportCourseId !== null): ?>
                    <input type="hidden" name="curso_id" value="<?php echo (int) $issueReportCourseId; ?>">
                <?php endif; ?>
                <?php if ($issueReportLessonId !== null): ?>
                    <input type="hidden" name="leccion_id" value="<?php echo (int) $issueReportLessonId; ?>">
                <?php endif; ?>
                <?php if ($issueReportActivityId !== null): ?>
                    <input type="hidden" name="actividad_id" value="<?php echo (int) $issueReportActivityId; ?>">
                <?php endif; ?>
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($issueReportReturnTo, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="issue_type_<?php echo htmlspecialchars($issueReportContextId); ?>">Tipo de fallo</label>
                        <select id="issue_type_<?php echo htmlspecialchars($issueReportContextId); ?>" name="issue_type" class="form-select" required>
                            <option value="error_visual">Error visual</option>
                            <option value="contenido_incorrecto">Contenido incorrecto</option>
                            <option value="boton_no_funciona">Boton no funciona</option>
                            <option value="audio_video">Audio/Video</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="issue_desc_<?php echo htmlspecialchars($issueReportContextId); ?>">Describe el problema</label>
                        <textarea id="issue_desc_<?php echo htmlspecialchars($issueReportContextId); ?>" name="description" class="form-control" rows="3" minlength="12" maxlength="2000" required placeholder="<?php echo htmlspecialchars($issueReportDescriptionPlaceholder); ?>"></textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-send"></i> Enviar reporte
                    </button>
                </div>
            </form>
        </details>
    </div>
</section>
