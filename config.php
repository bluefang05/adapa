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

function app_url_host_label($url) {
    if (!app_is_absolute_url($url)) {
        return 'ADAPA';
    }

    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
    $host = preg_replace('/^www\./', '', $host);

    $labels = [
        'youglish.com' => 'YouGlish',
        'forvo.com' => 'Forvo',
        'wordreference.com' => 'WordReference',
        'dictionary.cambridge.org' => 'Cambridge Dictionary',
        'larousse.fr' => 'Larousse',
        'conjugator.reverso.net' => 'Reverso Conjugator',
        'pons.com' => 'PONS',
        'learngerman.dw.com' => 'DW Learn German',
        'jisho.org' => 'Jisho',
        'nhk.or.jp' => 'NHK',
    ];

    foreach ($labels as $domain => $label) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            return $label;
        }
    }

    return $host !== '' ? $host : 'Fuente externa';
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

function app_media_resource_kind($url, $type = null) {
    $resolvedType = strtolower(trim((string) $type));
    if ($resolvedType !== '') {
        if (strpos($resolvedType, 'image') !== false) {
            return 'image';
        }
        if (strpos($resolvedType, 'audio') !== false) {
            return 'audio';
        }
        if (strpos($resolvedType, 'video') !== false) {
            return 'video';
        }
        if (in_array($resolvedType, ['image', 'audio', 'video', 'pdf'], true)) {
            return $resolvedType;
        }
    }

    $resolvedUrl = strtolower((string) $url);
    if ($resolvedUrl === '') {
        return 'link';
    }

    if (app_extract_youtube_video_id($resolvedUrl)) {
        return 'video';
    }

    $path = parse_url($resolvedUrl, PHP_URL_PATH) ?: $resolvedUrl;
    if (preg_match('/\.(png|jpe?g|gif|webp|avif)$/i', $path)) {
        return 'image';
    }
    if (preg_match('/\.(mp3|wav|ogg|m4a)$/i', $path)) {
        return 'audio';
    }
    if (preg_match('/\.(mp4|webm|mov|m4v)$/i', $path)) {
        return 'video';
    }
    if (preg_match('/\.pdf$/i', $path)) {
        return 'pdf';
    }

    return 'link';
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

function app_activity_support_resource($content) {
    if (is_string($content)) {
        $decoded = json_decode($content, true);
        $content = is_array($decoded) ? $decoded : [];
    } elseif (is_object($content)) {
        $content = (array) $content;
    }

    if (!is_array($content)) {
        return null;
    }

    $url = trim((string) ($content['recurso_apoyo_url'] ?? ''));
    if ($url === '') {
        return null;
    }

    $title = trim((string) ($content['recurso_apoyo_titulo'] ?? 'Recurso de apoyo'));
    $type = trim((string) ($content['recurso_apoyo_tipo'] ?? ''));
    $kind = app_media_resource_kind($url, $type);
    $embedUrl = $kind === 'video' ? app_youtube_embed_url($url) : null;

    return [
        'media_id' => $content['recurso_apoyo_media_id'] ?? null,
        'title' => $title,
        'url' => $url,
        'type' => $type,
        'kind' => $kind,
        'embed_url' => $embedUrl,
        'frame_class' => $embedUrl ? app_media_embed_frame_class($url) : null,
    ];
}

function app_media_external_video_policy() {
    return [
        'provider' => 'YouTube',
        'summary' => 'Para video externo, la politica oficial del producto es trabajar solo con enlaces normales de YouTube. La app resuelve el embed automaticamente y mantiene una experiencia mas estable.',
        'workflow' => [
            'Pega la URL normal de YouTube en la biblioteca.',
            'La app genera el embed y lo deja listo para reutilizar.',
            'Inserta ese recurso en teoria o apoyo de actividad sin pegar iframes.',
        ],
        'accepted_formats' => [
            'https://www.youtube.com/watch?v=...',
            'https://www.youtube.com/shorts/...',
            'https://youtu.be/...',
            'https://www.youtube.com/embed/...',
        ],
        'rejected_examples' => [
            'Instagram Reels',
            'TikTok',
            'iframes pegados manualmente',
        ],
    ];
}

function app_media_source_profile($url, $metadata = null) {
    $resolvedUrl = trim((string) $url);
    $resolvedMetadata = app_media_metadata($metadata);

    if ($resolvedUrl !== '' && app_extract_youtube_video_id($resolvedUrl)) {
        return [
            'label' => 'YouTube',
            'detail' => 'Embed oficial',
            'icon' => 'bi-youtube',
            'tone' => 'badge-accent',
        ];
    }

    if ($resolvedUrl !== '' && app_is_absolute_url($resolvedUrl)) {
        return [
            'label' => 'Enlace externo',
            'detail' => 'Se abre fuera de la app',
            'icon' => 'bi-link-45deg',
            'tone' => 'info',
        ];
    }

    if (!empty($resolvedMetadata['storage']) && $resolvedMetadata['storage'] === 'external') {
        return [
            'label' => 'Externo',
            'detail' => 'Recurso remoto',
            'icon' => 'bi-cloud-arrow-down',
            'tone' => 'info',
        ];
    }

    return [
        'label' => 'Archivo propio',
        'detail' => 'Subido a la biblioteca',
        'icon' => 'bi-cloud-arrow-up',
        'tone' => 'success',
    ];
}

function app_media_source_key($url, $metadata = null) {
    $resolvedUrl = trim((string) $url);
    $resolvedMetadata = app_media_metadata($metadata);

    if ($resolvedUrl !== '' && app_extract_youtube_video_id($resolvedUrl)) {
        return 'youtube';
    }

    if ($resolvedUrl !== '' && app_is_absolute_url($resolvedUrl)) {
        return 'external';
    }

    if (!empty($resolvedMetadata['storage']) && $resolvedMetadata['storage'] === 'external') {
        return 'external';
    }

    return 'uploaded';
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

function app_useful_resource_category_icon($category) {
    $icons = [
        'pronunciacion' => 'bi-mic',
        'diccionario' => 'bi-book',
        'gramatica' => 'bi-diagram-3',
        'conjugacion' => 'bi-arrow-repeat',
        'escucha' => 'bi-headphones',
        'apoyo' => 'bi-compass',
    ];

    return $icons[$category] ?? 'bi-stars';
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
            'best_for' => 'Oir una palabra dentro de frases naturales antes de repetirla.',
            'cta_label' => 'Abrir YouGlish',
            'priority' => 10,
        ],
        [
            'id' => 'forvo',
            'title' => 'Forvo',
            'url' => 'https://forvo.com/',
            'description' => 'Consulta pronunciacion grabada por hablantes nativos cuando una palabra todavia no te suena natural.',
            'category' => 'pronunciacion',
            'languages' => ['ingles', 'frances', 'aleman', 'italiano', 'japones', 'espanol'],
            'badge' => 'Nativos',
            'best_for' => 'Confirmar la pronunciacion aislada de una palabra concreta.',
            'cta_label' => 'Abrir Forvo',
            'priority' => 30,
        ],
        [
            'id' => 'wordreference',
            'title' => 'WordReference',
            'url' => 'https://www.wordreference.com/',
            'description' => 'Diccionario rapido con ejemplos, matices y foros utiles para dudas frecuentes de uso.',
            'category' => 'diccionario',
            'languages' => ['ingles', 'frances', 'italiano', 'espanol'],
            'badge' => 'Ejemplos',
            'best_for' => 'Aclarar matices de uso y salir de una duda puntual sin perder tiempo.',
            'cta_label' => 'Abrir WordReference',
            'priority' => 20,
        ],
        [
            'id' => 'cambridge',
            'title' => 'Cambridge Dictionary',
            'url' => 'https://dictionary.cambridge.org/',
            'description' => 'Ideal para ingles: definiciones claras, audio y ejemplos muy naturales.',
            'category' => 'diccionario',
            'languages' => ['ingles'],
            'badge' => 'Ingles',
            'best_for' => 'Fijar definicion, audio y ejemplo natural en ingles.',
            'cta_label' => 'Abrir Cambridge',
            'priority' => 35,
        ],
        [
            'id' => 'larousse-fr',
            'title' => 'Larousse Francais',
            'url' => 'https://www.larousse.fr/dictionnaires/francais',
            'description' => 'Buen apoyo para frances cuando quieres afinar significado, uso y registro.',
            'category' => 'diccionario',
            'languages' => ['frances'],
            'badge' => 'Frances',
            'best_for' => 'Refinar significado y registro cuando una palabra cambia segun contexto.',
            'cta_label' => 'Abrir Larousse',
            'priority' => 35,
        ],
        [
            'id' => 'reverso-conjugator-fr',
            'title' => 'Reverso Conjugator',
            'url' => 'https://conjugator.reverso.net/conjugation-french.html',
            'description' => 'Sirve para revisar conjugaciones rapido y confirmar tiempos verbales en frances.',
            'category' => 'conjugacion',
            'languages' => ['frances'],
            'badge' => 'Conjugacion',
            'best_for' => 'Comprobar rapidamente un verbo antes de escribir o hablar.',
            'cta_label' => 'Abrir conjugador',
            'priority' => 45,
        ],
        [
            'id' => 'pons',
            'title' => 'PONS',
            'url' => 'https://www.pons.com/',
            'description' => 'Diccionario y apoyo lexical bastante util para aleman e idiomas europeos.',
            'category' => 'diccionario',
            'languages' => ['aleman', 'ingles', 'frances', 'italiano'],
            'badge' => 'Aleman',
            'best_for' => 'Comparar vocabulario rapido en aleman y otras rutas europeas.',
            'cta_label' => 'Abrir PONS',
            'priority' => 40,
        ],
        [
            'id' => 'dw-german',
            'title' => 'DW Learn German',
            'url' => 'https://learngerman.dw.com/',
            'description' => 'Refuerzo externo muy bueno para escucha, estructuras y progresion en aleman.',
            'category' => 'escucha',
            'languages' => ['aleman'],
            'badge' => 'Escucha',
            'best_for' => 'Refuerzo guiado cuando necesitas escuchar y repetir con mas contexto.',
            'cta_label' => 'Abrir DW',
            'priority' => 55,
        ],
        [
            'id' => 'jisho',
            'title' => 'Jisho',
            'url' => 'https://jisho.org/',
            'description' => 'Buscador muy practico para japones: kanji, vocabulario, lecturas y ejemplos.',
            'category' => 'diccionario',
            'languages' => ['japones'],
            'badge' => 'Japones',
            'best_for' => 'Buscar kanji, lectura y ejemplo sin romper el ritmo de estudio.',
            'cta_label' => 'Abrir Jisho',
            'priority' => 40,
        ],
        [
            'id' => 'nhk-japanese',
            'title' => 'NHK World Easy Japanese',
            'url' => 'https://www.nhk.or.jp/lesson/english/',
            'description' => 'Lecciones cortas y claras para japones con audio y situaciones guiadas.',
            'category' => 'apoyo',
            'languages' => ['japones'],
            'badge' => 'Guia',
            'best_for' => 'Refuerzo corto con audio cuando quieres una explicacion mas guiada.',
            'cta_label' => 'Abrir NHK',
            'priority' => 60,
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
        $priorityA = (int) ($a['priority'] ?? 999);
        $priorityB = (int) ($b['priority'] ?? 999);

        if ($priorityA !== $priorityB) {
            return $priorityA <=> $priorityB;
        }

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

function app_course_editorial_states() {
    return [
        'borrador' => [
            'label' => 'Borrador',
            'tone' => 'warning',
            'description' => 'Todavia se esta armando. Mantiene el curso fuera de vista publica.',
        ],
        'en_revision' => [
            'label' => 'En revision',
            'tone' => 'info',
            'description' => 'La base ya existe, pero aun necesita una pasada editorial antes de quedar listo.',
        ],
        'publicable' => [
            'label' => 'Publicable',
            'tone' => 'accent',
            'description' => 'El contenido ya esta listo para abrirse cuando decidas darle visibilidad.',
        ],
        'publicado' => [
            'label' => 'Publicado',
            'tone' => 'success',
            'description' => 'Curso listo para operar. Si ademas esta visible, el alumno ya lo puede explorar.',
        ],
        'archivado' => [
            'label' => 'Archivado',
            'tone' => 'secondary',
            'description' => 'Se conserva como referencia, pero queda fuera del flujo activo.',
        ],
    ];
}

function app_lesson_editorial_states() {
    return [
        'borrador' => [
            'label' => 'Borrador',
            'tone' => 'warning',
            'description' => 'La leccion sigue interna y todavia no deberia considerarse lista.',
        ],
        'en_revision' => [
            'label' => 'En revision',
            'tone' => 'info',
            'description' => 'La estructura ya existe, pero aun conviene revisar teoria, practica y copy.',
        ],
        'publicable' => [
            'label' => 'Publicable',
            'tone' => 'accent',
            'description' => 'Tiene base suficiente y solo falta decidir el momento de abrirla.',
        ],
        'publicado' => [
            'label' => 'Publicado',
            'tone' => 'success',
            'description' => 'Leccion lista para operar como parte visible del curso.',
        ],
        'archivado' => [
            'label' => 'Archivado',
            'tone' => 'secondary',
            'description' => 'Se conserva, pero ya no forma parte del flujo normal.',
        ],
    ];
}

function app_normalize_editorial_state($value, $type = 'course') {
    $catalog = $type === 'lesson' ? app_lesson_editorial_states() : app_course_editorial_states();
    $value = strtolower(trim((string) $value));

    if (isset($catalog[$value])) {
        return $value;
    }

    return 'borrador';
}

function app_course_editorial_state_value($curso) {
    $explicit = strtolower(trim((string) ($curso->estado_editorial ?? '')));
    if (isset(app_course_editorial_states()[$explicit])) {
        return $explicit;
    }

    $legacyState = trim((string) ($curso->estado ?? 'preparacion'));
    $isPublic = (int) ($curso->es_publico ?? 0) === 1;

    if ($legacyState === 'archivado') {
        return 'archivado';
    }

    if ($legacyState === 'activo' && $isPublic) {
        return 'publicado';
    }

    if ($legacyState === 'activo') {
        return 'publicable';
    }

    if (in_array($legacyState, ['pausado', 'finalizado'], true)) {
        return 'en_revision';
    }

    return 'borrador';
}

function app_lesson_editorial_state_value($leccion) {
    $explicit = strtolower(trim((string) ($leccion->estado_editorial ?? '')));
    if (isset(app_lesson_editorial_states()[$explicit])) {
        return $explicit;
    }

    $legacyState = trim((string) ($leccion->estado ?? 'borrador'));

    if ($legacyState === 'archivada') {
        return 'archivado';
    }

    if ($legacyState === 'publicada') {
        return 'publicado';
    }

    return 'borrador';
}

function app_course_editorial_state_meta($curso) {
    $state = app_course_editorial_state_value($curso);
    $meta = app_course_editorial_states()[$state];
    $meta['state'] = $state;

    if ($state === 'publicado' && (int) ($curso->es_publico ?? 0) !== 1) {
        $meta['description'] = 'Curso editorialmente listo, pero todavia privado o dependiente de acceso restringido.';
    }

    return $meta;
}

function app_lesson_editorial_state_meta($leccion) {
    $state = app_lesson_editorial_state_value($leccion);
    $meta = app_lesson_editorial_states()[$state];
    $meta['state'] = $state;
    return $meta;
}

function app_course_editorial_snapshot($curso) {
    $workflow = app_course_editorial_state_meta($curso);
    $courseId = (int) ($curso->id ?? 0);
    $totalLessons = (int) ($curso->total_lecciones ?? 0);
    $totalActivities = (int) ($curso->total_actividades ?? 0);
    $isPublic = (int) ($curso->es_publico ?? 0) === 1;
    $estado = trim((string) ($curso->estado ?? 'preparacion'));

    if ($workflow['state'] === 'archivado') {
        return [
            'label' => 'Archivado',
            'tone' => 'secondary',
            'hint' => $workflow['description'],
            'progress' => 100,
            'action_label' => 'Revisar curso',
            'action_url' => $courseId > 0 ? url('/profesor/cursos/edit/' . $courseId) : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($totalLessons === 0) {
        return [
            'label' => 'En configuracion',
            'tone' => 'warning',
            'hint' => 'Crea la primera leccion para que este curso tenga recorrido real.',
            'progress' => 15,
            'action_label' => 'Crear primera leccion',
            'action_url' => $courseId > 0 ? url('/profesor/cursos/' . $courseId . '/lecciones/create') : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($totalActivities === 0) {
        return [
            'label' => 'En construccion',
            'tone' => 'info',
            'hint' => 'La estructura ya existe. Ahora falta convertirla en practica.',
            'progress' => 55,
            'action_label' => 'Entrar al constructor',
            'action_url' => $courseId > 0 ? url('/profesor/cursos/' . $courseId . '/lecciones') : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($isPublic && $estado === 'activo') {
        return [
            'label' => 'Publicado',
            'tone' => 'success',
            'hint' => 'Visible y con base suficiente para operar con alumnos.',
            'progress' => 100,
            'action_label' => 'Ver lecciones',
            'action_url' => $courseId > 0 ? url('/profesor/cursos/' . $courseId . '/lecciones') : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($isPublic) {
        return [
            'label' => 'Visible con ajustes',
            'tone' => 'accent',
            'hint' => 'Ya se muestra, pero aun conviene revisar calidad antes de empujarlo mas.',
            'progress' => 88,
            'action_label' => 'Revisar curso',
            'action_url' => $courseId > 0 ? url('/profesor/cursos/edit/' . $courseId) : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    return [
        'label' => 'Listo para revisar',
        'tone' => 'accent',
        'hint' => 'Tiene base real. Solo falta validacion editorial final.',
        'progress' => 82,
        'action_label' => 'Revisar curso',
        'action_url' => $courseId > 0 ? url('/profesor/cursos/edit/' . $courseId) : url('/profesor/cursos'),
        'workflow_state' => $workflow['state'],
        'workflow' => $workflow,
    ];
}

function app_course_readiness_summary($curso) {
    $snapshot = app_course_editorial_snapshot($curso);
    return [
        'label' => $snapshot['label'],
        'progress' => $snapshot['progress'],
    ];
}

function app_course_production_hint($curso) {
    return app_course_editorial_snapshot($curso)['hint'];
}

function app_lesson_editorial_snapshot($leccion) {
    $workflow = app_lesson_editorial_state_meta($leccion);
    $lessonId = (int) ($leccion->id ?? 0);
    $totalTheories = (int) ($leccion->total_teorias ?? 0);
    $totalActivities = (int) ($leccion->total_actividades ?? 0);
    $estado = trim((string) ($leccion->estado ?? 'borrador'));

    if ($workflow['state'] === 'archivado') {
        return [
            'label' => 'Archivada',
            'tone' => 'secondary',
            'hint' => $workflow['description'],
            'progress' => 100,
            'action_label' => 'Editar ficha',
            'action_url' => $lessonId > 0 ? url('/profesor/lecciones/edit/' . $lessonId) : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($totalTheories === 0) {
        return [
            'label' => 'Sin contexto',
            'tone' => 'warning',
            'hint' => 'Empieza por una teoria clara para darle piso a la leccion.',
            'progress' => 20,
            'action_label' => 'Crear teoria',
            'action_url' => $lessonId > 0 ? url('/profesor/lecciones/' . $lessonId . '/teoria/create') : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($totalActivities === 0) {
        return [
            'label' => 'Sin practica',
            'tone' => 'info',
            'hint' => 'La explicacion ya existe. Ahora conviertela en practica medible.',
            'progress' => 60,
            'action_label' => 'Crear actividad',
            'action_url' => $lessonId > 0 ? url('/profesor/lecciones/' . $lessonId . '/actividades/create') : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    if ($estado === 'publicada') {
        return [
            'label' => 'Publicada',
            'tone' => 'success',
            'hint' => 'La leccion ya esta visible y con piezas minimas completas.',
            'progress' => 100,
            'action_label' => 'Ver preview',
            'action_url' => $lessonId > 0 ? url('/profesor/lecciones/' . $lessonId . '/preview') : url('/profesor/cursos'),
            'workflow_state' => $workflow['state'],
            'workflow' => $workflow,
        ];
    }

    return [
        'label' => 'Lista para revisar',
        'tone' => 'accent',
        'hint' => 'La base ya esta. Revisa orden, copy y estado final antes de publicarla.',
        'progress' => 85,
        'action_label' => 'Abrir constructor',
        'action_url' => $lessonId > 0 ? url('/profesor/lecciones/' . $lessonId . '/builder') : url('/profesor/cursos'),
        'workflow_state' => $workflow['state'],
        'workflow' => $workflow,
    ];
}

function app_lesson_readiness_summary($leccion) {
    $snapshot = app_lesson_editorial_snapshot($leccion);
    return [
        'label' => $snapshot['label'],
        'tone' => $snapshot['tone'],
        'progress' => $snapshot['progress'],
        'message' => $snapshot['hint'],
        'action_label' => $snapshot['action_label'],
        'action_url' => $snapshot['action_url'],
    ];
}
