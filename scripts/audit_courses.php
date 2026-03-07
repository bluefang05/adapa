<?php

require_once __DIR__ . '/../config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    DB_HOST,
    defined('DB_PORT') ? DB_PORT : 3306,
    DB_NAME,
    defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
);

$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$courses = $pdo->query("
    SELECT
        c.*,
        u.email AS creator_email,
        (
            SELECT COUNT(*)
            FROM lecciones l
            WHERE l.curso_id = c.id
        ) AS lesson_count,
        (
            SELECT COUNT(*)
            FROM teoria t
            INNER JOIN lecciones l ON l.id = t.leccion_id
            WHERE l.curso_id = c.id
        ) AS theory_count,
        (
            SELECT COUNT(*)
            FROM actividades a
            INNER JOIN lecciones l ON l.id = a.leccion_id
            WHERE l.curso_id = c.id
        ) AS activity_count,
        (
            SELECT COUNT(*)
            FROM inscripciones i
            WHERE i.curso_id = c.id
        ) AS enrollment_count
    FROM cursos c
    LEFT JOIN usuarios u ON u.id = c.creado_por
    ORDER BY c.id ASC
")->fetchAll();

$titleCounts = [];
foreach ($courses as $course) {
    $titleCounts[$course['titulo']] = ($titleCounts[$course['titulo']] ?? 0) + 1;
}

foreach ($courses as $course) {
    $issues = [];
    $expectedLanguage = $course['idioma_objetivo'] ?: $course['idioma'];

    if ($course['lesson_count'] == 0) {
        $issues[] = 'sin lecciones';
    }
    if ($course['theory_count'] == 0) {
        $issues[] = 'sin teoria';
    }
    if ($course['activity_count'] == 0) {
        $issues[] = 'sin actividades';
    }
    if ($titleCounts[$course['titulo']] > 1) {
        $issues[] = 'titulo duplicado';
    }
    if ($course['idioma'] !== $expectedLanguage && !($course['idioma'] === 'ingles' && $course['idioma_objetivo'] === 'japones')) {
        $issues[] = 'idioma distinto a idioma_objetivo';
    }
    if ($course['idioma'] === 'ingles' && $course['idioma_objetivo'] === 'japones' && $course['idioma_base'] === 'espanol') {
        $issues[] = 'curso japones con idioma principal incorrecto';
    }
    if (stripos($course['titulo'], 'dummies') !== false) {
        $issues[] = 'titulo poco profesional';
    }
    if ($course['nivel_cefr_desde'] === $course['nivel_cefr_hasta'] && stripos($course['titulo'], 'ruta completa') !== false) {
        $issues[] = 'titulo sugiere ruta pero nivel unico';
    }
    if ($course['estado'] !== 'activo') {
        $issues[] = 'estado no activo';
    }

    echo 'Curso #' . $course['id'] . PHP_EOL;
    echo '  Titulo: ' . $course['titulo'] . PHP_EOL;
    echo '  Idioma: ' . $course['idioma'] . ' | Objetivo: ' . $course['idioma_objetivo'] . ' | Base: ' . $course['idioma_base'] . PHP_EOL;
    echo '  Nivel: ' . $course['nivel_cefr_desde'] . '-' . $course['nivel_cefr_hasta'] . ' | Estado: ' . $course['estado'] . PHP_EOL;
    echo '  Creador: ' . ($course['creator_email'] ?: 'sin email') . ' | Instancia: ' . $course['instancia_id'] . PHP_EOL;
    echo '  Lecciones: ' . $course['lesson_count'] . ' | Teoria: ' . $course['theory_count'] . ' | Actividades: ' . $course['activity_count'] . ' | Inscritos: ' . $course['enrollment_count'] . PHP_EOL;
    echo '  Issues: ' . (empty($issues) ? 'ninguno' : implode(', ', $issues)) . PHP_EOL;

    $lessons = $pdo->prepare("
        SELECT
            l.id,
            l.titulo,
            l.orden,
            l.estado,
            (
                SELECT COUNT(*)
                FROM teoria t
                WHERE t.leccion_id = l.id
            ) AS theory_count,
            (
                SELECT COUNT(*)
                FROM actividades a
                WHERE a.leccion_id = l.id
            ) AS activity_count
        FROM lecciones l
        WHERE l.curso_id = ?
        ORDER BY l.orden ASC, l.id ASC
    ");
    $lessons->execute([$course['id']]);

    foreach ($lessons as $lesson) {
        $lessonIssues = [];
        if ($lesson['theory_count'] == 0) {
            $lessonIssues[] = 'sin teoria';
        }
        if ($lesson['activity_count'] == 0) {
            $lessonIssues[] = 'sin actividades';
        }
        echo '    Leccion #' . $lesson['id'] . ' [' . $lesson['orden'] . '] ' . $lesson['titulo'] .
            ' | teoria=' . $lesson['theory_count'] .
            ' | actividades=' . $lesson['activity_count'] .
            ' | estado=' . $lesson['estado'] .
            (empty($lessonIssues) ? '' : ' | issues=' . implode('/', $lessonIssues)) .
            PHP_EOL;
    }

    echo PHP_EOL;
}
