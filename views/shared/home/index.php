<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<section class="marketing-hero">
    <div class="container">
        <div class="hero-copy">
            <span class="eyebrow"><i class="bi bi-translate"></i> LMS de idiomas</span>
            <h1 class="page-title">Aprendizaje guiado, interactivo y medible</h1>
            <p class="page-subtitle">ADAPA combina teoria, practica, progreso y paneles por rol para que estudiantes, docentes y administradores trabajen en la misma plataforma.</p>
            <div class="hero-actions marketing-grid">
                <a class="btn btn-primary btn-lg" href="<?php echo url('/register'); ?>">Crear cuenta</a>
                <a class="btn btn-outline-light btn-lg" href="<?php echo url('/login'); ?>">Iniciar sesion</a>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-stars"></i> Lo esencial</span>
        <h2 class="page-title">Una experiencia pensada para aprender idiomas</h2>
        <p class="page-subtitle">Desde actividades interactivas hasta seguimiento de progreso, la plataforma ya trabaja sobre el flujo real del aula.</p>
    </section>

    <div class="feature-grid">
        <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-journal-text"></i></div>
            <h3>Cursos interactivos</h3>
            <p>Combina teoria, lecciones, actividades guiadas y tipos de practica alineados al aprendizaje de idiomas.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <h3>Progreso visible</h3>
            <p>Estudiantes y docentes pueden seguir avance por curso, leccion y respuesta sin perder continuidad.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="bi bi-people"></i></div>
            <h3>Roles claros</h3>
            <p>Administra usuarios, construye contenido docente y aprende desde una navegacion coherente para cada rol.</p>
        </article>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
