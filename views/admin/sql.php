<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php $adminActiveTab = 'sql'; ?>

<?php
function adminSqlFormatBytes($bytes) {
    $bytes = max(0, (int) $bytes);
    $units = ['B', 'KB', 'MB', 'GB'];
    $index = 0;
    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }
    return round($bytes, $index === 0 ? 0 : 1) . ' ' . $units[$index];
}
?>

<div class="container admin-workspace">
    <section class="page-hero content-hero admin-command-hero mb-4">
        <div>
            <span class="eyebrow"><i class="bi bi-database-gear"></i> Database workspace</span>
            <h1 class="page-title">SQL Manager</h1>
            <p class="page-subtitle">Explora el esquema, ejecuta consultas y exporta resultados sin salir del panel.</p>
        </div>
        <div class="admin-command-meta">
            <span class="soft-badge info"><i class="bi bi-database"></i> <?php echo htmlspecialchars($databaseName); ?></span>
            <span class="soft-badge"><i class="bi bi-table"></i> <?php echo count($tables); ?> tablas</span>
            <span class="soft-badge warning"><i class="bi bi-shield-exclamation"></i> Escrituras con confirmacion</span>
        </div>
        <div class="hero-actions mt-3">
            <form
                method="POST"
                action="<?php echo url('/admin/sql/backup'); ?>"
                onsubmit="return confirm('Descargar una copia completa de la base de datos?');"
            >
                <?php echo csrf_input(); ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-arrow-down-fill"></i> Descargar copia completa
                </button>
            </form>
        </div>
    </section>

    <?php require __DIR__ . '/partials/tabs.php'; ?>
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="sql-workspace">
        <aside class="sql-schema-panel">
            <div class="sql-panel-head">
                <div>
                    <div class="metric-label">Esquema</div>
                    <h2>Tablas</h2>
                </div>
                <span class="soft-badge"><?php echo count($tables); ?></span>
            </div>
            <div class="sql-schema-list">
                <?php foreach ($tables as $table): ?>
                    <details class="sql-table-card">
                        <summary>
                            <span>
                                <i class="bi bi-table"></i>
                                <strong><?php echo htmlspecialchars($table['name']); ?></strong>
                            </span>
                            <small><?php echo adminSqlFormatBytes($table['size_bytes']); ?></small>
                        </summary>
                        <div class="sql-table-meta">
                            <span><?php echo htmlspecialchars((string) ($table['engine'] ?? '')); ?></span>
                            <span>~<?php echo number_format((int) ($table['rows'] ?? 0)); ?> filas</span>
                        </div>
                        <div class="sql-column-list">
                            <?php foreach ($table['columns'] as $column): ?>
                                <button
                                    type="button"
                                    class="sql-column"
                                    data-sql-snippet="`<?php echo htmlspecialchars($table['name'], ENT_QUOTES, 'UTF-8'); ?>`.`<?php echo htmlspecialchars($column['Field'], ENT_QUOTES, 'UTF-8'); ?>`"
                                    title="Insertar columna en el editor"
                                >
                                    <span><?php echo htmlspecialchars($column['Field']); ?></span>
                                    <small><?php echo htmlspecialchars($column['Type']); ?></small>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm w-100 mt-2"
                            data-sql-query="SELECT * FROM `<?php echo htmlspecialchars($table['name'], ENT_QUOTES, 'UTF-8'); ?>` LIMIT 100"
                        >
                            Consultar tabla
                        </button>
                    </details>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="sql-main-panel">
            <section class="panel sql-editor-card">
                <div class="panel-body">
                    <div class="section-title">
                        <div>
                            <div class="metric-label">Editor</div>
                            <h2>Consulta SQL</h2>
                        </div>
                        <div class="responsive-actions">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-sql-query="SHOW TABLES">SHOW TABLES</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-sql-query="SELECT * FROM usuarios ORDER BY id DESC LIMIT 50">Usuarios recientes</button>
                        </div>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo url('/admin/sql'); ?>" id="sql-manager-form">
                        <?php echo csrf_input(); ?>
                        <textarea
                            class="form-control sql-editor"
                            id="sql-query"
                            name="query"
                            rows="10"
                            spellcheck="false"
                            placeholder="SELECT * FROM usuarios ORDER BY id DESC LIMIT 50"
                        ><?php echo htmlspecialchars($query); ?></textarea>

                        <div class="sql-write-confirm mt-3">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="confirm_write" value="1">
                                <span class="form-check-label">Confirmo que esta consulta puede modificar datos o estructura.</span>
                            </label>
                            <input class="form-control" name="confirmation_text" placeholder="Escribe CONFIRMAR para consultas de escritura" autocomplete="off">
                        </div>

                        <div class="responsive-actions mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-play-fill"></i> Ejecutar consulta o script
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="sql-clear">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                            <span class="small text-muted">Admite SET, variables @, transacciones y multiples sentencias. Ctrl + Enter para ejecutar.</span>
                        </div>
                    </form>
                </div>
            </section>

            <?php if (count($scriptResults) > 1): ?>
                <section class="panel mt-4">
                    <div class="panel-body">
                        <div class="section-title">
                            <div>
                                <div class="metric-label">Script completado</div>
                                <h2><?php echo count($scriptResults); ?> sentencias ejecutadas</h2>
                            </div>
                            <?php if ($executionMs !== null): ?>
                                <span class="soft-badge info"><i class="bi bi-stopwatch"></i> <?php echo htmlspecialchars((string) $executionMs); ?> ms</span>
                            <?php endif; ?>
                        </div>

                        <div class="sql-script-results">
                            <?php foreach ($scriptResults as $statementIndex => $statementResult): ?>
                                <details class="sql-script-result" <?php echo $statementResult['type'] === 'rows' ? 'open' : ''; ?>>
                                    <summary>
                                        <span class="soft-badge success">#<?php echo $statementIndex + 1; ?></span>
                                        <code><?php echo htmlspecialchars($statementResult['statement']); ?></code>
                                        <span class="soft-badge info">
                                            <?php echo $statementResult['type'] === 'rows'
                                                ? number_format((int) $statementResult['count']) . ' filas'
                                                : number_format((int) $statementResult['count']) . ' afectadas'; ?>
                                        </span>
                                        <small><?php echo htmlspecialchars((string) $statementResult['execution_ms']); ?> ms</small>
                                    </summary>

                                    <?php if ($statementResult['type'] === 'rows'): ?>
                                        <?php if (!empty($statementResult['truncated'])): ?>
                                            <div class="small text-muted p-2">Vista limitada a 5,000 filas.</div>
                                        <?php endif; ?>
                                        <div class="data-table-shell sql-results mt-2">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead>
                                                        <tr>
                                                            <?php foreach ($statementResult['columns'] as $column): ?>
                                                                <th><?php echo htmlspecialchars($column); ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($statementResult['rows'] as $row): ?>
                                                            <tr>
                                                                <?php foreach ($statementResult['columns'] as $column): ?>
                                                                    <?php $value = $row[$column] ?? null; ?>
                                                                    <td><?php echo $value === null ? '<span class="sql-null">NULL</span>' : htmlspecialchars((string) $value); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php elseif ($result): ?>
                <section class="panel mt-4">
                    <div class="panel-body">
                        <div class="section-title">
                            <div>
                                <div class="metric-label">Resultado</div>
                                <h2>
                                    <?php echo $result['type'] === 'rows'
                                        ? number_format((int) $result['count']) . ' filas'
                                        : number_format((int) $result['count']) . ' filas afectadas'; ?>
                                </h2>
                                <?php if (!empty($result['truncated'])): ?>
                                    <div class="small text-muted">Vista limitada a las primeras 5,000 filas.</div>
                                <?php endif; ?>
                            </div>
                            <div class="responsive-actions">
                                <?php if ($executionMs !== null): ?>
                                    <span class="soft-badge info"><i class="bi bi-stopwatch"></i> <?php echo htmlspecialchars((string) $executionMs); ?> ms</span>
                                <?php endif; ?>
                                <?php if ($result['type'] === 'rows' && !empty($result['columns'])): ?>
                                    <form method="POST" action="<?php echo url('/admin/sql/export'); ?>">
                                        <?php echo csrf_input(); ?>
                                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                                            <i class="bi bi-download"></i> Exportar CSV
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($result['type'] === 'rows'): ?>
                            <div class="data-table-shell sql-results">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <?php foreach ($result['columns'] as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result['rows'] as $row): ?>
                                                <tr>
                                                    <?php foreach ($result['columns'] as $column): ?>
                                                        <?php $value = $row[$column] ?? null; ?>
                                                        <td>
                                                            <?php if ($value === null): ?>
                                                                <span class="sql-null">NULL</span>
                                                            <?php else: ?>
                                                                <?php echo htmlspecialchars((string) $value); ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success mb-0">La sentencia se ejecuto correctamente.</div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($history)): ?>
                <section class="panel mt-4">
                    <div class="panel-body">
                        <div class="section-title"><h2>Historial de esta sesion</h2></div>
                        <div class="sql-history-list">
                            <?php foreach ($history as $entry): ?>
                                <button type="button" class="sql-history-item" data-sql-query="<?php echo htmlspecialchars($entry['query'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="soft-badge <?php echo $entry['type'] === 'write' ? 'warning' : 'info'; ?>"><?php echo htmlspecialchars($entry['type']); ?></span>
                                    <code><?php echo htmlspecialchars($entry['query']); ?></code>
                                    <small><?php echo htmlspecialchars($entry['executed_at']); ?> · <?php echo htmlspecialchars((string) $entry['execution_ms']); ?> ms</small>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editor = document.getElementById('sql-query');
    var form = document.getElementById('sql-manager-form');
    var clearButton = document.getElementById('sql-clear');

    document.querySelectorAll('[data-sql-query]').forEach(function (button) {
        button.addEventListener('click', function () {
            editor.value = button.getAttribute('data-sql-query') || '';
            editor.focus();
        });
    });

    document.querySelectorAll('[data-sql-snippet]').forEach(function (button) {
        button.addEventListener('click', function () {
            var snippet = button.getAttribute('data-sql-snippet') || '';
            var start = editor.selectionStart || editor.value.length;
            editor.value = editor.value.slice(0, start) + snippet + editor.value.slice(editor.selectionEnd || start);
            editor.focus();
        });
    });

    clearButton.addEventListener('click', function () {
        editor.value = '';
        editor.focus();
    });

    editor.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key === 'Enter') {
            event.preventDefault();
            form.requestSubmit();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
