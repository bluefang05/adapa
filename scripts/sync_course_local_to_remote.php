<?php

declare(strict_types=1);

require_once __DIR__ . '/course_sync_lib.php';

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('Este script debe ejecutarse desde CLI.');
}

if ($argc < 2) {
    throw new RuntimeException(
        'Uso: php scripts/sync_course_local_to_remote.php <local_course_id> [--remote-course-id=16] [--teacher-email=profesor@adapa.edu] [--preserve-remote-title] [--preserve-remote-publication] [--preserve-remote-id]'
    );
}

$localCourseId = (int) $argv[1];
if ($localCourseId <= 0) {
    throw new RuntimeException('El primer argumento debe ser un id de curso local valido.');
}

$options = [];
for ($i = 2; $i < $argc; $i++) {
    $arg = $argv[$i];
    if (str_starts_with($arg, '--remote-course-id=')) {
        $options['remote_course_id'] = (int) substr($arg, strlen('--remote-course-id='));
        continue;
    }
    if (str_starts_with($arg, '--teacher-email=')) {
        $options['teacher_email'] = substr($arg, strlen('--teacher-email='));
        continue;
    }
    if ($arg === '--preserve-remote-title') {
        $options['preserve_remote_title'] = true;
        continue;
    }
    if ($arg === '--preserve-remote-publication') {
        $options['preserve_remote_publication'] = true;
        continue;
    }
    if ($arg === '--preserve-remote-id') {
        $options['preserve_remote_id'] = true;
        continue;
    }
}

$localPdo = course_sync_connect(course_sync_local_config());
$remotePdo = course_sync_connect(course_sync_remote_config());

$result = course_sync_sync_local_course_to_remote($localPdo, $remotePdo, $localCourseId, $options);
course_sync_cli_write(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
