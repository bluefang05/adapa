<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container auth-layout">
    <div class="auth-shell">
        <section class="page-hero auth-hero">
            <span class="eyebrow"><i class="bi bi-shield-lock"></i> Interno</span>
            <h1 class="page-title">Acceso rapido interno</h1>
            <p class="page-subtitle">Uso interno para QA y soporte. No compartir esta URL.</p>
        </section>

        <section class="form-shell auth-card">
            <div class="card-body">
                <?php require __DIR__ . '/../../partials/flash.php'; ?>
                <div class="row g-3">
                    <?php foreach ($accounts as $account): ?>
                        <div class="col-md-4">
                            <div class="surface-card h-100">
                                <div class="card-body">
                                    <div class="metric-label mb-2"><?php echo htmlspecialchars($account['label']); ?></div>
                                    <div class="small text-muted mb-3"><?php echo htmlspecialchars($account['email']); ?></div>

                                    <form method="POST" action="<?php echo url('/enm/login'); ?>" class="d-grid gap-2">
                                        <?php echo csrf_input(); ?>
                                        <input type="hidden" name="account_key" value="<?php echo htmlspecialchars($account['key']); ?>">
                                        <button type="submit" class="btn btn-<?php echo htmlspecialchars($account['accent']); ?>">
                                            <i class="bi bi-box-arrow-in-right"></i> Entrar como <?php echo htmlspecialchars($account['label']); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <a href="<?php echo url('/login'); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a login normal
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
