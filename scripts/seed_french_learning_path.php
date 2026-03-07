<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/french_learning_path_blueprint.php';

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

$blueprint = french_learning_path_blueprint();
$course = $blueprint['course'];
$lessons = $blueprint['lessons'];

$professorEmail = 'profesor@adapa.edu';
$studentEmail = 'estudiante1@adapa.edu';

$findUser = $pdo->prepare('SELECT id, instancia_id FROM usuarios WHERE email = ? LIMIT 1');
$findUser->execute([$professorEmail]);
$professor = $findUser->fetch();
if (!$professor) {
    throw new RuntimeException('No se encontro el profesor demo: ' . $professorEmail);
}

$findUser->execute([$studentEmail]);
$student = $findUser->fetch();
if (!$student) {
    throw new RuntimeException('No se encontro el estudiante demo: ' . $studentEmail);
}

$course['instancia_id'] = (int) $professor['instancia_id'];
$course['creado_por'] = (int) $professor['id'];
$course['fecha_inicio'] = date('Y-m-d');
$course['fecha_fin'] = null;
$course['fecha_cierre_inscripcion'] = null;

$findExistingCourse = $pdo->prepare('SELECT id FROM cursos WHERE creado_por = ? AND titulo = ? LIMIT 1');
$deleteExistingCourse = $pdo->prepare('DELETE FROM cursos WHERE id = ?');
$insertCourse = $pdo->prepare('
    INSERT INTO cursos (
        instancia_id, plantilla_pensum_id, creado_por, titulo, descripcion,
        idioma, idioma_objetivo, idioma_base, idioma_ensenanza,
        portada_media_id, nivel_cefr_desde, nivel_cefr_hasta, nivel_cefr,
        modalidad, fecha_inicio, fecha_fin, duracion_semanas,
        es_publico, requiere_codigo, codigo_acceso, tipo_codigo,
        inscripcion_abierta, fecha_cierre_inscripcion, max_estudiantes, estado,
        notificar_profesor_completada, notificar_profesor_atascado
    ) VALUES (
        :instancia_id, NULL, :creado_por, :titulo, :descripcion,
        :idioma, :idioma_objetivo, :idioma_base, :idioma_ensenanza,
        NULL, :nivel_cefr_desde, :nivel_cefr_hasta, :nivel_cefr,
        :modalidad, :fecha_inicio, :fecha_fin, :duracion_semanas,
        :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo,
        :inscripcion_abierta, :fecha_cierre_inscripcion, :max_estudiantes, :estado,
        :notificar_profesor_completada, :notificar_profesor_atascado
    )
');
$insertLesson = $pdo->prepare('INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (?, ?, ?, ?, ?, 1, "publicada")');
$insertTheory = $pdo->prepare('INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo) VALUES (?, ?, ?, "texto", ?, ?, 0)');
$insertBlock = $pdo->prepare('INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)');
$insertActivity = $pdo->prepare('INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")');
$insertEnrollment = $pdo->prepare('INSERT IGNORE INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');

$pdo->beginTransaction();

$findExistingCourse->execute([$course['creado_por'], $course['titulo']]);
$existingCourse = $findExistingCourse->fetch();
if ($existingCourse) {
    $deleteExistingCourse->execute([(int) $existingCourse['id']]);
}

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
        $html = french_theory_html(
            $theory['intro'],
            $theory['sections'],
            $theory['tip'],
            $theory['scenario_title'],
            $theory['scenario_lines']
        );

        $insertTheory->execute([$lessonId, $theory['titulo'], $html, $theory['duracion'], $theoryIndex + 1]);
        $theoryId = (int) $pdo->lastInsertId();
        $theoryCount++;

        $blocks = french_theory_blocks(
            $theory['intro'],
            $theory['sections'],
            $theory['tip'],
            $theory['scenario_title'],
            $theory['scenario_lines']
        );

        foreach ($blocks as $blockIndex => $block) {
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

$insertEnrollment->execute([$courseId, (int) $student['id']]);
$pdo->commit();

echo json_encode([
    'course_id' => $courseId,
    'title' => $course['titulo'],
    'lessons' => $lessonCount,
    'theory_items' => $theoryCount,
    'activities' => $activityCount,
    'creator_email' => $professorEmail,
    'student_email' => $studentEmail,
    'database' => DB_NAME,
    'host' => DB_HOST,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
