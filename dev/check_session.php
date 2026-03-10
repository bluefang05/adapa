<?php
require_once __DIR__ . '/../config/database.php';
// Include Auth to see its effect
if (file_exists(__DIR__ . '/../core/Auth.php')) {
    require_once __DIR__ . '/../core/Auth.php';
}

echo "Cookie Params:\n";
print_r(session_get_cookie_params());

echo "\nSession Status: " . session_status() . " (2=Active)\n";
echo "Session ID: " . session_id() . "\n";
