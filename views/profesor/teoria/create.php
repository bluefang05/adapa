<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos'); ?>">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/cursos/' . $leccion->curso_id . '/lecciones'); ?>">Lecciones</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>">Teoria</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crear teoria</li>
        </ol>
    </nav>

    <section class="page-hero mb-4">
        <span class="eyebrow"><i class="bi bi-plus-circle"></i> Nueva teoria</span>
        <h1 class="page-title">Crea una pieza teorica clara para la leccion actual.</h1>
        <p class="page-subtitle">
            Define el tipo de contenido, el orden y el tiempo estimado antes de publicar el bloque teorico.
        </p>
        <div class="hero-actions">
            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a teoria
            </a>
        </div>
    </section>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-shell">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Ficha teorica</h2>
                                <span class="soft-badge"><i class="bi bi-book"></i> Leccion: <?php echo htmlspecialchars($leccion->titulo); ?></span>
                            </div>

                            <div class="mb-3">
                                <label for="titulo" class="form-label">Titulo de la teoria</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                                <div class="invalid-feedback">Por favor ingrese un titulo.</div>
                            </div>

                            <div class="mb-3">
                                <label for="contenido" class="form-label">Contenido</label>
                                <textarea class="form-control" id="contenido" name="contenido" rows="10"></textarea>
                                <div class="invalid-feedback">Por favor ingrese el contenido.</div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="section-title">
                                <h2 class="form-section-title">Parametros</h2>
                                <span class="soft-badge"><i class="bi bi-sliders"></i> Publicacion</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="tipo_contenido" class="form-label">Tipo de contenido</label>
                                    <select class="form-select" id="tipo_contenido" name="tipo_contenido" required>
                                        <option value="texto">Texto</option>
                                        <option value="video">Video</option>
                                        <option value="imagen">Imagen</option>
                                        <option value="audio">Audio</option>
                                        <option value="presentacion">Presentacion</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="orden" class="form-label">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="1" value="1" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="duracion_minutos" class="form-label">Duracion en minutos</label>
                                    <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="1" value="10" required>
                                </div>
                            </div>
                        </section>

                        <div class="responsive-actions mt-4">
                            <a href="<?php echo url('/profesor/lecciones/' . $leccion->id . '/teoria'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Crear teoria
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#contenido',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    language: 'es',
    height: 400,
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});

(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (tinymce.get('contenido')) {
                    tinymce.triggerSave();
                    var content = tinymce.get('contenido').getContent();
                    var textarea = document.getElementById('contenido');
                    if (!content || content.trim() === '') {
                        textarea.setCustomValidity('Por favor complete este campo.');
                    } else {
                        textarea.setCustomValidity('');
                    }
                }

                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
