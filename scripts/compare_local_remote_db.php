<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$remoteConfigPath = $root . '/config/database.hosting.php';

$localConfig = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '',
    'name' => 'adapa',
    'port' => 3306,
    'charset' => 'utf8mb4',
];

if (!is_file($remoteConfigPath)) {
    fwrite(STDERR, "Remote config not found.\n");
    exit(1);
}

$remoteConfig = require $remoteConfigPath;

function connectPdo(array $config): PDO
{
    $host = $config['host'] ?? '127.0.0.1';
    $port = (int) ($config['port'] ?? 3306);
    $db = $config['name'] ?? '';
    $user = $config['user'] ?? '';
    $pass = $config['pass'] ?? '';
    $charset = $config['charset'] ?? 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $db, $charset);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function fetchTables(PDO $pdo): array
{
    $rows = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
    return array_map(static fn(array $row): string => (string) $row[0], $rows);
}

function fetchColumns(PDO $pdo, string $table): array
{
    $stmt = $pdo->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
    $columns = [];
    foreach ($stmt->fetchAll() as $row) {
        $columns[$row['Field']] = [
            'Type' => $row['Type'],
            'Null' => $row['Null'],
            'Key' => $row['Key'],
            'Default' => $row['Default'],
            'Extra' => $row['Extra'],
        ];
    }
    return $columns;
}

function fetchCount(PDO $pdo, string $table): int
{
    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM `' . str_replace('`', '``', $table) . '`');
    $row = $stmt->fetch();
    return (int) ($row['total'] ?? 0);
}

function printSection(string $title): void
{
    echo "\n" . $title . "\n";
    echo str_repeat('-', strlen($title)) . "\n";
}

$localPdo = connectPdo($localConfig);
$remotePdo = connectPdo($remoteConfig);

$localTables = fetchTables($localPdo);
$remoteTables = fetchTables($remotePdo);

sort($localTables);
sort($remoteTables);

$missingInRemote = array_values(array_diff($localTables, $remoteTables));
$missingInLocal = array_values(array_diff($remoteTables, $localTables));
$sharedTables = array_values(array_intersect($localTables, $remoteTables));

printSection('Database Compare');
echo 'Local:  ' . ($localConfig['name'] ?? 'unknown') . PHP_EOL;
echo 'Remote: ' . ($remoteConfig['name'] ?? 'unknown') . ' @ ' . ($remoteConfig['host'] ?? 'unknown') . PHP_EOL;

printSection('Missing In Remote');
if (empty($missingInRemote)) {
    echo "None\n";
} else {
    foreach ($missingInRemote as $table) {
        echo '- ' . $table . PHP_EOL;
    }
}

printSection('Missing In Local');
if (empty($missingInLocal)) {
    echo "None\n";
} else {
    foreach ($missingInLocal as $table) {
        echo '- ' . $table . PHP_EOL;
    }
}

$tableDiffs = [];
foreach ($sharedTables as $table) {
    $localColumns = fetchColumns($localPdo, $table);
    $remoteColumns = fetchColumns($remotePdo, $table);

    $missingColumnsRemote = array_values(array_diff(array_keys($localColumns), array_keys($remoteColumns)));
    $missingColumnsLocal = array_values(array_diff(array_keys($remoteColumns), array_keys($localColumns)));
    $changedColumns = [];

    foreach (array_intersect(array_keys($localColumns), array_keys($remoteColumns)) as $column) {
        if ($localColumns[$column] !== $remoteColumns[$column]) {
            $changedColumns[$column] = [
                'local' => $localColumns[$column],
                'remote' => $remoteColumns[$column],
            ];
        }
    }

    if (!empty($missingColumnsRemote) || !empty($missingColumnsLocal) || !empty($changedColumns)) {
        $tableDiffs[$table] = [
            'missing_remote' => $missingColumnsRemote,
            'missing_local' => $missingColumnsLocal,
            'changed' => $changedColumns,
        ];
    }
}

printSection('Schema Differences');
if (empty($tableDiffs)) {
    echo "None\n";
} else {
    foreach ($tableDiffs as $table => $diff) {
        echo '* ' . $table . PHP_EOL;
        foreach ($diff['missing_remote'] as $column) {
            echo '  - missing in remote: ' . $column . PHP_EOL;
        }
        foreach ($diff['missing_local'] as $column) {
            echo '  - missing in local: ' . $column . PHP_EOL;
        }
        foreach ($diff['changed'] as $column => $change) {
            echo '  - changed: ' . $column . PHP_EOL;
            echo '    local:  ' . json_encode($change['local'], JSON_UNESCAPED_SLASHES) . PHP_EOL;
            echo '    remote: ' . json_encode($change['remote'], JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }
    }
}

$countTables = [
    'usuarios',
    'cursos',
    'lecciones',
    'teoria',
    'contenido_bloques',
    'actividades',
    'media_recursos',
    'lesson_issue_reports',
    'lesson_issue_report_notes',
    'admin_activity_log',
];

printSection('Row Counts');
foreach ($countTables as $table) {
    $localCount = in_array($table, $localTables, true) ? fetchCount($localPdo, $table) : null;
    $remoteCount = in_array($table, $remoteTables, true) ? fetchCount($remotePdo, $table) : null;

    echo sprintf(
        "- %-24s local=%-6s remote=%-6s\n",
        $table,
        $localCount === null ? 'N/A' : (string) $localCount,
        $remoteCount === null ? 'N/A' : (string) $remoteCount
    );
}
