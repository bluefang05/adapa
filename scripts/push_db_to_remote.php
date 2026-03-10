<?php

declare(strict_types=1);

require_once __DIR__ . '/RemoteDB.php';

/**
 * Script to synchronize (push) local database to remote database.
 * 
 * WARNING: This script will OVERWRITE data in the remote database.
 * It will:
 * 1. Disable foreign key checks on remote.
 * 2. Truncate all tables on remote (to clear old data).
 * 3. Copy all data from local to remote tables.
 * 4. Re-enable foreign key checks.
 * 
 * Note: It assumes schemas are identical (as confirmed by compare_local_remote_db.php).
 * If schemas differ, use mysqldump or a migration tool first.
 */

function log_message(string $msg, bool $isError = false): void {
    $prefix = $isError ? '[ERROR] ' : '[INFO] ';
    $timestamp = date('Y-m-d H:i:s');
    $output = "{$timestamp} {$prefix}{$msg}" . PHP_EOL;
    
    if (PHP_SAPI === 'cli') {
        fwrite($isError ? STDERR : STDOUT, $output);
    } else {
        echo $output;
    }
}

try {
    // 1. Connect to both databases
    log_message("Connecting to Local DB...");
    $localPdo = RemoteDB::connectLocal();
    $localConfig = RemoteDB::getLocalConfig();
    log_message("Connected to Local: {$localConfig['name']}");

    log_message("Connecting to Remote DB...");
    $remotePdo = RemoteDB::connectRemote();
    $remoteConfig = RemoteDB::getRemoteConfig();
    log_message("Connected to Remote: {$remoteConfig['name']} @ {$remoteConfig['host']}");

    // 2. Get list of tables from Local
    $stmt = $localPdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        log_message("No tables found in Local DB. Aborting.", true);
        exit(1);
    }

    log_message("Found " . count($tables) . " tables in Local DB.");

    // 3. Prepare Remote DB
    log_message("Disabling foreign key checks on Remote...");
    $remotePdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 4. Iterate and Sync
    foreach ($tables as $table) {
        log_message("Syncing table: {$table}");
        
        // 4.1 Check if table exists on remote (optional safety, but good practice)
        try {
            $remotePdo->query("SELECT 1 FROM `{$table}` LIMIT 1");
        } catch (PDOException $e) {
            log_message("Table {$table} does not exist on remote. Creating it...", true);
            // Fetch CREATE TABLE from local
            $createStmt = $localPdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $createSql = $createStmt['Create Table'];
            // Execute on remote
            $remotePdo->exec($createSql);
            log_message("Table {$table} created on remote.");
        }

        // 4.2 Truncate Remote Table
        log_message("  - Truncating remote table...");
        $remotePdo->exec("TRUNCATE TABLE `{$table}`");

        // 4.3 Fetch Data from Local
        // Using unbuffered query for large tables to avoid memory issues
        $localPdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $dataStmt = $localPdo->query("SELECT * FROM `{$table}`");
        
        $batchSize = 100;
        $batch = [];
        $rowCount = 0;
        
        // Get column names for INSERT statement
        // We need to fetch one row or describe table to get columns if table is empty
        // But since we are iterating, we can just fetch rows.
        // Wait, if table is empty locally, we just truncated remote and we are done.
        
        $firstRow = true;
        $columns = [];
        $insertSqlStart = "";

        while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($firstRow) {
                $columns = array_keys($row);
                $colsList = implode('`, `', $columns);
                $insertSqlStart = "INSERT INTO `{$table}` (`{$colsList}`) VALUES ";
                $firstRow = false;
            }

            $batch[] = $row;
            $rowCount++;

            if (count($batch) >= $batchSize) {
                insert_batch($remotePdo, $table, $insertSqlStart, $batch, $columns);
                $batch = []; // Clear batch
                echo "."; // Progress indicator
            }
        }
        
        // Insert remaining rows
        if (!empty($batch)) {
            insert_batch($remotePdo, $table, $insertSqlStart, $batch, $columns);
        }

        // Re-enable buffered query for next operations
        $localPdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        
        log_message("  - Synced {$rowCount} rows.");
    }

    // 5. Re-enable foreign key checks
    log_message("Re-enabling foreign key checks on Remote...");
    $remotePdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    log_message("✅ Database synchronization complete!");

} catch (Exception $e) {
    log_message("CRITICAL ERROR: " . $e->getMessage(), true);
    // Attempt to re-enable checks even on error
    if (isset($remotePdo)) {
        try {
            $remotePdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        } catch (Exception $ex) {
            // Ignore
        }
    }
    exit(1);
}

/**
 * Helper to insert a batch of rows
 */
function insert_batch(PDO $pdo, string $table, string $sqlStart, array $rows, array $columns): void {
    if (empty($rows)) return;

    $placeholders = "(" . implode(', ', array_fill(0, count($columns), '?')) . ")";
    $sql = $sqlStart . implode(', ', array_fill(0, count($rows), $placeholders));
    
    $stmt = $pdo->prepare($sql);
    
    $params = [];
    foreach ($rows as $row) {
        foreach ($columns as $col) {
            $params[] = $row[$col];
        }
    }
    
    try {
        $stmt->execute($params);
    } catch (PDOException $e) {
        throw new RuntimeException("Failed to insert batch into {$table}: " . $e->getMessage());
    }
}
