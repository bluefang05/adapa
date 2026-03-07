<?php

declare(strict_types=1);

require_once __DIR__ . '/course_sync_lib.php';

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('Este script debe ejecutarse desde CLI.');
}

function printSection(string $title): void
{
    course_sync_cli_write("\n" . $title . "\n");
    course_sync_cli_write(str_repeat('-', strlen($title)) . "\n");
}

$remoteConfig = course_sync_remote_config();
$localPdo = course_sync_connect(course_sync_local_config());
$remotePdo = course_sync_connect($remoteConfig);

$localInventory = course_sync_fetch_course_inventory($localPdo);
$remoteInventory = course_sync_fetch_course_inventory($remotePdo);

$remoteExact = [];
foreach ($remoteInventory as $row) {
    $remoteExact[($row['creador_email'] ?? '') . '|' . ($row['titulo'] ?? '')] = $row;
}

$matchedRemoteIds = [];
$exactMatches = [];
$probableMatches = [];
$localOnly = [];

foreach ($localInventory as $localRow) {
    $exactKey = ($localRow['creador_email'] ?? '') . '|' . ($localRow['titulo'] ?? '');
    if (isset($remoteExact[$exactKey])) {
        $remoteRow = $remoteExact[$exactKey];
        $matchedRemoteIds[(int) $remoteRow['id']] = true;
        $exactMatches[] = [$localRow, $remoteRow];
        continue;
    }

    $best = null;
    $bestScore = 0.0;
    foreach ($remoteInventory as $remoteRow) {
        if (isset($matchedRemoteIds[(int) $remoteRow['id']])) {
            continue;
        }
        if (($localRow['creador_email'] ?? '') !== ($remoteRow['creador_email'] ?? '')) {
            continue;
        }

        $score = course_sync_title_similarity((string) $localRow['titulo'], (string) $remoteRow['titulo']);
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $remoteRow;
        }
    }

    if ($best && $bestScore >= 0.5) {
        $matchedRemoteIds[(int) $best['id']] = true;
        $probableMatches[] = [$localRow, $best, $bestScore];
        continue;
    }

    $localOnly[] = $localRow;
}

$remoteOnly = [];
foreach ($remoteInventory as $remoteRow) {
    if (!isset($matchedRemoteIds[(int) $remoteRow['id']])) {
        $remoteOnly[] = $remoteRow;
    }
}

printSection('Course Inventory Compare');
course_sync_cli_write('Local:  adapa' . PHP_EOL);
course_sync_cli_write('Remote: ' . $remoteConfig['name'] . ' @ ' . $remoteConfig['host'] . PHP_EOL);

printSection('Exact Title Matches');
if (empty($exactMatches)) {
    course_sync_cli_write("None\n");
} else {
    foreach ($exactMatches as [$localRow, $remoteRow]) {
        $same = ($localRow['fingerprint'] ?? '') === ($remoteRow['fingerprint'] ?? '');
        $contentSame = ($localRow['content_fingerprint'] ?? '') === ($remoteRow['content_fingerprint'] ?? '');
        course_sync_cli_write(sprintf(
            "- %s | local=%d remote=%d | full=%s content=%s | L:%d/%d/%d R:%d/%d/%d\n",
            $localRow['titulo'],
            (int) $localRow['id'],
            (int) $remoteRow['id'],
            $same ? 'same' : 'different',
            $contentSame ? 'same' : 'different',
            (int) $localRow['total_lecciones'],
            (int) $localRow['total_teorias'],
            (int) $localRow['total_actividades'],
            (int) $remoteRow['total_lecciones'],
            (int) $remoteRow['total_teorias'],
            (int) $remoteRow['total_actividades']
        ));
    }
}

printSection('Probable Title Matches');
if (empty($probableMatches)) {
    course_sync_cli_write("None\n");
} else {
    foreach ($probableMatches as [$localRow, $remoteRow, $score]) {
        $fullSame = ($localRow['fingerprint'] ?? '') === ($remoteRow['fingerprint'] ?? '');
        $contentSame = ($localRow['content_fingerprint'] ?? '') === ($remoteRow['content_fingerprint'] ?? '');
        course_sync_cli_write(sprintf(
            "- local=%d \"%s\" <-> remote=%d \"%s\" | score=%.2f | full=%s content=%s\n",
            (int) $localRow['id'],
            $localRow['titulo'],
            (int) $remoteRow['id'],
            $remoteRow['titulo'],
            $score,
            $fullSame ? 'same' : 'different',
            $contentSame ? 'same' : 'different'
        ));
    }
}

printSection('Local Only');
if (empty($localOnly)) {
    course_sync_cli_write("None\n");
} else {
    foreach ($localOnly as $row) {
        course_sync_cli_write(sprintf(
            "- %d | %s | %s | %d/%d/%d\n",
            (int) $row['id'],
            $row['titulo'],
            $row['creador_email'] ?? '',
            (int) $row['total_lecciones'],
            (int) $row['total_teorias'],
            (int) $row['total_actividades']
        ));
    }
}

printSection('Remote Only');
if (empty($remoteOnly)) {
    course_sync_cli_write("None\n");
} else {
    foreach ($remoteOnly as $row) {
        course_sync_cli_write(sprintf(
            "- %d | %s | %s | %d/%d/%d\n",
            (int) $row['id'],
            $row['titulo'],
            $row['creador_email'] ?? '',
            (int) $row['total_lecciones'],
            (int) $row['total_teorias'],
            (int) $row['total_actividades']
        ));
    }
}
