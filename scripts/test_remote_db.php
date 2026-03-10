<?php

require_once __DIR__ . '/RemoteDB.php';

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

try {
    $config = RemoteDB::getRemoteConfig();
} catch (RuntimeException $e) {
    output_message($e->getMessage(), true);
    exit(1);
}

$host = $config['host'];
$user = $config['user'];
$pass = $config['pass'];
$name = $config['name'];
$port = $config['port'];
$charset = $config['charset'];

if ($host === '' || $user === '' || $name === '') {
    output_message('Incomplete remote DB config', true);
    exit(1);
}

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

output_section('Connection Test');
try {
    $pdo = RemoteDB::connectRemote();
    output_message('✅ Connection SUCCESSFUL!');
    
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    output_message('Server Version: ' . $version);
    
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    output_message('Tables found: ' . count($tables));
    
} catch (Exception $e) {
    output_message('❌ Connection FAILED: ' . $e->getMessage(), true);
}
