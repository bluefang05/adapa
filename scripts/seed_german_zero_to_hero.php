<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=adapa;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

require_once __DIR__ . '/german_course_blueprint.php';

$blueprint = german_course_blueprint();
$course = $blueprint['course'];
$lessons = $blueprint['lessons'];

$insertCourse = $pdo->prepare('INSERT INTO cursos (instancia_id, creado_por, titulo, descripcion, idioma, idioma_objetivo, idioma_ensenanza, nivel_cefr, nivel_cefr_desde, nivel_cefr_hasta, modalidad, fecha_inicio, fecha_fin, duracion_semanas, es_publico, requiere_codigo, codigo_acceso, tipo_codigo, inscripcion_abierta, fecha_cierre_inscripcion, max_estudiantes, estado, notificar_profesor_completada, notificar_profesor_atascado) VALUES (:instancia_id, :creado_por, :titulo, :descripcion, :idioma, :idioma_objetivo, :idioma_ensenanza, :nivel_cefr, :nivel_cefr_desde, :nivel_cefr_hasta, :modalidad, :fecha_inicio, :fecha_fin, :duracion_semanas, :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :inscripcion_abierta, :fecha_cierre_inscripcion, :max_estudiantes, :estado, :notificar_profesor_completada, :notificar_profesor_atascado)');
$insertLesson = $pdo->prepare('INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (?, ?, ?, ?, ?, 1, "publicada")');
$insertTheory = $pdo->prepare('INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo) VALUES (?, ?, ?, "texto", ?, ?, 0)');
$insertBlock = $pdo->prepare('INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)');
$insertActivity = $pdo->prepare('INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")');
$insertEnrollment = $pdo->prepare('INSERT INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');

$pdo->beginTransaction();
$insertCourse->execute($course);
$courseId = (int) $pdo->lastInsertId();
$lessonCount = 0;
$theoryCount = 0;
$activityCount = 0;

foreach ($lessons as $lessonIndex => $lesson) {
    $insertLesson->execute([$courseId, $lesson['titulo'], $lesson['descripcion'], $lessonIndex + 1, $lesson['duracion']]);
    $lessonId = (int) $pdo->lastInsertId();
    $lessonCount++;

    foreach ($lesson['teoria'] as $theoryIndex => $theory) {
        $insertTheory->execute([
            $lessonId,
            $theory['titulo'],
            german_theory_html($theory['intro'], $theory['sections'], $theory['tip']),
            $theory['duracion'],
            $theoryIndex + 1,
        ]);
        $theoryId = (int) $pdo->lastInsertId();
        $theoryCount++;

        foreach (german_theory_blocks($theory['intro'], $theory['sections'], $theory['tip']) as $blockIndex => $block) {
            $insertBlock->execute([
                $theoryId,
                $block['tipo_bloque'],
                $block['titulo'],
                $block['contenido'],
                $block['idioma_bloque'],
                $block['tts_habilitado'],
                $blockIndex + 1,
            ]);
        }
    }

    foreach ($lesson['actividades'] as $activityIndex => $activity) {
        $insertActivity->execute([
            $lessonId,
            $activity['titulo'],
            $activity['descripcion'],
            $activity['tipo'],
            $activity['instrucciones'],
            json_encode($activity['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $activity['puntos'],
            $activity['tiempo'],
            $activityIndex + 1,
        ]);
        $activityCount++;
    }
}

$insertEnrollment->execute([$courseId, 14]);
$pdo->commit();

echo json_encode([
    'course_id' => $courseId,
    'title' => $course['titulo'],
    'lessons' => $lessonCount,
    'theory_items' => $theoryCount,
    'activities' => $activityCount,
    'professor_id' => 13,
    'student_id' => 14,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
