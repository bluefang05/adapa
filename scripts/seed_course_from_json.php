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

if ($argc < 2) {
    throw new RuntimeException('Uso: php scripts/seed_course_from_json.php ruta\\archivo.json [email_profesor] [email_estudiante]');
}

$jsonPath = $argv[1];
$professorEmail = $argv[2] ?? 'profesor@adapa.edu';
$studentEmail = $argv[3] ?? 'estudiante1@adapa.edu';

if (!is_file($jsonPath)) {
    throw new RuntimeException('No se encontro el archivo JSON: ' . $jsonPath);
}

$raw = file_get_contents($jsonPath);
$rawBlueprint = json_decode($raw, true);

if (!is_array($rawBlueprint)) {
    throw new RuntimeException('El JSON no es valido o no se pudo decodificar.');
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

$findUser = $pdo->prepare('SELECT id, instancia_id FROM usuarios WHERE email = ? LIMIT 1');
$findUser->execute([$professorEmail]);
$professor = $findUser->fetch();
if (!$professor) {
    throw new RuntimeException('No se encontro el profesor: ' . $professorEmail);
}

$findUser->execute([$studentEmail]);
$student = $findUser->fetch();
if (!$student) {
    throw new RuntimeException('No se encontro el estudiante: ' . $studentEmail);
}

$blueprint = normalize_import_blueprint($rawBlueprint);
$course = isset($blueprint['course']) && is_array($blueprint['course']) ? $blueprint['course'] : [];
$lessons = isset($blueprint['lessons']) && is_array($blueprint['lessons']) ? $blueprint['lessons'] : [];

if (empty($course) || empty($lessons)) {
    throw new RuntimeException('El JSON no cumple la estructura minima esperada.');
}

$courseDefaults = [
    'idioma_ensenanza' => 'espanol',
    'modalidad' => 'perpetuo',
    'duracion_semanas' => 8,
    'es_publico' => 1,
    'requiere_codigo' => 0,
    'codigo_acceso' => null,
    'tipo_codigo' => null,
    'inscripcion_abierta' => 1,
    'max_estudiantes' => 1000,
    'estado' => 'activo',
    'notificar_profesor_completada' => 1,
    'notificar_profesor_atascado' => 1,
];

$course = array_merge($courseDefaults, $course);
$course['instancia_id'] = (int) $professor['instancia_id'];
$course['creado_por'] = (int) $professor['id'];
$course['fecha_inicio'] = date('Y-m-d');
$course['fecha_fin'] = null;
$course['fecha_cierre_inscripcion'] = null;

$requiredCourseFields = ['titulo', 'descripcion', 'idioma', 'idioma_objetivo', 'idioma_base', 'nivel_cefr', 'nivel_cefr_desde', 'nivel_cefr_hasta'];
foreach ($requiredCourseFields as $field) {
    if (!array_key_exists($field, $course) || trim((string) $course[$field]) === '') {
        throw new RuntimeException('Falta el campo obligatorio de course: ' . $field);
    }
}

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

$insertLesson = $pdo->prepare('
    INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado)
    VALUES (?, ?, ?, ?, ?, 1, "publicada")
');

$insertTheory = $pdo->prepare('
    INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo)
    VALUES (?, ?, ?, "texto", ?, ?, 0)
');

$insertBlock = $pdo->prepare('
    INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
');

$findExistingMedia = $pdo->prepare('
    SELECT id
    FROM media_recursos
    WHERE profesor_id = ?
      AND instancia_id = ?
      AND titulo = ?
      AND ruta_archivo = ?
    LIMIT 1
');

$insertMedia = $pdo->prepare('
    INSERT INTO media_recursos (
        instancia_id,
        profesor_id,
        titulo,
        descripcion,
        tipo_media,
        ruta_archivo,
        mime_type,
        idioma,
        alt_text,
        metadata
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');

$insertActivity = $pdo->prepare('
    INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")
');

$insertEnrollment = $pdo->prepare('INSERT IGNORE INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');

function parse_json_string_or_array($value): array
{
    if (is_array($value)) {
        return $value;
    }

    if (!is_string($value)) {
        return [];
    }

    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : [];
}

function normalize_activity_payload(string $activityType, array $decoded, string $fallbackInstructions): array
{
    $instructions = trim((string) ($decoded['instruccion'] ?? $decoded['instructions'] ?? $fallbackInstructions));

    if ($activityType === 'completar_oracion') {
        $items = $decoded['items'] ?? $decoded;
        $items = is_array($items) ? $items : [];
        $normalized = [];

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $oracion = (string) ($item['oracion'] ?? '');
            $oracion = str_replace('___', '____', $oracion);

            $normalized[] = [
                'id' => (string) ($item['id'] ?? ('q' . ($idx + 1))),
                'oracion' => $oracion,
                'respuesta_correcta' => (string) ($item['respuesta_correcta'] ?? ''),
            ];
        }

        return [$normalized, $instructions];
    }

    if ($activityType === 'ordenar_palabras') {
        $items = $decoded['items'] ?? $decoded;
        $items = is_array($items) ? $items : [];
        $normalized = [];

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $correctSentence = trim((string) ($item['respuesta_correcta'] ?? ''));
            $correctTokens = $correctSentence !== '' ? preg_split('/\s+/', $correctSentence) : [];
            if (!is_array($correctTokens)) {
                $correctTokens = [];
            }

            $normalized[] = [
                'id' => (string) ($item['id'] ?? ('ord_' . ($idx + 1))),
                'instruction' => $instructions !== '' ? $instructions : 'Ordena la frase.',
                'items' => array_values(array_filter(array_map('strval', $correctTokens), static fn($token) => trim($token) !== '')),
            ];
        }

        return [$normalized, $instructions];
    }

    if ($activityType === 'emparejamiento') {
        $pairs = $decoded['pares'] ?? $decoded['pairs'] ?? [];
        $pairs = is_array($pairs) ? $pairs : [];
        $normalizedPairs = [];

        foreach ($pairs as $pair) {
            if (!is_array($pair)) {
                continue;
            }

            $left = (string) ($pair['left'] ?? $pair['izquierda'] ?? '');
            $right = (string) ($pair['right'] ?? $pair['derecha'] ?? '');
            if (trim($left) === '' || trim($right) === '') {
                continue;
            }

            $normalizedPairs[] = [
                'left' => $left,
                'right' => $right,
            ];
        }

        return [[
            'pares' => $normalizedPairs,
        ], $instructions];
    }

    if ($activityType === 'escucha') {
        $items = $decoded['items'] ?? [];
        $items = is_array($items) ? $items : [];
        $prompts = [];

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $textoTts = (string) ($item['texto_tts'] ?? $item['tts_text'] ?? '');
            $transcripcion = (string) ($item['transcripcion'] ?? $item['transcripcion_esperada'] ?? $textoTts);

            if (trim($textoTts) === '' && trim($transcripcion) === '') {
                continue;
            }

            $prompts[] = [
                'id' => (string) ($item['id'] ?? ('listen_' . ($idx + 1))),
                'descripcion' => (string) ($item['descripcion'] ?? ('Bloque ' . ($idx + 1))),
                'speaker_label' => (string) ($item['speaker_label'] ?? ('Voz ' . ($idx + 1))),
                'texto_tts' => $textoTts !== '' ? $textoTts : $transcripcion,
                'transcripcion' => $transcripcion,
            ];
        }

        return [[
            'idioma_objetivo' => 'frances',
            'intro' => $instructions,
            'preguntas' => $prompts,
        ], $instructions];
    }

    if ($activityType === 'pronunciacion') {
        $items = $decoded['items'] ?? $decoded;
        $items = is_array($items) ? $items : [];
        return [$items, $instructions];
    }

    if ($activityType === 'opcion_multiple') {
        return [$decoded, $instructions];
    }

    return [$decoded, $instructions];
}

function normalize_import_blueprint(array $rawBlueprint): array
{
    if (!empty($rawBlueprint['course']) && !empty($rawBlueprint['lessons'])) {
        return $rawBlueprint;
    }

    if (isset($rawBlueprint['course']) && is_array($rawBlueprint['course']) && isset($rawBlueprint['course']['title']) && isset($rawBlueprint['course']['lessons'])) {
        $course = $rawBlueprint['course'];
        $lessons = $course['lessons'];
        $lessons = is_array($lessons) ? $lessons : [];

        $normalizedCourse = [
            'titulo' => (string) ($course['title'] ?? 'Curso sin titulo'),
            'descripcion' => (string) ($course['description'] ?? ''),
            'idioma' => 'frances',
            'idioma_objetivo' => 'frances',
            'idioma_base' => 'espanol',
            'idioma_ensenanza' => 'espanol',
            'nivel_cefr' => 'A1',
            'nivel_cefr_desde' => 'A1',
            'nivel_cefr_hasta' => 'C1',
        ];

        $normalizedLessons = [];
        foreach ($lessons as $lesson) {
            if (!is_array($lesson)) {
                continue;
            }

            $content = $lesson['content'] ?? [];
            $content = is_array($content) ? $content : [];

            $teoria = [];
            $actividades = [];

            foreach ($content as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $type = (string) ($item['type'] ?? '');
                if ($type === 'theory') {
                    $title = trim((string) ($item['title'] ?? ''));
                    $preview = trim((string) ($item['content_preview'] ?? ''));
                    if ($preview === '' && $title === '') {
                        continue;
                    }

                    $blocks = [[
                        'tipo_bloque' => 'explicacion',
                        'titulo' => $title !== '' ? $title : null,
                        'contenido' => $preview !== '' ? $preview : $title,
                        'idioma_bloque' => 'espanol',
                        'tts_habilitado' => 1,
                    ]];

                    $teoria[] = [
                        'titulo' => $title !== '' ? $title : 'Teoria',
                        'duracion' => (int) ($item['duration'] ?? 15),
                        'bloques' => $blocks,
                    ];
                    continue;
                }

                if ($type === 'activity') {
                    $activityType = (string) ($item['activity_type'] ?? '');
                    if ($activityType === '') {
                        continue;
                    }

                    $decoded = parse_json_string_or_array($item['content_json'] ?? []);
                    [$normalizedContent, $instructions] = normalize_activity_payload(
                        $activityType,
                        $decoded,
                        (string) ($item['instructions'] ?? '')
                    );

                    $actividades[] = [
                        'titulo' => (string) ($item['title'] ?? 'Actividad'),
                        'descripcion' => '',
                        'tipo' => $activityType,
                        'instrucciones' => $instructions,
                        'contenido' => $normalizedContent,
                        'puntos' => (int) ($item['points'] ?? 10),
                        'tiempo' => (int) ($item['time'] ?? 5),
                    ];
                }
            }

            $normalizedLessons[] = [
                'titulo' => (string) ($lesson['title'] ?? 'Leccion sin titulo'),
                'descripcion' => (string) ($lesson['description'] ?? ''),
                'duracion' => (int) ($lesson['duration'] ?? 90),
                'teoria' => $teoria,
                'actividades' => $actividades,
            ];
        }

        return [
            'course' => $normalizedCourse,
            'lessons' => $normalizedLessons,
        ];
    }

    throw new RuntimeException('El JSON no cumple la estructura minima esperada.');
}

function lesson_theory_html_from_blocks(array $blocks): string
{
    $html = '<div class="theory-rich">';

    foreach ($blocks as $block) {
        $title = trim((string) ($block['titulo'] ?? ''));
        $content = trim((string) ($block['contenido'] ?? ''));

        if ($title !== '') {
            $html .= '<h3>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        }

        if ($content !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $content);
            $bulletLines = array_values(array_filter($lines, static fn($line) => preg_match('/^\s*[-*]\s+/', $line) === 1));

            if (!empty($bulletLines) && count($bulletLines) === count(array_filter($lines, static fn($line) => trim($line) !== ''))) {
                $html .= '<ul>';
                foreach ($bulletLines as $line) {
                    $html .= '<li>' . htmlspecialchars(trim(preg_replace('/^\s*[-*]\s+/', '', $line)), ENT_QUOTES, 'UTF-8') . '</li>';
                }
                $html .= '</ul>';
            } else {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $html .= '<p>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</p>';
                }
            }
        }
    }

    return $html . '</div>';
}

function resolve_block_media_id(PDO $pdo, array $block, array $professor, PDOStatement $findExistingMedia, PDOStatement $insertMedia): ?int
{
    if (empty($block['media']) || !is_array($block['media'])) {
        return null;
    }

    $media = $block['media'];
    $title = trim((string) ($media['titulo'] ?? ''));
    $path = trim((string) ($media['ruta_archivo'] ?? ''));

    if ($title === '' || $path === '') {
        return null;
    }

    $findExistingMedia->execute([
        (int) $professor['id'],
        (int) $professor['instancia_id'],
        $title,
        $path,
    ]);
    $existing = $findExistingMedia->fetch();
    if ($existing) {
        return (int) $existing['id'];
    }

    $metadata = $media['metadata'] ?? null;
    $insertMedia->execute([
        (int) $professor['instancia_id'],
        (int) $professor['id'],
        $title,
        (string) ($media['descripcion'] ?? ''),
        (string) ($media['tipo_media'] ?? 'documento'),
        $path,
        (string) ($media['mime_type'] ?? null),
        (string) ($media['idioma'] ?? null),
        (string) ($media['alt_text'] ?? $title),
        $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);

    return (int) $pdo->lastInsertId();
}

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
$blockCount = 0;

foreach ($lessons as $lessonIndex => $lesson) {
    $lessonTitle = trim((string) ($lesson['titulo'] ?? ''));
    if ($lessonTitle === '') {
        throw new RuntimeException('Una leccion no tiene titulo.');
    }

    $insertLesson->execute([
        $courseId,
        $lessonTitle,
        (string) ($lesson['descripcion'] ?? ''),
        $lessonIndex + 1,
        (int) ($lesson['duracion'] ?? 90),
    ]);

    $lessonId = (int) $pdo->lastInsertId();
    $lessonCount++;

    foreach (($lesson['teoria'] ?? []) as $theoryIndex => $theory) {
        $blocks = $theory['bloques'] ?? [];
        if (!is_array($blocks) || empty($blocks)) {
            continue;
        }

        $html = lesson_theory_html_from_blocks($blocks);
        $insertTheory->execute([
            $lessonId,
            (string) ($theory['titulo'] ?? ('Teoria ' . ($theoryIndex + 1))),
            $html,
            (int) ($theory['duracion'] ?? 15),
            $theoryIndex + 1,
        ]);

        $theoryId = (int) $pdo->lastInsertId();
        $theoryCount++;

        foreach ($blocks as $blockIndex => $block) {
            $mediaId = resolve_block_media_id($pdo, $block, $professor, $findExistingMedia, $insertMedia);
            $insertBlock->execute([
                $theoryId,
                (string) ($block['tipo_bloque'] ?? 'explicacion'),
                trim((string) ($block['titulo'] ?? '')) ?: null,
                trim((string) ($block['contenido'] ?? '')) ?: null,
                trim((string) ($block['idioma_bloque'] ?? '')) ?: null,
                !empty($block['tts_habilitado']) ? 1 : 0,
                $mediaId,
                $blockIndex + 1,
            ]);
            $blockCount++;
        }
    }

    foreach (($lesson['actividades'] ?? []) as $activityIndex => $activity) {
        $activityType = (string) ($activity['tipo'] ?? '');
        if ($activityType === '') {
            continue;
        }

        $insertActivity->execute([
            $lessonId,
            (string) ($activity['titulo'] ?? ('Actividad ' . ($activityIndex + 1))),
            (string) ($activity['descripcion'] ?? ''),
            $activityType,
            (string) ($activity['instrucciones'] ?? ''),
            json_encode($activity['contenido'] ?? new stdClass(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            (int) ($activity['puntos'] ?? 10),
            (int) ($activity['tiempo'] ?? 5),
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
    'blocks' => $blockCount,
    'activities' => $activityCount,
    'creator_email' => $professorEmail,
    'student_email' => $studentEmail,
    'database' => DB_NAME,
    'host' => DB_HOST,
    'source_json' => realpath($jsonPath) ?: $jsonPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
