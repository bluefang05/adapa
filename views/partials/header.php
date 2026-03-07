<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<?php
$serverThemeRaw = $_SESSION['theme_preference'] ?? ($_COOKIE['adapa-theme'] ?? 'warm');
$themeAliases = [
    'light' => 'warm',
];
$serverThemeRaw = $themeAliases[$serverThemeRaw] ?? $serverThemeRaw;
$allowedThemes = ['warm', 'paper', 'sky', 'dark'];
$serverTheme = in_array($serverThemeRaw, $allowedThemes, true) ? $serverThemeRaw : 'warm';
$serverThemeClass = 'theme-' . $serverTheme;
$serverThemeModeClass = $serverTheme === 'dark' ? 'theme-dark' : 'theme-light';
$appCssPath = __DIR__ . '/../../assets/css/app.css';
$appCssVersion = file_exists($appCssPath) ? (string) filemtime($appCssPath) : '1';
?>
<html
    lang="es"
    data-theme="<?php echo htmlspecialchars($serverTheme, ENT_QUOTES, 'UTF-8'); ?>"
    data-bs-theme="<?php echo $serverTheme === 'dark' ? 'dark' : 'light'; ?>"
    class="<?php echo htmlspecialchars($serverThemeClass . ' ' . $serverThemeModeClass, ENT_QUOTES, 'UTF-8'); ?>"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title>Adapa - Plataforma de Aprendizaje</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo url('/assets/css/app.css'); ?>?v=<?php echo urlencode($appCssVersion); ?>">
    <script>
        (function () {
            var root = document.documentElement;
            var themeAliases = { light: 'warm' };
            var themeSequence = ['warm', 'paper', 'sky', 'dark'];

            function sanitizeTheme(theme) {
                if (themeAliases[theme]) {
                    theme = themeAliases[theme];
                }

                return themeSequence.indexOf(theme) !== -1 ? theme : null;
            }

            function getThemeMode(theme) {
                return theme === 'dark' ? 'dark' : 'light';
            }

            function applyRootTheme(theme) {
                var safeTheme = sanitizeTheme(theme) || 'warm';
                var modeTheme = getThemeMode(safeTheme);
                root.setAttribute('data-theme', safeTheme);
                root.setAttribute('data-bs-theme', modeTheme);
                root.classList.remove('theme-warm', 'theme-paper', 'theme-sky', 'theme-dark', 'theme-light');
                root.classList.add('theme-' + safeTheme);
                root.classList.add('theme-' + modeTheme);

                return safeTheme;
            }

            window.__adapaTheme = window.__adapaTheme || {};
            window.__adapaTheme.sanitizeTheme = sanitizeTheme;
            window.__adapaTheme.applyRootTheme = applyRootTheme;
            window.__adapaTheme.themeSequence = themeSequence.slice();
            window.__adapaTheme.getThemeMode = getThemeMode;
            var serverTheme = <?php echo json_encode($serverTheme); ?>;
            // Arranque estable: usar el tema resuelto por backend para evitar saltos de dark->light en Chrome.
            var appliedTheme = applyRootTheme(serverTheme);
            window.__adapaInitialTheme = appliedTheme;
        }());
    </script>
</head>
<body
    data-server-theme="<?php echo htmlspecialchars($serverTheme, ENT_QUOTES, 'UTF-8'); ?>"
>
<a class="skip-link" href="#main-content">Saltar al contenido</a>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?php echo url('/'); ?>">
            <i class="bi bi-mortarboard-fill"></i> ADAPA
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir navegacion">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item d-flex align-items-center me-lg-2 mb-2 mb-lg-0">
                    <button type="button" class="theme-toggle theme-toggle-navbar" data-theme-toggle aria-label="Cambiar tema" aria-pressed="false">
                        <i class="bi bi-moon-stars-fill d-none" data-theme-icon="dark"></i>
                        <i class="bi bi-sun-fill" data-theme-icon="light"></i>
                        <span data-theme-label>Tema: Calido</span>
                    </button>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item d-flex align-items-center me-lg-2 mb-2 mb-lg-0">
                        <span class="user-chip">
                            <span class="dot"></span>
                            <?php echo htmlspecialchars(Auth::getUserName()); ?>
                        </span>
                    </li>
                    <?php if ($_SESSION['user_role'] === 'profesor'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/profesor/cursos'); ?>">
                                <i class="bi bi-journal-bookmark-fill"></i> Mis Cursos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/profesor/estudiantes'); ?>">
                                <i class="bi bi-people"></i> Estudiantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/profesor/recursos'); ?>">
                                <i class="bi bi-images"></i> Recursos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/profesor/calificaciones'); ?>">
                                <i class="bi bi-check2-square"></i> Calificaciones
                            </a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'estudiante'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/estudiante'); ?>">
                                <i class="bi bi-person-badge"></i> Mis Cursos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/estudiante/recursos'); ?>">
                                <i class="bi bi-compass"></i> Recursos utiles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/estudiante/progreso'); ?>">
                                <i class="bi bi-graph-up-arrow"></i> Progreso
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/estudiante/calificaciones'); ?>">
                                <i class="bi bi-award"></i> Calificaciones
                            </a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/admin'); ?>">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/admin/usuarios'); ?>">
                                <i class="bi bi-people-fill"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/admin/profesores'); ?>">
                                <i class="bi bi-person-workspace"></i> Profesores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/admin/cursos'); ?>">
                                <i class="bi bi-book-fill"></i> Cursos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/admin/tickets'); ?>">
                                <i class="bi bi-life-preserver"></i> Tickets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('/admin/actividad'); ?>">
                                <i class="bi bi-activity"></i> Actividad
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <form method="POST" action="<?php echo url('/logout'); ?>" class="d-inline">
                            <?php echo csrf_input(); ?>
                            <button type="submit" class="nav-link btn btn-link border-0 p-0">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesion
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('/login'); ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar sesion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('/register'); ?>">
                            <i class="bi bi-person-plus"></i> Registrarse
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main id="main-content" class="app-shell">
