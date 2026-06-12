<?php
$adminActiveTab = $adminActiveTab ?? 'overview';
$adminTabs = [
    'overview' => ['label' => 'Resumen', 'icon' => 'bi-grid-1x2-fill', 'url' => '/admin'],
    'users' => ['label' => 'Usuarios', 'icon' => 'bi-people-fill', 'url' => '/admin/usuarios'],
    'content' => ['label' => 'Contenido', 'icon' => 'bi-journal-richtext', 'url' => '/admin/cursos'],
    'support' => ['label' => 'Soporte', 'icon' => 'bi-life-preserver', 'url' => '/admin/tickets'],
    'system' => ['label' => 'Sistema', 'icon' => 'bi-activity', 'url' => '/admin/actividad'],
    'sql' => ['label' => 'SQL Manager', 'icon' => 'bi-database-gear', 'url' => '/admin/sql'],
];
?>
<nav class="admin-tabs mb-4" aria-label="Secciones de administracion">
    <?php foreach ($adminTabs as $key => $tab): ?>
        <a
            href="<?php echo url($tab['url']); ?>"
            class="admin-tab <?php echo $adminActiveTab === $key ? 'active' : ''; ?>"
            <?php echo $adminActiveTab === $key ? 'aria-current="page"' : ''; ?>
        >
            <i class="bi <?php echo htmlspecialchars($tab['icon']); ?>"></i>
            <span><?php echo htmlspecialchars($tab['label']); ?></span>
        </a>
    <?php endforeach; ?>
</nav>
