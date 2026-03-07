<?php

declare(strict_types=1);

require_once __DIR__ . '/course_sync_lib.php';

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('Este script debe ejecutarse desde CLI.');
}

if ($argc < 3) {
    throw new RuntimeException('Uso: php scripts/diff_course_local_remote.php <local_course_id> <remote_course_id>');
}

$localCourseId = (int) $argv[1];
$remoteCourseId = (int) $argv[2];
if ($localCourseId <= 0 || $remoteCourseId <= 0) {
    throw new RuntimeException('Los ids deben ser enteros positivos.');
}

function diffSection(string $title): void
{
    course_sync_cli_write("\n" . $title . "\n");
    course_sync_cli_write(str_repeat('-', strlen($title)) . "\n");
}

function normalizeLessonItem(array $lessonBlock): array
{
    $lesson = $lessonBlock['lesson'];
    return [
        'titulo' => $lesson['titulo'] ?? '',
        'descripcion' => $lesson['descripcion'] ?? '',
        'duracion_minutos' => $lesson['duracion_minutos'] ?? null,
        'estado' => $lesson['estado'] ?? null,
        'theory_titles' => array_map(
            static fn(array $theoryBlock): string => (string) ($theoryBlock['theory']['titulo'] ?? ''),
            $lessonBlock['theories'] ?? []
        ),
        'activity_titles' => array_map(
            static fn(array $activityBlock): string => (string) ($activityBlock['activity']['titulo'] ?? ''),
            $lessonBlock['activities'] ?? []
        ),
        'theory_hashes' => array_map(
            static fn(array $theoryBlock): string => hash('sha1', json_encode([
                'titulo' => $theoryBlock['theory']['titulo'] ?? '',
                'contenido' => $theoryBlock['theory']['contenido'] ?? '',
                'bloques' => array_map(static fn(array $block): array => [
                    'tipo_bloque' => $block['tipo_bloque'] ?? '',
                    'titulo' => $block['titulo'] ?? '',
                    'contenido' => $block['contenido'] ?? '',
                    'media' => $block['ruta_archivo'] ?? null,
                ], $theoryBlock['blocks'] ?? []),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            $lessonBlock['theories'] ?? []
        ),
        'activity_hashes' => array_map(
            static fn(array $activityBlock): string => hash('sha1', json_encode([
                'titulo' => $activityBlock['activity']['titulo'] ?? '',
                'tipo' => $activityBlock['activity']['tipo_actividad'] ?? '',
                'contenido' => course_sync_decode_json($activityBlock['activity']['contenido'] ?? null) ?? ($activityBlock['activity']['contenido'] ?? ''),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            $lessonBlock['activities'] ?? []
        ),
    ];
}

$localPdo = course_sync_connect(course_sync_local_config());
$remotePdo = course_sync_connect(course_sync_remote_config());
$localBundle = course_sync_fetch_course_bundle($localPdo, $localCourseId);
$remoteBundle = course_sync_fetch_course_bundle($remotePdo, $remoteCourseId);

if (!$localBundle) {
    throw new RuntimeException('No existe el curso local ' . $localCourseId);
}
if (!$remoteBundle) {
    throw new RuntimeException('No existe el curso remoto ' . $remoteCourseId);
}

$localCourse = $localBundle['course'];
$remoteCourse = $remoteBundle['course'];

diffSection('Course Shell');
$fields = [
    'titulo',
    'descripcion',
    'idioma',
    'idioma_objetivo',
    'idioma_base',
    'idioma_ensenanza',
    'nivel_cefr',
    'nivel_cefr_desde',
    'nivel_cefr_hasta',
    'modalidad',
    'duracion_semanas',
    'es_publico',
    'inscripcion_abierta',
    'estado',
];

foreach ($fields as $field) {
    $localValue = $localCourse[$field] ?? null;
    $remoteValue = $remoteCourse[$field] ?? null;
    if ((string) $localValue === (string) $remoteValue) {
        continue;
    }
    course_sync_cli_write(sprintf(
        "- %s\n  local:  %s\n  remote: %s\n",
        $field,
        json_encode($localValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        json_encode($remoteValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ));
}

diffSection('Lessons By Order');
$localLessons = $localBundle['lessons'];
$remoteLessons = $remoteBundle['lessons'];
$max = max(count($localLessons), count($remoteLessons));

for ($i = 0; $i < $max; $i++) {
    $localLesson = $localLessons[$i] ?? null;
    $remoteLesson = $remoteLessons[$i] ?? null;

    if (!$localLesson || !$remoteLesson) {
        course_sync_cli_write(sprintf(
            "- orden %d | local=%s remote=%s\n",
            $i + 1,
            $localLesson ? ($localLesson['lesson']['titulo'] ?? '[sin titulo]') : '[faltante]',
            $remoteLesson ? ($remoteLesson['lesson']['titulo'] ?? '[sin titulo]') : '[faltante]'
        ));
        continue;
    }

    $localNormalized = normalizeLessonItem($localLesson);
    $remoteNormalized = normalizeLessonItem($remoteLesson);
    $lessonDiffs = [];

    foreach (['titulo', 'descripcion', 'duracion_minutos', 'estado'] as $field) {
        if (($localNormalized[$field] ?? null) !== ($remoteNormalized[$field] ?? null)) {
            $lessonDiffs[] = sprintf(
                "%s local=%s remote=%s",
                $field,
                json_encode($localNormalized[$field] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                json_encode($remoteNormalized[$field] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }
    }

    if (count($localNormalized['theory_titles']) !== count($remoteNormalized['theory_titles'])) {
        $lessonDiffs[] = sprintf(
            'teorias local=%d remote=%d',
            count($localNormalized['theory_titles']),
            count($remoteNormalized['theory_titles'])
        );
    }

    if (count($localNormalized['activity_titles']) !== count($remoteNormalized['activity_titles'])) {
        $lessonDiffs[] = sprintf(
            'actividades local=%d remote=%d',
            count($localNormalized['activity_titles']),
            count($remoteNormalized['activity_titles'])
        );
    }

    if ($localNormalized['theory_hashes'] !== $remoteNormalized['theory_hashes']) {
        $lessonDiffs[] = 'teoria_contenido=diferente';
    }

    if ($localNormalized['activity_hashes'] !== $remoteNormalized['activity_hashes']) {
        $lessonDiffs[] = 'actividad_contenido=diferente';
    }

    if (empty($lessonDiffs)) {
        course_sync_cli_write(sprintf("- orden %d | %s | sin diferencias visibles\n", $i + 1, $localNormalized['titulo']));
        continue;
    }

    course_sync_cli_write(sprintf("- orden %d | %s\n", $i + 1, $localNormalized['titulo']));
    foreach ($lessonDiffs as $diff) {
        course_sync_cli_write('  * ' . $diff . PHP_EOL);
    }
}

diffSection('Media Summary');
$localMedia = array_map(static fn(array $media): string => ($media['titulo'] ?? '') . '|' . ($media['ruta_archivo'] ?? ''), $localBundle['media'] ?? []);
$remoteMedia = array_map(static fn(array $media): string => ($media['titulo'] ?? '') . '|' . ($media['ruta_archivo'] ?? ''), $remoteBundle['media'] ?? []);

$localOnlyMedia = array_values(array_diff($localMedia, $remoteMedia));
$remoteOnlyMedia = array_values(array_diff($remoteMedia, $localMedia));

course_sync_cli_write('Local only media: ' . count($localOnlyMedia) . PHP_EOL);
foreach ($localOnlyMedia as $item) {
    course_sync_cli_write('  - ' . $item . PHP_EOL);
}
course_sync_cli_write('Remote only media: ' . count($remoteOnlyMedia) . PHP_EOL);
foreach ($remoteOnlyMedia as $item) {
    course_sync_cli_write('  - ' . $item . PHP_EOL);
}
