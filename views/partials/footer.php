    </main>
<?php
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
    <footer class="app-footer">
        <div class="container">
            <div class="footer-shell d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="footer-brand">ADAPA</div>
                    <div>LMS para aprender idiomas con teoria, practica y progreso.</div>
                </div>
                <div class="footer-controls small text-lg-end">
                    <div>Enfocado en continuidad de aprendizaje y avance visible.</div>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="mt-1">
                            <a class="footer-text-btn" href="<?php echo url('/enm'); ?>">Acceso interno</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            if (window.__adapaThemeInitDone) {
                return;
            }
            window.__adapaThemeInitDone = true;

            var root = document.documentElement;
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var themeApi = window.__adapaTheme || {};
            var themeSequence = Array.isArray(themeApi.themeSequence)
                ? themeApi.themeSequence.slice()
                : ['warm', 'paper', 'sky', 'dark'];
            var themeLabelMap = {
                warm: 'Tema: Calido',
                paper: 'Tema: Paper',
                sky: 'Tema: Sky',
                dark: 'Tema: Oscuro'
            };

            function getToggles() {
                return document.querySelectorAll('[data-theme-toggle]');
            }

            function sanitizeTheme(theme) {
                if (typeof themeApi.sanitizeTheme === 'function') {
                    return themeApi.sanitizeTheme(theme);
                }

                if (theme === 'light') {
                    theme = 'warm';
                }

                return themeSequence.indexOf(theme) !== -1 ? theme : null;
            }

            function persistTheme(theme) {
                try {
                    localStorage.setItem('adapa-theme', theme);
                } catch (error) {
                }

                try {
                    document.cookie = 'adapa-theme=' + encodeURIComponent(theme) + '; path=/; max-age=31536000; SameSite=Lax';
                } catch (error) {
                }
            }

            function syncToggles(theme) {
                var isDark = theme === 'dark';
                var labelText = themeLabelMap[theme] || 'Tema';
                getToggles().forEach(function (toggle) {
                    var darkIcon = toggle.querySelector('[data-theme-icon="dark"]');
                    var lightIcon = toggle.querySelector('[data-theme-icon="light"]');
                    var label = toggle.querySelector('[data-theme-label]');

                    toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                    toggle.setAttribute('aria-label', 'Cambiar tema. Actual: ' + labelText);

                    if (label) {
                        label.textContent = labelText;
                    }

                    if (darkIcon) {
                        darkIcon.classList.toggle('d-none', isDark);
                    }

                    if (lightIcon) {
                        lightIcon.classList.toggle('d-none', !isDark);
                    }
                });
            }

            function applyTheme(theme, options) {
                var safeTheme = sanitizeTheme(theme) || 'light';

                if (typeof themeApi.applyRootTheme === 'function') {
                    safeTheme = themeApi.applyRootTheme(safeTheme);
                } else {
                    root.setAttribute('data-theme', safeTheme);
                    root.setAttribute('data-bs-theme', safeTheme);
                    root.classList.remove('theme-light', 'theme-dark');
                    root.classList.add('theme-' + safeTheme);
                }

                syncToggles(safeTheme);

                if (!options || !options.skipEvent) {
                    window.dispatchEvent(new CustomEvent('adapa:themechange', {
                        detail: { theme: safeTheme }
                    }));
                }

                return safeTheme;
            }

            function saveThemeOnServer(theme) {
                if (!csrfMeta || !window.fetch) {
                    return;
                }

                var formData = new URLSearchParams();
                formData.append('_csrf', csrfMeta.getAttribute('content') || '');
                formData.append('theme', theme);

                fetch('<?php echo url('/theme/preference'); ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: formData.toString()
                }).catch(function () {
                });
            }

            function normalizePath(path) {
                if (!path) {
                    return '/';
                }

                try {
                    path = path.split('?')[0].split('#')[0];
                } catch (error) {
                }

                if (path.length > 1 && path.endsWith('/')) {
                    path = path.slice(0, -1);
                }

                return path || '/';
            }

            function markActiveNavLink() {
                var currentPath = normalizePath(window.location.pathname);
                var links = document.querySelectorAll('.app-navbar .nav-link[href]');

                links.forEach(function (link) {
                    var hrefPath = normalizePath(link.getAttribute('href'));
                    var active = currentPath === hrefPath || (hrefPath !== '/' && currentPath.indexOf(hrefPath + '/') === 0);

                    if (active) {
                        link.classList.add('active');
                        link.setAttribute('aria-current', 'page');
                    } else {
                        link.classList.remove('active');
                        link.removeAttribute('aria-current');
                    }
                });
            }

            function lockSubmittingForms() {
                document.addEventListener('submit', function (event) {
                    var form = event.target;
                    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-submit-lock')) {
                        return;
                    }

                    var submitter = event.submitter;
                    if (!submitter || !(submitter instanceof HTMLButtonElement) || submitter.disabled) {
                        return;
                    }

                    submitter.dataset.originalText = submitter.innerHTML;
                    submitter.disabled = true;
                    submitter.classList.add('is-submitting');
                    submitter.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Procesando...';
                });
            }

            // En carga normal, el tema ya debe venir aplicado desde header (<html data-theme=...>).
            // Evitamos recalcular desde storage aqui para no provocar un segundo cambio de tema.
            var initialTheme =
                sanitizeTheme(root.getAttribute('data-theme')) ||
                sanitizeTheme(window.__adapaInitialTheme || null) ||
                sanitizeTheme(document.body ? document.body.getAttribute('data-server-theme') : null) ||
                'light';
            applyTheme(initialTheme, { skipEvent: true });
            markActiveNavLink();
            lockSubmittingForms();

            document.addEventListener('click', function (event) {
                var toggle = event.target.closest('[data-theme-toggle]');
                if (!toggle) {
                    return;
                }

                var currentTheme = sanitizeTheme(root.getAttribute('data-theme')) || 'warm';
                var currentIndex = themeSequence.indexOf(currentTheme);
                var nextTheme = themeSequence[(currentIndex + 1 + themeSequence.length) % themeSequence.length];
                persistTheme(nextTheme);
                applyTheme(nextTheme);
                saveThemeOnServer(nextTheme);
            });

            window.addEventListener('pageshow', function (event) {
                var restoredTheme =
                    sanitizeTheme(root.getAttribute('data-theme')) ||
                    sanitizeTheme(window.__adapaInitialTheme || null) ||
                    sanitizeTheme(document.body ? document.body.getAttribute('data-server-theme') : null) ||
                    'warm';
                if (event && event.persisted) {
                    // Chrome bfcache: defer one paint cycle before re-applying theme.
                    requestAnimationFrame(function () {
                        applyTheme(restoredTheme, { skipEvent: true });
                    });
                } else {
                    applyTheme(restoredTheme, { skipEvent: true });
                }
            });
        }());
    </script>
</body>
</html>
