<?php

declare(strict_types=1);

function course_sync_root_path(): string
{
    return dirname(__DIR__);
}

function course_sync_local_config(): array
{
    return [
        'label' => 'local',
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'name' => 'adapa',
        'port' => 3306,
        'charset' => 'utf8mb4',
    ];
}

function course_sync_remote_config(): array
{
    $path = course_sync_root_path() . '/config/database.hosting.php';
    if (!is_file($path)) {
        throw new RuntimeException('No se encontro config/database.hosting.php');
    }

    $config = require $path;
    if (!is_array($config)) {
        throw new RuntimeException('database.hosting.php no devolvio un array valido.');
    }

    $config['label'] = 'remote';
    $config['port'] = (int) ($config['port'] ?? 3306);
    $config['charset'] = $config['charset'] ?? 'utf8mb4';

    return $config;
}

function course_sync_connect(array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'] ?? '127.0.0.1',
        (int) ($config['port'] ?? 3306),
        $config['name'] ?? '',
        $config['charset'] ?? 'utf8mb4'
    );

    return new PDO($dsn, $config['user'] ?? '', $config['pass'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function course_sync_cli_write(string $message): void
{
    if (PHP_SAPI !== 'cli') {
        echo $message;
        return;
    }

    fwrite(STDOUT, $message);
}

function course_sync_decode_json(?string $json): mixed
{
    if (!is_string($json) || trim($json) === '') {
        return null;
    }

    $decoded = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $decoded;
}

function course_sync_encode_json(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function course_sync_title_tokens(string $title): array
{
    $title = mb_strtolower($title, 'UTF-8');
    $title = preg_replace('/\[[^\]]+\]/u', ' ', $title);
    $title = preg_replace('/\([^)]*\)/u', ' ', $title);
    $title = preg_replace('/[^a-z0-9áéíóúñü]+/iu', ' ', $title);
    $parts = preg_split('/\s+/u', trim((string) $title)) ?: [];

    $stopwords = [
        'de', 'del', 'la', 'el', 'los', 'las', 'y', 'o', 'en', 'para', 'por', 'con',
        'from', 'for', 'the', 'and', 'to', 'of', 'draft', 'archived', 'ruta', 'complete',
        'completa', 'guiada', 'premium', 'desde', 'base',
    ];

    $tokens = [];
    foreach ($parts as $part) {
        if ($part === '' || mb_strlen($part, 'UTF-8') < 3) {
            continue;
        }
        if (in_array($part, $stopwords, true)) {
            continue;
        }
        $tokens[] = $part;
    }

    return array_values(array_unique($tokens));
}

function course_sync_title_similarity(string $left, string $right): float
{
    $leftTokens = course_sync_title_tokens($left);
    $rightTokens = course_sync_title_tokens($right);

    if (empty($leftTokens) || empty($rightTokens)) {
        return 0.0;
    }

    $shared = array_values(array_intersect($leftTokens, $rightTokens));
    $union = array_values(array_unique(array_merge($leftTokens, $rightTokens)));

    if (count($shared) < 2) {
        return 0.0;
    }

    return count($shared) / max(count($union), 1);
}

function course_sync_fetch_media_rows(PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM media_recursos WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $media = [];
    foreach ($stmt->fetchAll() as $row) {
        $media[(int) $row['id']] = $row;
    }

    return $media;
}

function course_sync_collect_media_ids_from_value(mixed $value): array
{
    $ids = [];

    if (is_string($value)) {
        $decoded = course_sync_decode_json($value);
        if ($decoded !== null) {
            return course_sync_collect_media_ids_from_value($decoded);
        }
        return [];
    }

    if (!is_array($value)) {
        return [];
    }

    foreach ($value as $key => $item) {
        if (is_string($key) && preg_match('/(^|_)media_id$/', $key) === 1) {
            $mediaId = (int) $item;
            if ($mediaId > 0) {
                $ids[] = $mediaId;
            }
            continue;
        }

        $ids = array_merge($ids, course_sync_collect_media_ids_from_value($item));
    }

    return array_values(array_unique($ids));
}

function course_sync_fetch_course_bundle(PDO $pdo, int $courseId): ?array
{
    $courseStmt = $pdo->prepare("
        SELECT c.*, u.email AS creador_email
        FROM cursos c
        LEFT JOIN usuarios u ON u.id = c.creado_por
        WHERE c.id = ?
        LIMIT 1
    ");
    $courseStmt->execute([$courseId]);
    $course = $courseStmt->fetch();

    if (!$course) {
        return null;
    }

    $lessonsStmt = $pdo->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC, id ASC");
    $lessonsStmt->execute([$courseId]);
    $lessons = $lessonsStmt->fetchAll();

    $theoryStmt = $pdo->prepare("SELECT * FROM teoria WHERE leccion_id = ? ORDER BY orden ASC, id ASC");
    $blocksStmt = $pdo->prepare("
        SELECT cb.*, mr.titulo AS media_titulo, mr.descripcion AS media_descripcion, mr.tipo_media, mr.ruta_archivo, mr.mime_type, mr.idioma, mr.alt_text, mr.metadata
        FROM contenido_bloques cb
        LEFT JOIN media_recursos mr ON mr.id = cb.media_id
        WHERE cb.teoria_id = ?
        ORDER BY cb.orden ASC, cb.id ASC
    ");
    $activityStmt = $pdo->prepare("SELECT * FROM actividades WHERE leccion_id = ? ORDER BY orden ASC, id ASC");
    $optionsStmt = $pdo->prepare("SELECT * FROM opciones_multiples WHERE actividad_id = ? ORDER BY id ASC");

    $mediaIds = [];
    if (!empty($course['portada_media_id'])) {
        $mediaIds[] = (int) $course['portada_media_id'];
    }

    $bundleLessons = [];
    foreach ($lessons as $lesson) {
        $lessonId = (int) $lesson['id'];

        $theoryStmt->execute([$lessonId]);
        $theories = [];
        foreach ($theoryStmt->fetchAll() as $theory) {
            $theoryId = (int) $theory['id'];
            $blocksStmt->execute([$theoryId]);
            $blocks = $blocksStmt->fetchAll();
            foreach ($blocks as $block) {
                if (!empty($block['media_id'])) {
                    $mediaIds[] = (int) $block['media_id'];
                }
            }

            $theories[] = [
                'theory' => $theory,
                'blocks' => $blocks,
            ];
        }

        $activityStmt->execute([$lessonId]);
        $activities = [];
        foreach ($activityStmt->fetchAll() as $activity) {
            $activityId = (int) $activity['id'];
            $optionsStmt->execute([$activityId]);
            $options = $optionsStmt->fetchAll();
            $mediaIds = array_merge($mediaIds, course_sync_collect_media_ids_from_value($activity['contenido'] ?? null));

            $activities[] = [
                'activity' => $activity,
                'options' => $options,
            ];
        }

        $bundleLessons[] = [
            'lesson' => $lesson,
            'theories' => $theories,
            'activities' => $activities,
        ];
    }

    return [
        'course' => $course,
        'media' => course_sync_fetch_media_rows($pdo, $mediaIds),
        'lessons' => $bundleLessons,
    ];
}

function course_sync_media_signature(?array $media): ?array
{
    if (!$media) {
        return null;
    }

    return [
        'titulo' => trim((string) ($media['titulo'] ?? '')),
        'descripcion' => trim((string) ($media['descripcion'] ?? '')),
        'tipo_media' => (string) ($media['tipo_media'] ?? ''),
        'ruta_archivo' => (string) ($media['ruta_archivo'] ?? ''),
        'mime_type' => (string) ($media['mime_type'] ?? ''),
        'idioma' => (string) ($media['idioma'] ?? ''),
        'alt_text' => (string) ($media['alt_text'] ?? ''),
        'metadata' => $media['metadata'] ?? null,
    ];
}

function course_sync_build_course_fingerprint(array $bundle, array $options = []): string
{
    $ignoreTitle = !empty($options['ignore_title']);
    $ignorePublication = !empty($options['ignore_publication']);
    $course = $bundle['course'] ?? [];
    $media = $bundle['media'] ?? [];
    $normalized = [
        'course' => [
            'titulo' => $ignoreTitle ? '' : ($course['titulo'] ?? ''),
            'descripcion' => $course['descripcion'] ?? '',
            'idioma' => $course['idioma'] ?? '',
            'idioma_objetivo' => $course['idioma_objetivo'] ?? '',
            'idioma_base' => $course['idioma_base'] ?? '',
            'idioma_ensenanza' => $course['idioma_ensenanza'] ?? '',
            'nivel_cefr' => $course['nivel_cefr'] ?? '',
            'nivel_cefr_desde' => $course['nivel_cefr_desde'] ?? '',
            'nivel_cefr_hasta' => $course['nivel_cefr_hasta'] ?? '',
            'modalidad' => $course['modalidad'] ?? '',
            'duracion_semanas' => $course['duracion_semanas'] ?? '',
            'es_publico' => $ignorePublication ? '' : ($course['es_publico'] ?? ''),
            'inscripcion_abierta' => $ignorePublication ? '' : ($course['inscripcion_abierta'] ?? ''),
            'estado' => $ignorePublication ? '' : ($course['estado'] ?? ''),
            'portada' => course_sync_media_signature(!empty($course['portada_media_id']) ? ($media[(int) $course['portada_media_id']] ?? null) : null),
        ],
        'lessons' => [],
    ];

    foreach ($bundle['lessons'] ?? [] as $lessonBlock) {
        $lesson = $lessonBlock['lesson'];
        $lessonNormalized = [
            'titulo' => $lesson['titulo'] ?? '',
            'descripcion' => $lesson['descripcion'] ?? '',
            'orden' => (int) ($lesson['orden'] ?? 0),
            'duracion_minutos' => (int) ($lesson['duracion_minutos'] ?? 0),
            'es_obligatoria' => (int) ($lesson['es_obligatoria'] ?? 0),
            'estado' => $lesson['estado'] ?? '',
            'theories' => [],
            'activities' => [],
        ];

        foreach ($lessonBlock['theories'] ?? [] as $theoryBlock) {
            $theory = $theoryBlock['theory'];
            $theoryNormalized = [
                'titulo' => $theory['titulo'] ?? '',
                'contenido' => $theory['contenido'] ?? '',
                'tipo_contenido' => $theory['tipo_contenido'] ?? '',
                'orden' => (int) ($theory['orden'] ?? 0),
                'duracion_minutos' => (int) ($theory['duracion_minutos'] ?? 0),
                'es_interactivo' => (int) ($theory['es_interactivo'] ?? 0),
                'blocks' => [],
            ];

            foreach ($theoryBlock['blocks'] ?? [] as $block) {
                $theoryNormalized['blocks'][] = [
                    'tipo_bloque' => $block['tipo_bloque'] ?? '',
                    'titulo' => $block['titulo'] ?? '',
                    'contenido' => $block['contenido'] ?? '',
                    'idioma_bloque' => $block['idioma_bloque'] ?? '',
                    'tts_habilitado' => (int) ($block['tts_habilitado'] ?? 0),
                    'orden' => (int) ($block['orden'] ?? 0),
                    'media' => course_sync_media_signature(!empty($block['media_id']) ? ($media[(int) $block['media_id']] ?? null) : null),
                ];
            }

            $lessonNormalized['theories'][] = $theoryNormalized;
        }

        foreach ($lessonBlock['activities'] ?? [] as $activityBlock) {
            $activity = $activityBlock['activity'];
            $lessonNormalized['activities'][] = [
                'titulo' => $activity['titulo'] ?? '',
                'descripcion' => $activity['descripcion'] ?? '',
                'tipo_actividad' => $activity['tipo_actividad'] ?? '',
                'instrucciones' => $activity['instrucciones'] ?? '',
                'contenido' => course_sync_decode_json($activity['contenido'] ?? null) ?? ($activity['contenido'] ?? ''),
                'puntos_maximos' => (int) ($activity['puntos_maximos'] ?? 0),
                'tiempo_limite_minutos' => (int) ($activity['tiempo_limite_minutos'] ?? 0),
                'intentos_permitidos' => (int) ($activity['intentos_permitidos'] ?? 0),
                'es_calificable' => (int) ($activity['es_calificable'] ?? 0),
                'orden' => (int) ($activity['orden'] ?? 0),
                'estado' => $activity['estado'] ?? '',
                'options' => array_map(static fn(array $option): array => [
                    'texto' => $option['texto'] ?? ($option['opcion_texto'] ?? ''),
                    'es_correcta' => (int) ($option['es_correcta'] ?? 0),
                ], $activityBlock['options'] ?? []),
            ];
        }

        $normalized['lessons'][] = $lessonNormalized;
    }

    return hash('sha256', course_sync_encode_json($normalized));
}

function course_sync_fetch_course_inventory(PDO $pdo): array
{
    $sql = "
        SELECT c.id, c.titulo, c.creado_por, u.email AS creador_email,
               COUNT(DISTINCT l.id) AS total_lecciones,
               COUNT(DISTINCT t.id) AS total_teorias,
               COUNT(DISTINCT a.id) AS total_actividades
        FROM cursos c
        LEFT JOIN usuarios u ON u.id = c.creado_por
        LEFT JOIN lecciones l ON l.curso_id = c.id
        LEFT JOIN teoria t ON t.leccion_id = l.id
        LEFT JOIN actividades a ON a.leccion_id = l.id
        GROUP BY c.id
        ORDER BY c.id ASC
    ";

    $rows = $pdo->query($sql)->fetchAll();
    foreach ($rows as &$row) {
        $bundle = course_sync_fetch_course_bundle($pdo, (int) $row['id']);
        $row['fingerprint'] = $bundle ? course_sync_build_course_fingerprint($bundle) : null;
        $row['content_fingerprint'] = $bundle
            ? course_sync_build_course_fingerprint($bundle, ['ignore_title' => true, 'ignore_publication' => true])
            : null;
    }
    unset($row);

    return $rows;
}

function course_sync_resolve_remote_teacher(PDO $remotePdo, ?string $preferredEmail = null): array
{
    $emails = array_values(array_unique(array_filter([$preferredEmail, 'profesor@adapa.edu'])));
    $stmt = $remotePdo->prepare('SELECT id, instancia_id, email FROM usuarios WHERE email = ? LIMIT 1');

    foreach ($emails as $email) {
        $stmt->execute([$email]);
        $teacher = $stmt->fetch();
        if ($teacher) {
            return $teacher;
        }
    }

    throw new RuntimeException('No se pudo resolver un profesor remoto valido.');
}

function course_sync_map_media_reference(PDO $remotePdo, array $sourceMediaById, int $sourceMediaId, array $remoteTeacher, array &$mediaMap): ?int
{
    if ($sourceMediaId <= 0) {
        return null;
    }

    if (isset($mediaMap[$sourceMediaId])) {
        return $mediaMap[$sourceMediaId];
    }

    $source = $sourceMediaById[$sourceMediaId] ?? null;
    if (!$source) {
        $mediaMap[$sourceMediaId] = null;
        return null;
    }

    $findStmt = $remotePdo->prepare('
        SELECT id
        FROM media_recursos
        WHERE profesor_id = ?
          AND instancia_id = ?
          AND titulo = ?
          AND ruta_archivo = ?
        LIMIT 1
    ');
    $findStmt->execute([
        (int) $remoteTeacher['id'],
        (int) $remoteTeacher['instancia_id'],
        $source['titulo'] ?? '',
        $source['ruta_archivo'] ?? '',
    ]);
    $existing = $findStmt->fetch();
    if ($existing) {
        $mediaMap[$sourceMediaId] = (int) $existing['id'];
        return $mediaMap[$sourceMediaId];
    }

    $insertStmt = $remotePdo->prepare('
        INSERT INTO media_recursos (
            instancia_id, profesor_id, titulo, descripcion, tipo_media, ruta_archivo,
            mime_type, idioma, alt_text, metadata
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $insertStmt->execute([
        (int) $remoteTeacher['instancia_id'],
        (int) $remoteTeacher['id'],
        $source['titulo'] ?? '',
        $source['descripcion'] ?? null,
        $source['tipo_media'] ?? 'documento',
        $source['ruta_archivo'] ?? '',
        $source['mime_type'] ?? null,
        $source['idioma'] ?? null,
        $source['alt_text'] ?? null,
        $source['metadata'] ?? null,
    ]);

    $mediaMap[$sourceMediaId] = (int) $remotePdo->lastInsertId();
    return $mediaMap[$sourceMediaId];
}

function course_sync_remap_media_ids_in_value(mixed $value, array $mediaMap): mixed
{
    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        if (is_string($key) && preg_match('/(^|_)media_id$/', $key) === 1) {
            $mediaId = (int) $item;
            if ($mediaId > 0 && array_key_exists($mediaId, $mediaMap)) {
                $value[$key] = $mediaMap[$mediaId];
            }
            continue;
        }

        $value[$key] = course_sync_remap_media_ids_in_value($item, $mediaMap);
    }

    return $value;
}

function course_sync_remap_activity_content(?string $json, array $mediaMap): ?string
{
    if (!is_string($json) || trim($json) === '') {
        return $json;
    }

    $decoded = course_sync_decode_json($json);
    if ($decoded === null) {
        return $json;
    }

    return course_sync_encode_json(course_sync_remap_media_ids_in_value($decoded, $mediaMap));
}

function course_sync_sync_local_course_to_remote(PDO $localPdo, PDO $remotePdo, int $localCourseId, array $options = []): array
{
    $bundle = course_sync_fetch_course_bundle($localPdo, $localCourseId);
    if (!$bundle) {
        throw new RuntimeException('No se encontro el curso local ' . $localCourseId);
    }

    $localCourse = $bundle['course'];
    $remoteTeacher = course_sync_resolve_remote_teacher($remotePdo, $options['teacher_email'] ?? ($localCourse['creador_email'] ?? null));
    $remoteCourseId = isset($options['remote_course_id']) ? (int) $options['remote_course_id'] : null;

    $fetchRemoteCourse = $remotePdo->prepare('SELECT * FROM cursos WHERE id = ? LIMIT 1');
    $findByTitle = $remotePdo->prepare('SELECT * FROM cursos WHERE creado_por = ? AND titulo = ? LIMIT 1');
    $deleteCourse = $remotePdo->prepare('DELETE FROM cursos WHERE id = ?');

    $existingRemote = null;
    if ($remoteCourseId) {
        $fetchRemoteCourse->execute([$remoteCourseId]);
        $existingRemote = $fetchRemoteCourse->fetch();
        if (!$existingRemote) {
            throw new RuntimeException('No se encontro el curso remoto objetivo ' . $remoteCourseId);
        }
    } else {
        $findByTitle->execute([(int) $remoteTeacher['id'], $localCourse['titulo']]);
        $existingRemote = $findByTitle->fetch();
        if ($existingRemote) {
            $remoteCourseId = (int) $existingRemote['id'];
        }
    }

    $preserveRemoteTitle = !empty($options['preserve_remote_title']);
    $preserveRemotePublication = !empty($options['preserve_remote_publication']);
    $explicitInsertId = !empty($options['preserve_remote_id']) && $remoteCourseId;

    $mediaMap = [];
    $sourceMedia = $bundle['media'];

    $courseRow = $localCourse;
    $courseRow['instancia_id'] = (int) $remoteTeacher['instancia_id'];
    $courseRow['creado_por'] = (int) $remoteTeacher['id'];

    if ($existingRemote && $preserveRemoteTitle) {
        $courseRow['titulo'] = $existingRemote['titulo'];
    }

    if ($existingRemote && $preserveRemotePublication) {
        foreach (['es_publico', 'requiere_codigo', 'codigo_acceso', 'tipo_codigo', 'inscripcion_abierta', 'estado'] as $field) {
            $courseRow[$field] = $existingRemote[$field] ?? $courseRow[$field] ?? null;
        }
    }

    if (!empty($localCourse['portada_media_id'])) {
        $courseRow['portada_media_id'] = course_sync_map_media_reference(
            $remotePdo,
            $sourceMedia,
            (int) $localCourse['portada_media_id'],
            $remoteTeacher,
            $mediaMap
        );
    } else {
        $courseRow['portada_media_id'] = null;
    }

    $remotePdo->beginTransaction();

    if ($existingRemote) {
        $deleteCourse->execute([(int) $existingRemote['id']]);
    }

    $courseFields = [
        'instancia_id', 'plantilla_pensum_id', 'creado_por', 'titulo', 'descripcion',
        'idioma', 'idioma_objetivo', 'idioma_base', 'idioma_ensenanza',
        'portada_media_id', 'nivel_cefr', 'nivel_cefr_desde', 'nivel_cefr_hasta',
        'modalidad', 'fecha_inicio', 'fecha_fin', 'duracion_semanas',
        'es_publico', 'requiere_codigo', 'codigo_acceso', 'tipo_codigo',
        'inscripcion_abierta', 'fecha_cierre_inscripcion', 'max_estudiantes', 'estado',
        'notificar_profesor_completada', 'notificar_profesor_atascado',
    ];

    if ($explicitInsertId) {
        array_unshift($courseFields, 'id');
    }

    $columnsSql = implode(', ', $courseFields);
    $valuesSql = implode(', ', array_fill(0, count($courseFields), '?'));
    $insertCourse = $remotePdo->prepare("INSERT INTO cursos ($columnsSql) VALUES ($valuesSql)");

    $courseValues = [];
    foreach ($courseFields as $field) {
        $courseValues[] = $field === 'id' ? $remoteCourseId : ($courseRow[$field] ?? null);
    }
    $insertCourse->execute($courseValues);

    $newCourseId = $explicitInsertId ? $remoteCourseId : (int) $remotePdo->lastInsertId();

    $insertLesson = $remotePdo->prepare('
        INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $insertTheory = $remotePdo->prepare('
        INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, orden, duracion_minutos, es_interactivo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $insertBlock = $remotePdo->prepare('
        INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $insertActivity = $remotePdo->prepare('
        INSERT INTO actividades (
            leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
            puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $insertOption = $remotePdo->prepare('
        INSERT INTO opciones_multiples (actividad_id, opcion_texto, es_correcta)
        VALUES (?, ?, ?)
    ');

    $lessonCount = 0;
    $theoryCount = 0;
    $blockCount = 0;
    $activityCount = 0;
    $optionsCount = 0;

    foreach ($bundle['lessons'] as $lessonBlock) {
        $lesson = $lessonBlock['lesson'];
        $insertLesson->execute([
            $newCourseId,
            $lesson['titulo'] ?? '',
            $lesson['descripcion'] ?? null,
            (int) ($lesson['orden'] ?? 0),
            $lesson['duracion_minutos'] ?? null,
            (int) ($lesson['es_obligatoria'] ?? 1),
            $lesson['estado'] ?? 'borrador',
        ]);
        $newLessonId = (int) $remotePdo->lastInsertId();
        $lessonCount++;

        foreach ($lessonBlock['theories'] as $theoryBlock) {
            $theory = $theoryBlock['theory'];
            $insertTheory->execute([
                $newLessonId,
                $theory['titulo'] ?? '',
                $theory['contenido'] ?? '',
                $theory['tipo_contenido'] ?? 'texto',
                (int) ($theory['orden'] ?? 0),
                $theory['duracion_minutos'] ?? null,
                (int) ($theory['es_interactivo'] ?? 0),
            ]);
            $newTheoryId = (int) $remotePdo->lastInsertId();
            $theoryCount++;

            foreach ($theoryBlock['blocks'] as $block) {
                $newMediaId = null;
                if (!empty($block['media_id'])) {
                    $newMediaId = course_sync_map_media_reference(
                        $remotePdo,
                        $sourceMedia,
                        (int) $block['media_id'],
                        $remoteTeacher,
                        $mediaMap
                    );
                }

                $insertBlock->execute([
                    $newTheoryId,
                    $block['tipo_bloque'] ?? 'explicacion',
                    $block['titulo'] ?? null,
                    $block['contenido'] ?? null,
                    $block['idioma_bloque'] ?? null,
                    (int) ($block['tts_habilitado'] ?? 0),
                    $newMediaId,
                    (int) ($block['orden'] ?? 0),
                ]);
                $blockCount++;
            }
        }

        foreach ($lessonBlock['activities'] as $activityBlock) {
            $activity = $activityBlock['activity'];
            $content = course_sync_remap_activity_content($activity['contenido'] ?? null, $mediaMap);

            $insertActivity->execute([
                $newLessonId,
                $activity['titulo'] ?? '',
                $activity['descripcion'] ?? null,
                $activity['tipo_actividad'] ?? '',
                $activity['instrucciones'] ?? null,
                $content,
                (int) ($activity['puntos_maximos'] ?? 0),
                $activity['tiempo_limite_minutos'] ?? null,
                (int) ($activity['intentos_permitidos'] ?? 3),
                (int) ($activity['es_calificable'] ?? 1),
                (int) ($activity['orden'] ?? 0),
                $activity['estado'] ?? 'activa',
            ]);
            $newActivityId = (int) $remotePdo->lastInsertId();
            $activityCount++;

            foreach ($activityBlock['options'] as $option) {
                $insertOption->execute([
                    $newActivityId,
                    $option['texto'] ?? '',
                    (int) ($option['es_correcta'] ?? 0),
                ]);
                $optionsCount++;
            }
        }
    }

    $remotePdo->commit();

    return [
        'local_course_id' => $localCourseId,
        'remote_course_id' => $newCourseId,
        'remote_teacher_email' => $remoteTeacher['email'],
        'title' => $courseRow['titulo'] ?? '',
        'lessons' => $lessonCount,
        'theories' => $theoryCount,
        'blocks' => $blockCount,
        'activities' => $activityCount,
        'options' => $optionsCount,
        'media_mapped' => count(array_filter($mediaMap, static fn($id): bool => !empty($id))),
        'replaced_remote_course_id' => $existingRemote['id'] ?? null,
        'preserved_remote_title' => $preserveRemoteTitle,
        'preserved_remote_publication' => $preserveRemotePublication,
    ];
}
