<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<section class="marketing-hero">
    <div class="container">
        <div class="hero-copy">
            <span class="eyebrow"><i class="bi bi-translate"></i> LMS de idiomas</span>
            <h1 class="page-title">Aprende idiomas y continua donde lo dejaste.</h1>
            <p class="page-subtitle">Teoria, practica y progreso en un solo flujo.</p>
            <div class="hero-actions marketing-grid">
                <a class="btn btn-primary btn-lg" href="<?php echo url('/register'); ?>">Crear cuenta</a>
                <a class="btn btn-outline-light btn-lg" href="<?php echo url('/login'); ?>">Iniciar sesion</a>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="feature-grid">
        <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-journal-text"></i></div>
            <h3>Cursos interactivos</h3>
            <p>Lecciones claras con teoria y practica aplicable.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <h3>Progreso visible</h3>
            <p>Retoma rapido tu siguiente paso sin perder contexto.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-people"></i></div>
            <h3>Roles claros</h3>
            <p>Experiencia separada para estudiante, profesor y admin.</p>
        </article>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
