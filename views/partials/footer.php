    </main>
    <footer class="app-footer">
        <div class="container">
            <div class="footer-theme-bar">
                <button type="button" class="theme-toggle theme-toggle-footer" data-theme-toggle aria-label="Cambiar modo oscuro" aria-pressed="false">
                    <i class="bi bi-moon-stars-fill" data-theme-icon="dark"></i>
                    <i class="bi bi-sun-fill d-none" data-theme-icon="light"></i>
                    <span data-theme-label>Modo oscuro</span>
                </button>
            </div>
            <div class="footer-shell d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="footer-brand">ADAPA</div>
                    <div>Plataforma LMS enfocada en rutas de aprendizaje, teoria guiada y practica interactiva.</div>
                </div>
                <div class="footer-controls small text-lg-end">
                    <div>Interfaz en consolidacion con seguimiento visible para estudiantes y docentes.</div>
                    <div>Estado actual: operativa y en refinamiento de producto.</div>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var root = document.documentElement;
            var toggles = document.querySelectorAll('[data-theme-toggle]');

            if (!toggles.length) {
                return;
            }

            function applyTheme(theme) {
                var isDark = theme === 'dark';
                root.setAttribute('data-theme', theme);

                toggles.forEach(function (toggle) {
                    var darkIcon = toggle.querySelector('[data-theme-icon="dark"]');
                    var lightIcon = toggle.querySelector('[data-theme-icon="light"]');
                    var label = toggle.querySelector('[data-theme-label]');

                    toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');

                    if (label) {
                        label.textContent = isDark ? 'Modo claro' : 'Modo oscuro';
                    }

                    if (darkIcon) {
                        darkIcon.classList.toggle('d-none', isDark);
                    }

                    if (lightIcon) {
                        lightIcon.classList.toggle('d-none', !isDark);
                    }
                });
            }

            applyTheme(root.getAttribute('data-theme') || 'light');

            toggles.forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

                    try {
                        localStorage.setItem('adapa-theme', nextTheme);
                    } catch (error) {
                    }

                    applyTheme(nextTheme);
                });
            });
        }());
    </script>
</body>
</html>
