<?php

require_once __DIR__ . '/../../core/Controller.php';

class ThemeController extends Controller {
    public function store() {
        $this->requirePost();
        require_csrf();

        $theme = strtolower(trim($_POST['theme'] ?? 'warm'));
        if ($theme === 'light') {
            $theme = 'warm';
        }

        if (!in_array($theme, ['warm', 'paper', 'sky', 'dark'], true)) {
            $this->json(['ok' => false, 'message' => 'Invalid theme'], 422);
        }

        $_SESSION['theme_preference'] = $theme;
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie('adapa-theme', $theme, [
            'expires' => time() + 31536000,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => $isHttps,
            'httponly' => false,
        ]);

        $this->json(['ok' => true, 'theme' => $theme]);
    }
}
