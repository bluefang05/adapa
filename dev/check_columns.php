<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'config/database.php';
require_once 'core/Database.php';
$db = new Database();
$db->query("SHOW COLUMNS FROM usuarios");
$columns = $db->resultSet();
foreach ($columns as $col) {
    echo $col->Field . "\n";
}
