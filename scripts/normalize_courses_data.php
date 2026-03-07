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

$updates = [];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('UPDATE cursos SET idioma = ?, titulo = ?, descripcion = ? WHERE id = ?');
    $stmt->execute([
        'japones',
        'Japones Zero to Hero: Ruta guiada A1-B1',
        'Ruta practica para hispanohablantes desde cero absoluto hasta base conversacional funcional en japones. Curso guiado para principiantes totales con enfoque progresivo y objetivos claros.',
        19,
    ]);
    $updates[] = 'Curso 19: corregido idioma principal y titulo';

    $stmt = $pdo->prepare('UPDATE cursos SET titulo = ?, descripcion = ?, idioma_base = ?, es_publico = 0, inscripcion_abierta = 0, estado = ? WHERE id = ?');
    $stmt->execute([
        'Japanese Zero to Hero: English-base draft (archived)',
        'Borrador interno derivado del curso de japones. Se archiva porque el contenido aun no esta realmente adaptado para base en ingles.',
        'ingles',
        'archivado',
        20,
    ]);
    $updates[] = 'Curso 20: archivado por variante base ingles no traducida';

    $stmt->execute([
        'Frances Zero to Hero: Sante & Sourires (English-base draft, archived)',
        'Borrador interno derivado del curso de frances. Se archiva porque el contenido aun no esta realmente adaptado para base en ingles.',
        'ingles',
        'archivado',
        22,
    ]);
    $updates[] = 'Curso 22: archivado por variante base ingles no traducida';

    $stmt = $pdo->prepare('UPDATE cursos SET titulo = ?, descripcion = ?, es_publico = 0, inscripcion_abierta = 0 WHERE id = ?');
    $stmt->execute([
        '[QA] French Activity Lab',
        'Curso interno para QA funcional de actividades soportadas. Se mantiene disponible para pruebas, pero fuera de la oferta publica.',
        16,
    ]);
    $updates[] = 'Curso 16: marcado como QA y retirado de oferta publica';

    $stmt = $pdo->prepare('UPDATE lecciones SET titulo = ?, descripcion = ? WHERE id = ?');
    $stmt->execute([
        '[QA] Lecon Demo: Activites Supportees',
        'Leccion interna para probar actividades soportadas en la experiencia principal del estudiante.',
        44,
    ]);
    $updates[] = 'Leccion 44: renombrada como QA';

    $stmt = $pdo->prepare('UPDATE lecciones SET titulo = ?, descripcion = ? WHERE id = ?');
    $stmt->execute([
        'Lesson 8. Bonus practice lab',
        'Bonus final con actividades mixtas para practicar varios formatos soportados sin romper el flujo principal del curso.',
        39,
    ]);
    $updates[] = 'Leccion 39: convertida en bonus explicito';

    $stmt = $pdo->prepare('UPDATE cursos SET titulo = ? WHERE id = ?');
    $stmt->execute([
        'Frances de Cero a Intermedio: Ruta divertida A1-B1',
        24,
    ]);
    $updates[] = 'Curso 24: titulo alineado con el rango CEFR real';

    $stmt = $pdo->prepare('UPDATE lecciones SET orden = ? WHERE id = ?');
    $lessonOrders = [
        45 => 1,
        48 => 2,
        46 => 3,
        47 => 4,
        49 => 5,
        50 => 6,
    ];
    foreach ($lessonOrders as $lessonId => $order) {
        $stmt->execute([$order, $lessonId]);
    }
    $updates[] = 'Curso 17: secuencia de lecciones normalizada';

    $pdo->commit();

    echo json_encode([
        'database' => DB_NAME,
        'host' => DB_HOST,
        'updates' => $updates,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}
