<?php

declare(strict_types=1);

require_once __DIR__ . '/german_course_master_blueprint.php';

function german_master_table_exists(PDO $pdo, string $table): bool
{
    static $cache = [];
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    $cache[$table] = ((int) $stmt->fetchColumn()) > 0;

    return $cache[$table];
}

function german_master_fetch_ids(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_values(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
}

function german_master_delete_by_ids(PDO $pdo, string $table, string $column, array $ids): void
{
    if (empty($ids) || !german_master_table_exists($pdo, $table)) {
        return;
    }

    $ids = array_values(array_unique(array_map('intval', $ids)));
    $chunks = array_chunk($ids, 250);

    foreach ($chunks as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$column} IN ({$placeholders})");
        $stmt->execute($chunk);
    }
}

function german_master_support_blocks_for_theory(string $lessonTitle, string $theoryTitle): array
{
    static $support = null;
    if ($support === null) {
        $support = german_expand_existing_theory_support();
        if (function_exists('german_master_additional_theory_support')) {
            $support = array_merge($support, german_master_additional_theory_support());
        }
    }

    $lessonNeedle = mb_strtolower(trim($lessonTitle), 'UTF-8');
    $theoryNeedle = mb_strtolower(trim($theoryTitle), 'UTF-8');

    $blocks = [];

    foreach ($support as $entry) {
        $lessonMatch = false;
        foreach ((array) ($entry['lesson_aliases'] ?? []) as $alias) {
            if (mb_strtolower(trim((string) $alias), 'UTF-8') === $lessonNeedle) {
                $lessonMatch = true;
                break;
            }
        }

        if (!$lessonMatch) {
            continue;
        }

        foreach ((array) ($entry['theory_aliases'] ?? []) as $alias) {
            if (mb_strtolower(trim((string) $alias), 'UTF-8') === $theoryNeedle) {
                $blocks = array_merge($blocks, (array) ($entry['blocks'] ?? []));
                break;
            }
        }
    }

    if (function_exists('german_master_dialogue_blocks_for_theory')) {
        $blocks = array_merge($blocks, german_master_dialogue_blocks_for_theory($lessonTitle, $theoryTitle));
    }

    return german_expand_restore_umlauts_recursive($blocks);
}

function german_master_delete_course_content(PDO $pdo, int $courseId): array
{
    $lessonIds = german_master_fetch_ids($pdo, 'SELECT id FROM lecciones WHERE curso_id = ?', [$courseId]);
    $theoryIds = german_master_fetch_ids($pdo, '
        SELECT t.id
        FROM teoria t
        INNER JOIN lecciones l ON l.id = t.leccion_id
        WHERE l.curso_id = ?
    ', [$courseId]);
    $activityIds = german_master_fetch_ids($pdo, '
        SELECT a.id
        FROM actividades a
        INNER JOIN lecciones l ON l.id = a.leccion_id
        WHERE l.curso_id = ?
    ', [$courseId]);

    if (!empty($lessonIds) && german_master_table_exists($pdo, 'lesson_issue_reports')) {
        $reportIds = german_master_fetch_ids(
            $pdo,
            'SELECT id FROM lesson_issue_reports WHERE leccion_id IN (' . implode(',', array_fill(0, count($lessonIds), '?')) . ')',
            $lessonIds
        );
        german_master_delete_by_ids($pdo, 'lesson_issue_report_notes', 'report_id', $reportIds);
        german_master_delete_by_ids($pdo, 'lesson_issue_reports', 'id', $reportIds);
    }

    german_master_delete_by_ids($pdo, 'progreso_teoria', 'teoria_id', $theoryIds);
    german_master_delete_by_ids($pdo, 'contenido_bloques', 'teoria_id', $theoryIds);
    german_master_delete_by_ids($pdo, 'respuestas', 'actividad_id', $activityIds);
    german_master_delete_by_ids($pdo, 'intentos_actividades', 'actividad_id', $activityIds);
    german_master_delete_by_ids($pdo, 'respuestas_actividades', 'actividad_id', $activityIds);
    german_master_delete_by_ids($pdo, 'opciones_multiples', 'actividad_id', $activityIds);
    german_master_delete_by_ids($pdo, 'progreso_lecciones', 'leccion_id', $lessonIds);
    german_master_delete_by_ids($pdo, 'actividades', 'id', $activityIds);
    german_master_delete_by_ids($pdo, 'teoria', 'id', $theoryIds);
    german_master_delete_by_ids($pdo, 'lecciones', 'id', $lessonIds);

    return [
        'old_lessons' => count($lessonIds),
        'old_theories' => count($theoryIds),
        'old_activities' => count($activityIds),
    ];
}

function german_master_upsert_course(PDO $pdo, array $course, ?int $courseId = null): int
{
    $payload = [
        'instancia_id' => (int) ($course['instancia_id'] ?? 1),
        'creado_por' => (int) ($course['creado_por'] ?? 13),
        'titulo' => (string) ($course['titulo'] ?? ''),
        'descripcion' => (string) ($course['descripcion'] ?? ''),
        'idioma' => (string) ($course['idioma'] ?? ''),
        'idioma_objetivo' => (string) ($course['idioma_objetivo'] ?? ''),
        'idioma_base' => (string) ($course['idioma_base'] ?? ($course['idioma_ensenanza'] ?? 'espanol')),
        'idioma_ensenanza' => (string) ($course['idioma_ensenanza'] ?? 'espanol'),
        'nivel_cefr' => (string) ($course['nivel_cefr'] ?? 'A1'),
        'nivel_cefr_desde' => (string) ($course['nivel_cefr_desde'] ?? 'A1'),
        'nivel_cefr_hasta' => (string) ($course['nivel_cefr_hasta'] ?? 'C1'),
        'modalidad' => (string) ($course['modalidad'] ?? 'perpetuo'),
        'fecha_inicio' => $course['fecha_inicio'] ?? date('Y-m-d'),
        'fecha_fin' => $course['fecha_fin'] ?? null,
        'duracion_semanas' => (int) ($course['duracion_semanas'] ?? 104),
        'es_publico' => (int) ($course['es_publico'] ?? 1),
        'requiere_codigo' => (int) ($course['requiere_codigo'] ?? 0),
        'codigo_acceso' => $course['codigo_acceso'] ?? null,
        'tipo_codigo' => $course['tipo_codigo'] ?? null,
        'inscripcion_abierta' => (int) ($course['inscripcion_abierta'] ?? 1),
        'fecha_cierre_inscripcion' => $course['fecha_cierre_inscripcion'] ?? null,
        'max_estudiantes' => (int) ($course['max_estudiantes'] ?? 1000),
        'estado' => (string) ($course['estado'] ?? 'activo'),
        'estado_editorial' => (string) ($course['estado_editorial'] ?? 'publicado'),
        'notificar_profesor_completada' => (int) ($course['notificar_profesor_completada'] ?? 1),
        'notificar_profesor_atascado' => (int) ($course['notificar_profesor_atascado'] ?? 1),
    ];

    if ($courseId !== null) {
        $payload['id'] = $courseId;
        $stmt = $pdo->prepare('
            UPDATE cursos SET
                instancia_id = :instancia_id,
                creado_por = :creado_por,
                titulo = :titulo,
                descripcion = :descripcion,
                idioma = :idioma,
                idioma_objetivo = :idioma_objetivo,
                idioma_base = :idioma_base,
                idioma_ensenanza = :idioma_ensenanza,
                nivel_cefr = :nivel_cefr,
                nivel_cefr_desde = :nivel_cefr_desde,
                nivel_cefr_hasta = :nivel_cefr_hasta,
                modalidad = :modalidad,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                duracion_semanas = :duracion_semanas,
                es_publico = :es_publico,
                requiere_codigo = :requiere_codigo,
                codigo_acceso = :codigo_acceso,
                tipo_codigo = :tipo_codigo,
                inscripcion_abierta = :inscripcion_abierta,
                fecha_cierre_inscripcion = :fecha_cierre_inscripcion,
                max_estudiantes = :max_estudiantes,
                estado = :estado,
                estado_editorial = :estado_editorial,
                notificar_profesor_completada = :notificar_profesor_completada,
                notificar_profesor_atascado = :notificar_profesor_atascado
            WHERE id = :id
        ');
        $stmt->execute($payload);

        return $courseId;
    }

    $stmt = $pdo->prepare('
        INSERT INTO cursos (
            instancia_id, creado_por, titulo, descripcion, idioma, idioma_objetivo, idioma_base, idioma_ensenanza,
            nivel_cefr, nivel_cefr_desde, nivel_cefr_hasta, modalidad, fecha_inicio, fecha_fin, duracion_semanas,
            es_publico, requiere_codigo, codigo_acceso, tipo_codigo, inscripcion_abierta, fecha_cierre_inscripcion,
            max_estudiantes, estado, estado_editorial, notificar_profesor_completada, notificar_profesor_atascado
        ) VALUES (
            :instancia_id, :creado_por, :titulo, :descripcion, :idioma, :idioma_objetivo, :idioma_base, :idioma_ensenanza,
            :nivel_cefr, :nivel_cefr_desde, :nivel_cefr_hasta, :modalidad, :fecha_inicio, :fecha_fin, :duracion_semanas,
            :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :inscripcion_abierta, :fecha_cierre_inscripcion,
            :max_estudiantes, :estado, :estado_editorial, :notificar_profesor_completada, :notificar_profesor_atascado
        )
    ');
    $stmt->execute($payload);

    return (int) $pdo->lastInsertId();
}

function german_master_insert_course_content(PDO $pdo, int $courseId, array $blueprint): array
{
    $insertLesson = $pdo->prepare('
        INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado, estado_editorial)
        VALUES (?, ?, ?, ?, ?, 1, "publicada", "publicado")
    ');
    $insertTheory = $pdo->prepare('
        INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, duracion_minutos, orden, es_interactivo)
        VALUES (?, ?, ?, "texto", ?, ?, 0)
    ');
    $insertBlock = $pdo->prepare('
        INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden)
        VALUES (?, ?, ?, ?, ?, ?, NULL, ?)
    ');
    $insertActivity = $pdo->prepare('
        INSERT INTO actividades (
            leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido,
            puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 3, 1, ?, "activa")
    ');

    $lessonCount = 0;
    $theoryCount = 0;
    $activityCount = 0;
    $blockCount = 0;

    foreach ($blueprint['lessons'] as $lesson) {
        $insertLesson->execute([
            $courseId,
            $lesson['titulo'],
            $lesson['descripcion'],
            (int) ($lesson['orden'] ?? 0),
            (int) ($lesson['duracion'] ?? 120),
        ]);
        $lessonId = (int) $pdo->lastInsertId();
        $lessonCount++;

        foreach ((array) ($lesson['teoria'] ?? []) as $theoryIndex => $theory) {
            $theory = german_expand_restore_umlauts_recursive($theory);
            $insertTheory->execute([
                $lessonId,
                $theory['titulo'],
                german_expand_render_theory_html($theory),
                (int) ($theory['duracion'] ?? 15),
                $theoryIndex + 1,
            ]);
            $theoryId = (int) $pdo->lastInsertId();
            $theoryCount++;

            $blocks = german_expand_restore_umlauts_recursive(german_expand_theory_blocks($theory));
            foreach (german_master_support_blocks_for_theory($lesson['titulo'], $theory['titulo']) as $supportBlock) {
                $supportTitle = trim((string) ($supportBlock['titulo'] ?? ''));
                if ($supportTitle !== '' && in_array($supportTitle, ['Escenario de practica', 'Mision express'], true)) {
                    $alreadyExists = false;
                    foreach ($blocks as $existingBlock) {
                        if (trim((string) ($existingBlock['titulo'] ?? '')) === $supportTitle) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                    if ($alreadyExists) {
                        continue;
                    }
                }
                $blocks[] = german_expand_restore_umlauts_recursive($supportBlock);
            }

            foreach ($blocks as $blockIndex => $block) {
                $block = german_expand_restore_umlauts_recursive($block);
                $insertBlock->execute([
                    $theoryId,
                    $block['tipo_bloque'],
                    $block['titulo'],
                    $block['contenido'],
                    $block['idioma_bloque'] ?? 'espanol',
                    (int) ($block['tts_habilitado'] ?? 1),
                    $blockIndex + 1,
                ]);
                $blockCount++;
            }
        }

        foreach ((array) ($lesson['actividades'] ?? []) as $activityIndex => $activity) {
            $activity = german_expand_restore_umlauts_recursive($activity);
            $insertActivity->execute([
                $lessonId,
                $activity['titulo'],
                $activity['descripcion'],
                $activity['tipo'],
                $activity['instrucciones'],
                json_encode($activity['contenido'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                (int) ($activity['puntos'] ?? 10),
                (int) ($activity['tiempo'] ?? 5),
                $activityIndex + 1,
            ]);
            $activityCount++;
        }
    }

    return [
        'lessons' => $lessonCount,
        'theories' => $theoryCount,
        'activities' => $activityCount,
        'blocks' => $blockCount,
    ];
}

function german_master_apply_to_course(PDO $pdo, int $courseId): array
{
    $blueprint = german_course_master_blueprint();

    $pdo->beginTransaction();
    try {
        german_master_upsert_course($pdo, $blueprint['course'], $courseId);
        $deleted = german_master_delete_course_content($pdo, $courseId);
        $inserted = german_master_insert_course_content($pdo, $courseId, $blueprint);
        $pdo->commit();

        return array_merge([
            'course_id' => $courseId,
            'title' => $blueprint['course']['titulo'],
        ], $deleted, $inserted);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function german_master_seed_new_course(PDO $pdo, ?int $studentId = 14): array
{
    $blueprint = german_course_master_blueprint();

    $pdo->beginTransaction();
    try {
        $courseId = german_master_upsert_course($pdo, $blueprint['course'], null);
        $inserted = german_master_insert_course_content($pdo, $courseId, $blueprint);

        if ($studentId !== null && german_master_table_exists($pdo, 'inscripciones')) {
            $stmt = $pdo->prepare('INSERT INTO inscripciones (curso_id, estudiante_id) VALUES (?, ?)');
            $stmt->execute([$courseId, $studentId]);
        }

        $pdo->commit();

        return array_merge([
            'course_id' => $courseId,
            'title' => $blueprint['course']['titulo'],
            'student_id' => $studentId,
        ], $inserted);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
