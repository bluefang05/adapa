<?php

declare(strict_types=1);

$baseUrl = rtrim($argv[1] ?? 'http://localhost/adapa', '/');
$cookieFile = tempnam(sys_get_temp_dir(), 'adapa-smoke-');

if ($cookieFile === false) {
    fwrite(STDERR, "Could not create cookie jar.\n");
    exit(1);
}

register_shutdown_function(static function () use ($cookieFile): void {
    if (is_file($cookieFile)) {
        @unlink($cookieFile);
    }
});

function httpRequest(string $method, string $url, string $cookieFile, array $postFields = []): array
{
    $ch = curl_init($url);
    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'User-Agent: ADAPA-Smoke/1.0',
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 20,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    }

    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('cURL error: ' . $error);
    }

    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headersRaw = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);
    curl_close($ch);

    return [
        'status' => $status,
        'headers' => $headersRaw,
        'body' => $body,
        'url' => $url,
    ];
}

function assertHealthyResponse(array $response, string $label): void
{
    $status = (int) ($response['status'] ?? 0);
    $body = (string) ($response['body'] ?? '');

    if ($status < 200 || $status >= 400) {
        throw new RuntimeException($label . ' returned HTTP ' . $status);
    }

    if (preg_match('/Fatal error|Parse error|Uncaught|RuntimeException|PDOException/i', $body)) {
        throw new RuntimeException($label . ' returned a PHP fatal marker.');
    }
}

function extractCsrfToken(string $html): ?string
{
    if (preg_match('/name="_csrf"\s+value="([^"]+)"/i', $html, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }

    if (preg_match('/meta\s+name="csrf-token"\s+content="([^"]+)"/i', $html, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }

    return null;
}

function findFirstPath(string $html, string $pattern): ?string
{
    if (preg_match($pattern, $html, $matches)) {
        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }

    return null;
}

function makeAbsoluteUrl(string $baseUrl, string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $basePath = (string) (parse_url($baseUrl, PHP_URL_PATH) ?? '');
    $baseOrigin = preg_replace('#' . preg_quote($basePath, '#') . '$#', '', $baseUrl);

    if ($basePath !== '' && strpos($path, $basePath . '/') === 0) {
        return rtrim((string) $baseOrigin, '/') . $path;
    }

    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function loginViaEnm(string $baseUrl, string $accountKey, string $cookieFile): array
{
    $enmResponse = httpRequest('GET', $baseUrl . '/enm', $cookieFile);
    assertHealthyResponse($enmResponse, 'GET /enm');

    $csrf = extractCsrfToken($enmResponse['body']);
    if (!$csrf) {
        throw new RuntimeException('Could not find CSRF token in /enm.');
    }

    $loginResponse = httpRequest('POST', $baseUrl . '/enm/login', $cookieFile, [
        '_csrf' => $csrf,
        'account_key' => $accountKey,
    ]);
    assertHealthyResponse($loginResponse, 'POST /enm/login (' . $accountKey . ')');

    return $loginResponse;
}

function fetchPath(string $baseUrl, string $cookieFile, string $path, string $label): array
{
    $response = httpRequest('GET', makeAbsoluteUrl($baseUrl, $path), $cookieFile);
    assertHealthyResponse($response, $label);
    return $response;
}

function printOk(string $label, array $response): void
{
    echo '[OK] ' . $label . ' -> HTTP ' . $response['status'] . PHP_EOL;
}

echo "ADAPA Smoke Test\n";
echo "================\n";
echo 'Base URL: ' . $baseUrl . PHP_EOL . PHP_EOL;

$checks = [];

// Admin
loginViaEnm($baseUrl, 'admin', $cookieFile);
$adminPaths = [
    '/admin' => 'Admin dashboard',
    '/admin/cursos' => 'Admin courses',
    '/admin/profesores' => 'Admin teachers',
    '/admin/tickets' => 'Admin tickets',
    '/admin/actividad' => 'Admin activity',
    '/admin/cursos/create' => 'Admin create course',
];

foreach ($adminPaths as $path => $label) {
    $response = fetchPath($baseUrl, $cookieFile, $path, $label);
    $checks[] = [$label, $response];
}

$adminCoursesResponse = fetchPath($baseUrl, $cookieFile, '/admin/cursos', 'Admin courses discovery');
$adminStructurePath = findFirstPath(
    $adminCoursesResponse['body'],
    '#href="([^"]*/admin/cursos/estructura/\d+)"#i'
);
if ($adminStructurePath) {
    $response = fetchPath($baseUrl, $cookieFile, $adminStructurePath, 'Admin course structure');
    $checks[] = ['Admin course structure', $response];
}

// Professor
loginViaEnm($baseUrl, 'profesor', $cookieFile);
$professorPaths = [
    '/profesor/cursos' => 'Professor courses',
    '/profesor/recursos' => 'Professor resources',
];

foreach ($professorPaths as $path => $label) {
    $response = fetchPath($baseUrl, $cookieFile, $path, $label);
    $checks[] = [$label, $response];
}

$profCoursesResponse = fetchPath($baseUrl, $cookieFile, '/profesor/cursos', 'Professor course discovery');
$profLessonsPath = findFirstPath(
    $profCoursesResponse['body'],
    '#href="([^"]*/profesor/cursos/\d+/lecciones)"#i'
);
if ($profLessonsPath) {
    $response = fetchPath($baseUrl, $cookieFile, $profLessonsPath, 'Professor lessons');
    $checks[] = ['Professor lessons', $response];

    $builderPath = findFirstPath($response['body'], '#href="([^"]*/profesor/lecciones/\d+/builder)"#i');
    if ($builderPath) {
        $builderResponse = fetchPath($baseUrl, $cookieFile, $builderPath, 'Professor lesson builder');
        $checks[] = ['Professor lesson builder', $builderResponse];
    }

    $previewPath = findFirstPath($response['body'], '#href="([^"]*/profesor/lecciones/\d+/preview)"#i');
    if ($previewPath) {
        $previewResponse = fetchPath($baseUrl, $cookieFile, $previewPath, 'Professor lesson preview');
        $checks[] = ['Professor lesson preview', $previewResponse];
    }
}

// Student
loginViaEnm($baseUrl, 'estudiante', $cookieFile);
$studentPaths = [
    '/estudiante' => 'Student dashboard',
    '/estudiante/progreso' => 'Student progress',
    '/estudiante/calificaciones' => 'Student grades',
    '/estudiante/recursos' => 'Student resources',
];

foreach ($studentPaths as $path => $label) {
    $response = fetchPath($baseUrl, $cookieFile, $path, $label);
    $checks[] = [$label, $response];
}

$studentDashboardResponse = fetchPath($baseUrl, $cookieFile, '/estudiante', 'Student discovery');
$studentLessonsPath = findFirstPath(
    $studentDashboardResponse['body'],
    '#href="([^"]*/estudiante/cursos/\d+/lecciones)"#i'
);
if ($studentLessonsPath) {
    $lessonsResponse = fetchPath($baseUrl, $cookieFile, $studentLessonsPath, 'Student lessons');
    $checks[] = ['Student lessons', $lessonsResponse];

    $lessonContentPath = findFirstPath(
        $lessonsResponse['body'],
        '#href="([^"]*/estudiante/lecciones/\d+/contenido)"#i'
    );
    if ($lessonContentPath) {
        $contentResponse = fetchPath($baseUrl, $cookieFile, $lessonContentPath, 'Student lesson content');
        $checks[] = ['Student lesson content', $contentResponse];

        $activityPath = findFirstPath(
            $contentResponse['body'],
            '#href="([^"]*/estudiante/actividades/\d+)"#i'
        );
        if ($activityPath) {
            $activityResponse = fetchPath($baseUrl, $cookieFile, $activityPath, 'Student activity');
            $checks[] = ['Student activity', $activityResponse];
        }
    }
}

foreach ($checks as [$label, $response]) {
    printOk($label, $response);
}

echo PHP_EOL . 'Smoke checks passed: ' . count($checks) . PHP_EOL;
