<?php

if (PHP_SAPI === 'cli') {
    if (empty($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = 'localhost';
    }
    if (empty($_SERVER['SERVER_NAME'])) {
        $_SERVER['SERVER_NAME'] = 'localhost';
    }
}

require_once __DIR__ . '/../config/database.php';

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('Este script debe ejecutarse desde CLI.');
}

$courseId = isset($argv[1]) ? (int) $argv[1] : 0;
if ($courseId <= 0) {
    throw new RuntimeException('Uso: php scripts/inspect_course.php <course_id>');
}

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

$courseStmt = $pdo->prepare('
    SELECT id, titulo, descripcion, nivel_cefr_desde, nivel_cefr_hasta, idioma_objetivo, idioma_base
    FROM cursos
    WHERE id = ?
');
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch();

if (!$course) {
    fwrite(STDOUT, "Course {$courseId} not found." . PHP_EOL);
    exit(1);
}

echo 'COURSE ' . $course['id'] . ': ' . $course['titulo'] . ' [' . $course['idioma_objetivo'] . ' desde ' . $course['idioma_base'] . ' | ' . $course['nivel_cefr_desde'] . '-' . $course['nivel_cefr_hasta'] . ']' . PHP_EOL;
echo $course['descripcion'] . PHP_EOL . PHP_EOL;

$lessonStmt = $pdo->prepare('
    SELECT id, orden, titulo, descripcion
    FROM lecciones
    WHERE curso_id = ?
    ORDER BY orden ASC, id ASC
');
$theoryStmt = $pdo->prepare('
    SELECT id, orden, titulo
    FROM teoria
    WHERE leccion_id = ?
    ORDER BY orden ASC, id ASC
');
$blockStmt = $pdo->prepare('
    SELECT orden, tipo_bloque, titulo, contenido
    FROM contenido_bloques
    WHERE teoria_id = ?
    ORDER BY orden ASC, id ASC
');
$activityStmt = $pdo->prepare('
    SELECT id, orden, titulo, descripcion, tipo_actividad, instrucciones, contenido
    FROM actividades
    WHERE leccion_id = ?
    ORDER BY orden ASC, id ASC
');

$lessonStmt->execute([$courseId]);
$lessons = $lessonStmt->fetchAll();

foreach ($lessons as $lesson) {
    echo 'LESSON ' . $lesson['orden'] . ' (#' . $lesson['id'] . '): ' . $lesson['titulo'] . PHP_EOL;
    echo '  ' . $lesson['descripcion'] . PHP_EOL;

    $theoryStmt->execute([$lesson['id']]);
    foreach ($theoryStmt->fetchAll() as $theory) {
        echo '  THEORY ' . $theory['orden'] . ' (#' . $theory['id'] . '): ' . $theory['titulo'] . PHP_EOL;
        $blockStmt->execute([$theory['id']]);
        foreach ($blockStmt->fetchAll() as $block) {
            $content = preg_replace('/\s+/', ' ', trim((string) $block['contenido']));
            if (mb_strlen($content, 'UTF-8') > 140) {
                $content = mb_substr($content, 0, 140, 'UTF-8') . '...';
            }
            echo '    - [' . $block['tipo_bloque'] . '] ' . ($block['titulo'] ?: 'sin titulo') . ': ' . $content . PHP_EOL;
        }
    }

    $activityStmt->execute([$lesson['id']]);
    foreach ($activityStmt->fetchAll() as $activity) {
        $payload = json_decode($activity['contenido'], true);
        if (is_array($payload)) {
            $payloadShape = array_is_list($payload)
                ? 'list[' . count($payload) . ']'
                : implode(', ', array_slice(array_keys($payload), 0, 6));
        } else {
            $payloadShape = gettype($payload);
        }

        echo '  ACTIVITY ' . $activity['orden'] . ' (#' . $activity['id'] . '): ' . $activity['titulo'] . ' [' . $activity['tipo_actividad'] . ']' . PHP_EOL;
        echo '    Desc: ' . $activity['descripcion'] . PHP_EOL;
        echo '    Instr: ' . $activity['instrucciones'] . PHP_EOL;
        echo '    Payload: ' . $payloadShape . PHP_EOL;
    }

    echo PHP_EOL;
}
