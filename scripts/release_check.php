<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$scriptsDir = __DIR__;
$php = PHP_BINARY ?: 'php';
$baseUrl = 'http://localhost/adapa';
$includeRemoteAudit = in_array('--remote-audit', $argv, true);

function runCommand(array $command, string $workingDir): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $workingDir);
    if (!is_resource($process)) {
        throw new RuntimeException('Could not start process: ' . implode(' ', $command));
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return [
        'exit_code' => $exitCode,
        'stdout' => (string) $stdout,
        'stderr' => (string) $stderr,
    ];
}

function printSection(string $title): void
{
    echo PHP_EOL . $title . PHP_EOL;
    echo str_repeat('-', strlen($title)) . PHP_EOL;
}

$checks = [
    [
        'label' => 'Editorial audit (local)',
        'command' => [$php, $scriptsDir . '/audit_editorial_catalog.php'],
    ],
    [
        'label' => 'Smoke routes (local)',
        'command' => [$php, $scriptsDir . '/smoke_role_routes.php', $baseUrl],
    ],
];

if ($includeRemoteAudit) {
    $checks[] = [
        'label' => 'Editorial audit (remote)',
        'command' => [$php, $scriptsDir . '/audit_editorial_catalog.php', '--remote'],
    ];
}

$failures = [];

echo "ADAPA Release Check\n";
echo "===================\n";
echo 'Working directory: ' . $root . PHP_EOL;
echo 'PHP binary: ' . $php . PHP_EOL;
echo 'Base URL: ' . $baseUrl . PHP_EOL;

foreach ($checks as $check) {
    printSection($check['label']);
    $result = runCommand($check['command'], $root);

    if ($result['stdout'] !== '') {
        echo rtrim($result['stdout']) . PHP_EOL;
    }

    if ($result['stderr'] !== '') {
        echo '[stderr]' . PHP_EOL;
        echo rtrim($result['stderr']) . PHP_EOL;
    }

    if ($result['exit_code'] !== 0) {
        $failures[] = [
            'label' => $check['label'],
            'exit_code' => $result['exit_code'],
        ];
        echo '[FAIL] exit code ' . $result['exit_code'] . PHP_EOL;
    } else {
        echo '[OK]' . PHP_EOL;
    }
}

printSection('Summary');
if (empty($failures)) {
    echo "All release checks passed.\n";
    exit(0);
}

foreach ($failures as $failure) {
    echo '- ' . $failure['label'] . ' failed with exit code ' . $failure['exit_code'] . PHP_EOL;
}

exit(1);
