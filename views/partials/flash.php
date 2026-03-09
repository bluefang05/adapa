<?php
$flashMessages = [];
if (isset($_SESSION['success'])) {
    $flashMessages[] = [
        'type' => 'success',
        'icon' => 'bi-check-circle-fill',
        'role' => 'status',
        'live' => 'polite',
        'message' => $_SESSION['success'],
    ];
    unset($_SESSION['success']);
}

if (isset($_SESSION['mensaje'])) {
    $flashMessages[] = [
        'type' => 'success',
        'icon' => 'bi-info-circle-fill',
        'role' => 'status',
        'live' => 'polite',
        'message' => $_SESSION['mensaje'],
    ];
    unset($_SESSION['mensaje']);
}

if (isset($_SESSION['error'])) {
    $flashMessages[] = [
        'type' => 'danger',
        'icon' => 'bi-exclamation-triangle-fill',
        'role' => 'alert',
        'live' => 'assertive',
        'message' => $_SESSION['error'],
    ];
    unset($_SESSION['error']);
}
?>
<?php if (!empty($flashMessages)): ?>
    <div class="flash-stack mb-4">
        <?php foreach ($flashMessages as $flash): ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-dismissible fade show" role="<?php echo htmlspecialchars($flash['role']); ?>" aria-live="<?php echo htmlspecialchars($flash['live']); ?>">
                <div class="flash-alert-body">
                    <span class="flash-alert-icon" aria-hidden="true">
                        <i class="bi <?php echo htmlspecialchars($flash['icon']); ?>"></i>
                    </span>
                    <div class="flash-alert-copy"><?php echo htmlspecialchars($flash['message']); ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
