<?php

require_once __DIR__ . '/../../core/Controller.php';

class ThemeController extends Controller {
    public function store() {
        $this->requirePost();
        require_csrf();

        $theme = strtolower(trim($_POST['theme'] ?? ''));
        if (!in_array($theme, ['light', 'dark'], true)) {
            $this->json(['ok' => false, 'message' => 'Invalid theme'], 422);
        }

        $_SESSION['theme_preference'] = $theme;
        setcookie('adapa-theme', $theme, [
            'expires' => time() + 31536000,
            'path' => '/',
            'samesite' => 'Lax',
        ]);

        $this->json(['ok' => true, 'theme' => $theme]);
    }
}
