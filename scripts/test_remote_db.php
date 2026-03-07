<?php

function output_message($message, $isError = false) {
    $message .= PHP_EOL;

    if (PHP_SAPI === 'cli') {
        $stream = $isError ? 'php://stderr' : 'php://stdout';
        file_put_contents($stream, $message);
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
        if ($isError) {
            http_response_code(500);
        }
    }

    echo $message;
}

function output_section($title) {
    output_message('');
    output_message($title);
    output_message(str_repeat('-', strlen($title)));
}

function mask_secret($value) {
    $value = (string) $value;
    $length = strlen($value);

    if ($length <= 4) {
        return str_repeat('*', $length);
    }

    return substr($value, 0, 2) . str_repeat('*', max(0, $length - 4)) . substr($value, -2);
}

$configFile = __DIR__ . '/../config/database.hosting.php';

if (!file_exists($configFile)) {
    output_message('Missing config/database.hosting.php', true);
    exit(1);
}

$config = require $configFile;

$host = $config['host'] ?? '';
$user = $config['user'] ?? '';
$pass = $config['pass'] ?? '';
$name = $config['name'] ?? '';
$port = (int) ($config['port'] ?? 3306);
$charset = $config['charset'] ?? 'utf8mb4';

if ($host === '' || $user === '' || $name === '') {
    output_message('Incomplete remote DB config', true);
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);
$serverNumber = null;
if (preg_match('/(?:sv|ifastnet|byethost)(\d+)/i', $host, $matches)) {
    $serverNumber = $matches[1];
}

$hostVariants = [$host];
if ($serverNumber !== null) {
    foreach ([
        "sv{$serverNumber}.ifastnet.com",
        "ifastnet{$serverNumber}.org",
        "byethost{$serverNumber}.org",
    ] as $candidateHost) {
        if (!in_array($candidateHost, $hostVariants, true)) {
            $hostVariants[] = $candidateHost;
        }
    }
}

output_section('Remote DB Diagnostic');
output_message('Config file: ' . $configFile);
output_message('Configured host: ' . $host);
output_message('Configured port: ' . $port);
output_message('Configured database: ' . $name);
output_message('Configured user: ' . $user);
output_message('Configured password mask: ' . mask_secret($pass));
output_message('Configured charset: ' . $charset);
output_message('PHP SAPI: ' . PHP_SAPI);
output_message('Local machine hostname: ' . gethostname());

if (!empty($_SERVER['REMOTE_ADDR'])) {
    output_message('HTTP requester IP: ' . $_SERVER['REMOTE_ADDR']);
}

output_section('Host Resolution');
foreach ($hostVariants as $candidateHost) {
    $resolvedIp = gethostbyname($candidateHost);
    $status = $resolvedIp === $candidateHost ? 'DNS unresolved from this machine' : 'DNS resolved';
    output_message($candidateHost . ' => ' . $resolvedIp . ' (' . $status . ')');
}

output_section('Connection Attempt');
output_message('DSN: ' . $dsn);
output_message('Expected remote MySQL client IP from server error: if authentication fails, MySQL usually reports the public IP it sees for this machine.');

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
    ]);

    $serverVersion = $pdo->query('SELECT VERSION() AS version')->fetch();
    $dbInfo = $pdo->query('SELECT DATABASE() AS db_name, CURRENT_USER() AS mysql_current_user, USER() AS mysql_session_user')->fetch();

    output_message('REMOTE DB OK');
    output_message("Host: {$host}:{$port}");
    output_message("Database: {$dbInfo['db_name']}");
    output_message("Current user: {$dbInfo['mysql_current_user']}");
    output_message("Session user: {$dbInfo['mysql_session_user']}");
    output_message("Server version: {$serverVersion['version']}");
    exit(0);
} catch (Throwable $e) {
    output_message('REMOTE DB ERROR', true);
    output_message('Exception class: ' . get_class($e), true);
    output_message('Error message: ' . $e->getMessage(), true);

    if ($e instanceof PDOException) {
        $errorInfo = $e->errorInfo ?? null;
        if (is_array($errorInfo)) {
            output_message('SQLSTATE: ' . ($errorInfo[0] ?? 'n/a'), true);
            output_message('Driver code: ' . ($errorInfo[1] ?? 'n/a'), true);
            output_message('Driver detail: ' . ($errorInfo[2] ?? 'n/a'), true);
        }
    }

    output_section('Interpretation');
    output_message('The remote MySQL server was reached, but it rejected the login.', true);
    output_message('This usually means one of these:', true);
    output_message('1. The public IP seen by MySQL is not allowed in Remote MySQL.', true);
    output_message('2. The password is not exactly the one configured for the MySQL user.', true);
    output_message('3. The hostname is not the correct MySQL hostname for this account.', true);
    output_message('4. Remote MySQL access is enabled in cPanel, but not yet applied to this specific source IP.', true);

    if (preg_match("/'([^']+)'@'([^']+)'/", $e->getMessage(), $authMatches)) {
        output_message('MySQL says it sees this login as user `' . $authMatches[1] . '` from client IP `' . $authMatches[2] . '`.', true);
        output_message('That client IP is the one that must be allowed in Remote MySQL, unless you use `%`.', true);
    }

    output_section('Suggested Next Checks');
    output_message('1. In cPanel > Remote MySQL, allow `191.98.210.134` or `%`.', true);
    output_message('2. Re-save the password for MySQL user `aspierd1_admin` and verify it against this file.', true);
    output_message('3. If it still fails, test the alternative hostnames listed above.', true);
    output_message('4. If one alternative hostname resolves differently, update config/database.hosting.php and retest.', true);
    exit(2);
}
