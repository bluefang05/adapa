<?php
// Configuracion de la aplicacion

// Permite override explicito desde entorno si el hosting lo requiere
$envBaseUrl = getenv('APP_BASE_URL');
if ($envBaseUrl !== false && $envBaseUrl !== '') {
    $baseUrl = '/' . trim($envBaseUrl, '/');
    if ($baseUrl === '/') {
        $baseUrl = '';
    }
} else {
    // Deteccion automatica de la URL base desde el script actual
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $baseUrl = ($scriptName === '/' || $scriptName === '.') ? '' : rtrim($scriptName, '/');
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
    if (BASE_URL !== '' && strpos($path, BASE_URL) === 0) {
        return $path;
    }

    // Asegurarse de que la ruta empiece con /
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }

    return BASE_URL . $path;
}

/**
 * Redirige a una URL especifica
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

    if (BASE_URL !== '' && strpos($request_uri, BASE_URL) === 0) {
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

function app_supported_languages() {
    return [
        'espanol' => 'Espanol',
        'ingles' => 'Ingles',
        'frances' => 'Frances',
        'aleman' => 'Aleman',
        'italiano' => 'Italiano',
        'portugues' => 'Portugues',
        'neerlandes' => 'Neerlandes',
        'ruso' => 'Ruso',
        'chino' => 'Chino',
        'japones' => 'Japones',
    ];
}

function app_course_target_languages() {
    return app_supported_languages();
}

function app_interface_languages() {
    return [
        'espanol' => 'Espanol',
        'ingles' => 'Ingles',
    ];
}

function app_language_label($languageKey, $fallback = 'Sin definir') {
    $languages = app_supported_languages();
    return $languages[$languageKey] ?? $fallback;
}

function app_tts_language_map() {
    return [
        'espanol' => 'es-ES',
        'ingles' => 'en-US',
        'frances' => 'fr-FR',
        'aleman' => 'de-DE',
        'italiano' => 'it-IT',
        'portugues' => 'pt-BR',
        'chino' => 'zh-CN',
        'japones' => 'ja-JP',
    ];
}

function sanitize_rich_html($html) {
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    $clean = preg_replace('#<\s*(script|style|iframe|object|embed|link|meta)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
    $clean = preg_replace('/\son[a-z0-9_-]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $clean);
    $clean = preg_replace('/\sstyle\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $clean);
    $clean = preg_replace('/\s(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2/is', '', $clean);

    return strip_tags(
        $clean,
        '<p><br><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre><a>'
    );
}

function app_is_absolute_url($value) {
    return is_string($value) && preg_match('#^https?://#i', $value) === 1;
}

function app_media_public_url($value) {
    $value = (string) $value;
    if ($value === '') {
        return '';
    }

    if (app_is_absolute_url($value)) {
        return $value;
    }

    return url('/' . ltrim($value, '/'));
}

function app_extract_youtube_video_id($url) {
    if (!app_is_absolute_url($url)) {
        return null;
    }

    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    $path = trim((string) ($parts['path'] ?? ''), '/');

    if (in_array($host, ['youtu.be'], true) && $path !== '') {
        return preg_match('/^[A-Za-z0-9_-]{6,15}$/', $path) ? $path : null;
    }

    if (strpos($host, 'youtube.com') !== false) {
        if ($path === 'watch') {
            parse_str($parts['query'] ?? '', $query);
            $videoId = $query['v'] ?? '';
            return preg_match('/^[A-Za-z0-9_-]{6,15}$/', $videoId) ? $videoId : null;
        }

        if (preg_match('#^(embed|shorts)/([A-Za-z0-9_-]{6,15})$#', $path, $matches)) {
            return $matches[2];
        }
    }

    return null;
}

function app_youtube_embed_url($url) {
    $videoId = app_extract_youtube_video_id($url);
    if (!$videoId) {
        return null;
    }

    return 'https://www.youtube-nocookie.com/embed/' . $videoId;
}

function app_media_embed_frame_class($url, $metadata = null) {
    $classes = ['media-embed-frame'];
    $resolvedUrl = (string) $url;
    $resolvedMetadata = app_media_metadata($metadata);

    if (!empty($resolvedMetadata['layout']) && $resolvedMetadata['layout'] === 'vertical') {
        $classes[] = 'is-vertical';
    }

    if (stripos($resolvedUrl, '/shorts/') !== false) {
        $classes[] = 'is-vertical';
    }

    return implode(' ', array_unique($classes));
}

function app_media_metadata($metadata) {
    if (is_array($metadata)) {
        return $metadata;
    }

    if (!is_string($metadata) || trim($metadata) === '') {
        return [];
    }

    $decoded = json_decode($metadata, true);
    return is_array($decoded) ? $decoded : [];
}

function app_useful_resource_category_labels() {
    return [
        'pronunciacion' => 'Pronunciacion',
        'diccionario' => 'Diccionario',
        'gramatica' => 'Gramatica',
        'conjugacion' => 'Conjugacion',
        'escucha' => 'Escucha',
        'apoyo' => 'Apoyo',
    ];
}

function app_useful_resources_catalog() {
    return [
        [
            'id' => 'youglish',
            'title' => 'YouGlish',
            'url' => 'https://youglish.com/',
            'description' => 'Escucha palabras y frases reales dentro de videos para fijar pronunciacion, ritmo y entonacion.',
            'category' => 'pronunciacion',
            'languages' => ['ingles', 'frances', 'aleman', 'italiano', 'japones'],
            'badge' => 'Contexto real',
        ],
        [
            'id' => 'forvo',
            'title' => 'Forvo',
            'url' => 'https://forvo.com/',
            'description' => 'Consulta pronunciacion grabada por hablantes nativos cuando una palabra todavia no te suena natural.',
            'category' => 'pronunciacion',
            'languages' => ['ingles', 'frances', 'aleman', 'italiano', 'japones', 'espanol'],
            'badge' => 'Nativos',
        ],
        [
            'id' => 'wordreference',
            'title' => 'WordReference',
            'url' => 'https://www.wordreference.com/',
            'description' => 'Diccionario rapido con ejemplos, matices y foros utiles para dudas frecuentes de uso.',
            'category' => 'diccionario',
            'languages' => ['ingles', 'frances', 'italiano', 'espanol'],
            'badge' => 'Ejemplos',
        ],
        [
            'id' => 'cambridge',
            'title' => 'Cambridge Dictionary',
            'url' => 'https://dictionary.cambridge.org/',
            'description' => 'Ideal para ingles: definiciones claras, audio y ejemplos muy naturales.',
            'category' => 'diccionario',
            'languages' => ['ingles'],
            'badge' => 'Ingles',
        ],
        [
            'id' => 'larousse-fr',
            'title' => 'Larousse Francais',
            'url' => 'https://www.larousse.fr/dictionnaires/francais',
            'description' => 'Buen apoyo para frances cuando quieres afinar significado, uso y registro.',
            'category' => 'diccionario',
            'languages' => ['frances'],
            'badge' => 'Frances',
        ],
        [
            'id' => 'reverso-conjugator-fr',
            'title' => 'Reverso Conjugator',
            'url' => 'https://conjugator.reverso.net/conjugation-french.html',
            'description' => 'Sirve para revisar conjugaciones rapido y confirmar tiempos verbales en frances.',
            'category' => 'conjugacion',
            'languages' => ['frances'],
            'badge' => 'Conjugacion',
        ],
        [
            'id' => 'pons',
            'title' => 'PONS',
            'url' => 'https://www.pons.com/',
            'description' => 'Diccionario y apoyo lexical bastante util para aleman e idiomas europeos.',
            'category' => 'diccionario',
            'languages' => ['aleman', 'ingles', 'frances', 'italiano'],
            'badge' => 'Aleman',
        ],
        [
            'id' => 'dw-german',
            'title' => 'DW Learn German',
            'url' => 'https://learngerman.dw.com/',
            'description' => 'Refuerzo externo muy bueno para escucha, estructuras y progresion en aleman.',
            'category' => 'escucha',
            'languages' => ['aleman'],
            'badge' => 'Escucha',
        ],
        [
            'id' => 'jisho',
            'title' => 'Jisho',
            'url' => 'https://jisho.org/',
            'description' => 'Buscador muy practico para japones: kanji, vocabulario, lecturas y ejemplos.',
            'category' => 'diccionario',
            'languages' => ['japones'],
            'badge' => 'Japones',
        ],
        [
            'id' => 'nhk-japanese',
            'title' => 'NHK World Easy Japanese',
            'url' => 'https://www.nhk.or.jp/lesson/english/',
            'description' => 'Lecciones cortas y claras para japones con audio y situaciones guiadas.',
            'category' => 'apoyo',
            'languages' => ['japones'],
            'badge' => 'Guia',
        ],
    ];
}

function app_useful_resources_for_language($languageKey = null, $limit = null) {
    $languageKey = $languageKey ? strtolower((string) $languageKey) : null;
    $catalog = array_values(array_filter(app_useful_resources_catalog(), function ($resource) use ($languageKey) {
        if (!$languageKey) {
            return true;
        }

        return in_array($languageKey, $resource['languages'], true);
    }));

    usort($catalog, function ($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    if ($limit !== null) {
        return array_slice($catalog, 0, max(0, (int) $limit));
    }

    return $catalog;
}

function app_group_useful_resources_by_category($resources) {
    $groups = [];
    foreach ($resources as $resource) {
        $category = $resource['category'] ?? 'apoyo';
        if (!isset($groups[$category])) {
            $groups[$category] = [];
        }
        $groups[$category][] = $resource;
    }

    return $groups;
}
