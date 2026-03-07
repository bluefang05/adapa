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

$courseId = 17;

$courseStmt = $pdo->prepare('SELECT id, titulo, descripcion FROM cursos WHERE id = ?');
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch();

if (!$course) {
    fwrite(STDOUT, "Course 17 not found.\n");
    exit(1);
}

echo "COURSE {$course['id']}: {$course['titulo']}\n";
echo $course['descripcion'] . "\n\n";

$lessonStmt = $pdo->prepare('
    SELECT id, orden, titulo, descripcion
    FROM lecciones
    WHERE curso_id = ?
    ORDER BY orden ASC, id ASC
');
$lessonStmt->execute([$courseId]);
$lessons = $lessonStmt->fetchAll();

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
    SELECT id, orden, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos
    FROM actividades
    WHERE leccion_id = ?
    ORDER BY orden ASC, id ASC
');

foreach ($lessons as $lesson) {
    echo "LESSON {$lesson['orden']} (#{$lesson['id']}): {$lesson['titulo']}\n";
    echo $lesson['descripcion'] . "\n";

    $theoryStmt->execute([$lesson['id']]);
    foreach ($theoryStmt->fetchAll() as $theory) {
        echo "  THEORY {$theory['orden']} (#{$theory['id']}): {$theory['titulo']}\n";
        $blockStmt->execute([$theory['id']]);
        foreach ($blockStmt->fetchAll() as $block) {
            echo "    - [{$block['tipo_bloque']}] {$block['titulo']}: " . preg_replace('/\s+/', ' ', trim($block['contenido'])) . "\n";
        }
    }

    $activityStmt->execute([$lesson['id']]);
    foreach ($activityStmt->fetchAll() as $activity) {
        echo "  ACTIVITY {$activity['orden']} (#{$activity['id']}): {$activity['titulo']} [{$activity['tipo_actividad']}]\n";
        echo "    Desc: {$activity['descripcion']}\n";
        echo "    Instr: {$activity['instrucciones']}\n";
        echo "    Puntos/Tiempo: {$activity['puntos_maximos']}/{$activity['tiempo_limite_minutos']}\n";
        echo "    Payload: {$activity['contenido']}\n";
    }

    echo "\n";
}
