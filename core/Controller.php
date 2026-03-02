<?php

abstract class Controller {

    protected function view($viewPath, $data = array()) {
        $filePath = str_replace(".", "/", $viewPath);
        $viewFile = __DIR__ . "/../views/" . $filePath . ".php";

        extract($data);

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("View file not found: " . $viewFile);
        }
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        $target = $url;

        if (
            function_exists('url') &&
            !preg_match('/^https?:\/\//i', (string) $url)
        ) {
            $target = url($url);
        }

        header("Location: $target");
        exit;
    }

    protected function flash($key, $message) {
        $_SESSION[$key] = $message;
    }

    protected function abort($statusCode, $message) {
        http_response_code($statusCode);
        echo $message;
        exit;
    }

    protected function requirePost() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->abort(405, 'Method Not Allowed');
        }
    }

    protected function requireRole($roles, $redirectTo = '/login') {
        $roles = (array) $roles;

        if (!class_exists('Auth') || !Auth::isLoggedIn()) {
            $this->redirect($redirectTo);
        }

        foreach ($roles as $role) {
            if (Auth::hasRole($role)) {
                return;
            }
        }

        $this->redirect($redirectTo);
    }
}
