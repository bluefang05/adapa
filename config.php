<?php
// Configuración de la aplicación

// Detección automática de la URL base
// Esto permite que la aplicación funcione tanto en la raíz como en subdirectorios (ej: /adapa)
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = ($scriptName === '/') ? '' : $scriptName;

// Ajuste manual para producción si la detección automática falla
if ($_SERVER['SERVER_NAME'] === 'aspierd.com') {
    $baseUrl = '/adapa';
}

define('BASE_URL', $baseUrl);

/**
 * Genera una URL absoluta basada en la ruta relativa
 * 
 * @param string $path Ruta relativa (ej: '/login', '/profesor/cursos')
 * @return string URL completa (ej: '/adapa/login')
 */
function url($path = '') {
    if (empty($path)) {
        return BASE_URL;
    }
    
    // Si la ruta ya empieza con BASE_URL, devolverla tal cual
    if (strpos($path, BASE_URL) === 0) {
        return $path;
    }
    
    // Asegurarse de que la ruta empiece con /
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    return BASE_URL . $path;
}

/**
 * Redirige a una URL específica
 * 
 * @param string $path Ruta relativa
 */
function redirect($path = '') {
    $url = url($path);
    header('Location: ' . $url);
    exit;
}

/**
 * Obtiene la ruta actual sin BASE_URL
 * 
 * @return string Ruta actual relativa
 */
function current_path() {
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $base_url_length = strlen(BASE_URL);
    
    if (strpos($request_uri, BASE_URL) === 0) {
        return substr($request_uri, $base_url_length);
    }
    
    return $request_uri;
}

function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_token_from_request() {
    return $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
}

function verify_csrf_token($token = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    $token = $token ?? csrf_token_from_request();

    return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function require_csrf() {
    if (!verify_csrf_token()) {
        http_response_code(419);
        echo 'CSRF token mismatch';
        exit;
    }
}
