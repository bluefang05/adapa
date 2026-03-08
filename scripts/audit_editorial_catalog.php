<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$remoteConfigPath = $root . '/config/database.hosting.php';

$localConfig = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '',
    'name' => 'adapa',
    'port' => 3306,
    'charset' => 'utf8mb4',
];

$target = 'local';
foreach ($argv as $arg) {
    if ($arg === '--remote') {
        $target = 'remote';
    } elseif ($arg === '--local') {
        $target = 'local';
    }
}

if ($target === 'remote') {
    if (!is_file($remoteConfigPath)) {
        fwrite(STDERR, "Remote config not found.\n");
        exit(1);
    }
    $config = require $remoteConfigPath;
} else {
    $config = $localConfig;
}

if (!is_array($config)) {
    fwrite(STDERR, "Database config is not a valid array.\n");
    exit(1);
}

function connectPdo(array $config): PDO
{
    $host = $config['host'] ?? '127.0.0.1';
    $port = (int) ($config['port'] ?? 3306);
    $db = $config['name'] ?? '';
    $user = $config['user'] ?? '';
    $pass = $config['pass'] ?? '';
    $charset = $config['charset'] ?? 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $db, $charset);

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function fetchRows(PDO $pdo, string $sql): array
{
    return $pdo->query($sql)->fetchAll();
}

$pdo = connectPdo($config);

$report = [
    'meta' => [
        'target' => $target,
        'database' => $config['name'] ?? 'unknown',
        'host' => $config['host'] ?? 'unknown',
        'generated_at' => date('c'),
    ],
    'checks' => [],
];

$checks = [
    'catalog_marked_without_published_lessons' => "
        SELECT
            c.id,
            c.titulo,
            c.es_publico,
            c.estado_editorial,
            COUNT(l.id) AS total_published_lessons
        FROM cursos c
        LEFT JOIN lecciones l
            ON l.curso_id = c.id
           AND COALESCE(NULLIF(l.estado_editorial, ''), 'borrador') = 'publicado'
        WHERE c.es_publico = 1
          AND COALESCE(NULLIF(c.estado_editorial, ''), 'borrador') = 'publicado'
        GROUP BY c.id, c.titulo, c.es_publico, c.estado_editorial
        HAVING total_published_lessons = 0
        ORDER BY c.id ASC
    ",
    'published_without_visible_flag' => "
        SELECT
            c.id,
            c.titulo,
            c.es_publico,
            c.estado_editorial
        FROM cursos c
        WHERE COALESCE(NULLIF(c.estado_editorial, ''), 'borrador') = 'publicado'
          AND c.es_publico = 0
        ORDER BY c.id ASC
    ",
    'student_enrollments_on_hidden_courses' => "
        SELECT
            c.id AS curso_id,
            c.titulo,
            COUNT(i.id) AS total_inscripciones
        FROM cursos c
        INNER JOIN inscripciones i ON i.curso_id = c.id
        WHERE c.es_publico = 0
           OR COALESCE(NULLIF(c.estado_editorial, ''), 'borrador') <> 'publicado'
           OR NOT EXISTS (
                SELECT 1
                FROM lecciones l
                WHERE l.curso_id = c.id
                  AND COALESCE(NULLIF(l.estado_editorial, ''), 'borrador') = 'publicado'
           )
        GROUP BY c.id, c.titulo
        ORDER BY total_inscripciones DESC, c.id ASC
    ",
    'lessons_marked_published_without_theory' => "
        SELECT
            l.id,
            l.titulo,
            l.curso_id,
            COUNT(t.id) AS total_teorias
        FROM lecciones l
        LEFT JOIN teoria t ON t.leccion_id = l.id
        WHERE COALESCE(NULLIF(l.estado_editorial, ''), 'borrador') = 'publicado'
        GROUP BY l.id, l.titulo, l.curso_id
        HAVING total_teorias = 0
        ORDER BY l.id ASC
    ",
    'lessons_marked_published_without_activities' => "
        SELECT
            l.id,
            l.titulo,
            l.curso_id,
            COUNT(a.id) AS total_actividades
        FROM lecciones l
        LEFT JOIN actividades a ON a.leccion_id = l.id
        WHERE COALESCE(NULLIF(l.estado_editorial, ''), 'borrador') = 'publicado'
        GROUP BY l.id, l.titulo, l.curso_id
        HAVING total_actividades = 0
        ORDER BY l.id ASC
    ",
];

foreach ($checks as $key => $sql) {
    $rows = fetchRows($pdo, $sql);
    $report['checks'][$key] = [
        'total' => count($rows),
        'rows' => $rows,
    ];
}

echo "Editorial Catalog Audit\n";
echo "======================\n";
echo 'Target: ' . $report['meta']['target'] . PHP_EOL;
echo 'Database: ' . $report['meta']['database'] . PHP_EOL;
echo 'Host: ' . $report['meta']['host'] . PHP_EOL;
echo 'Generated: ' . $report['meta']['generated_at'] . PHP_EOL . PHP_EOL;

foreach ($report['checks'] as $name => $result) {
    echo strtoupper($name) . ': ' . $result['total'] . PHP_EOL;
    if (!empty($result['rows'])) {
        foreach (array_slice($result['rows'], 0, 5) as $row) {
            echo ' - ' . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }
        if ($result['total'] > 5) {
            echo ' - ... +' . ($result['total'] - 5) . " more\n";
        }
    }
    echo PHP_EOL;
}
