# 🕵️ Ancobo Login Debugger & Fixer

This script is a powerful diagnostic tool designed to resolve "Invalid credentials" errors in PHP/MySQL applications without modifying the core codebase.

## 🚀 Purpose
When a user cannot login despite correct passwords, this script isolates the failure point:
1.  **Database Connection:** Verifies PDO connectivity independently.
2.  **User Existence:** Checks if the user exists *and* meets login criteria (e.g., `is_active=1`, `deleted_at IS NULL`).
3.  **Password Hashing:** Verifies the password hash against the input using `password_verify()`.
4.  **Auto-Fix:** Provides one-click buttons to reset passwords or activate users directly in the DB.

## 🛠️ Usage
1.  Upload `debug_login.php` to a web-accessible directory (e.g., `/dev/`).
2.  Navigate to the script URL (e.g., `https://site.com/dev/debug_login.php`).
3.  Review the diagnostic output for `admin@ancobo.com` (or modify source for other users).
4.  **Fix:** Click the "🔧 Forzar contraseña" or "✨ Activar Usuario" buttons if issues are found.
5.  **Cleanup:** Delete the file after use to prevent security risks.

## 📄 Code (`debug_login.php`)

```php
<?php
// SCRIPT DE DIAGNÓSTICO DE LOGIN MEJORADO
// Subir a /dev/debug_login.php
// Ejecutar: https://yoursite.com/dev/debug_login.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<html><body style='font-family: sans-serif; padding: 20px; max-width: 800px; margin: 0 auto;'>";
echo "<h1>🕵️ Ancobo Login Debugger v2</h1>";

// 1. Cargar configuración de DB (Ajustar ruta según estructura)
$dbPath = __DIR__ . '/../api/v1/db.php';
if (!file_exists($dbPath)) {
    die("❌ No encuentro db.php en: $dbPath");
}

echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd; margin-bottom: 20px;'>";
echo "<strong>📂 Cargando configuración de DB...</strong><br>";
require_once $dbPath;

if (!$pdo) {
    die("❌ \$pdo es null después de cargar db.php");
}
echo "✅ Conexión PDO establecida.<br>";

global $usedConfig;
if (isset($usedConfig)) {
    echo "Connected to: <strong>" . htmlspecialchars($usedConfig['host'] ?? '???') . " / " . htmlspecialchars($usedConfig['db'] ?? '???') . "</strong>";
}
echo "</div>";

// Función para probar un usuario
function testUser($pdo, $email, $expectedPass) {
    echo "<hr><h2>👤 Probando usuario: <span style='color: blue'>$email</span></h2>";
    
    // 1. Simular query exacta de login.php (Ajustar WHERE según lógica de app)
    $sqlLogin = "SELECT id, tenant_id, username, email, password_hash, full_name, is_active, deleted_at 
                 FROM users 
                 WHERE email = ? AND is_active = 1 AND deleted_at IS NULL 
                 LIMIT 1";
    $stmt = $pdo->prepare($sqlLogin);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<p style='color: green; font-weight: bold;'>✅ El usuario fue encontrado por la consulta de Login.</p>";
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Active: {$user['is_active']}</li>";
        echo "</ul>";

        // 2. Verificar password
        echo "<h3>🔑 Verificando contraseña: '$expectedPass'</h3>";
        if (password_verify($expectedPass, $user['password_hash'])) {
            echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>✅ Contraseña CORRECTA.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'>❌ Contraseña INCORRECTA.</p>";
            echo "<p>Hash en DB: " . substr($user['password_hash'], 0, 20) . "...</p>";
            
            // Botón para arreglar
            echo "<form method='post' style='margin-top: 10px;'>";
            echo "<input type='hidden' name='fix_email' value='$email'>";
            echo "<input type='hidden' name='fix_pass' value='$expectedPass'>";
            echo "<button type='submit' style='background: #dc3545; color: white; padding: 10px 20px; font-size: 16px; border: none; cursor: pointer; border-radius: 5px;'>🔧 Forzar contraseña a '$expectedPass'</button>";
            echo "</form>";
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ El usuario NO fue encontrado por la consulta de Login.</p>";
        
        // Diagnóstico profundo
        $stmt2 = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt2->execute([$email]);
        $userAny = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($userAny) {
            echo "<p>⚠️ Pero el usuario EXISTE en la tabla. Problemas detectados:</p>";
            echo "<ul>";
            if ($userAny['is_active'] != 1) echo "<li style='color: red'>is_active es {$userAny['is_active']} (Debe ser 1)</li>";
            if ($userAny['deleted_at'] !== null) echo "<li style='color: red'>deleted_at es {$userAny['deleted_at']} (Debe ser NULL)</li>";
            echo "</ul>";
            
            // Botón para activar
            echo "<form method='post' style='margin-top: 10px;'>";
            echo "<input type='hidden' name='activate_email' value='$email'>";
            echo "<button type='submit' style='background: #ffc107; color: black; padding: 10px 20px; font-size: 16px; border: none; cursor: pointer; border-radius: 5px;'>✨ Activar Usuario (Fix Flags)</button>";
            echo "</form>";
        } else {
            echo "<p style='color: red'>El usuario no existe en absoluto en la tabla 'users'.</p>";
        }
    }
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fix_email']) && isset($_POST['fix_pass'])) {
        $email = $_POST['fix_email'];
        $pass = $_POST['fix_pass'];
        $newHash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$newHash, $email]);
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb;'>✅ Contraseña actualizada para $email.</div>";
    }
    
    if (isset($_POST['activate_email'])) {
        $email = $_POST['activate_email'];
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1, deleted_at = NULL WHERE email = ?");
        $stmt->execute([$email]);
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb;'>✅ Usuario activado: $email.</div>";
    }
}

// Ejecutar pruebas (Personalizar usuarios aquí)
testUser($pdo, 'admin@ancobo.com', 'Admin123!');
testUser($pdo, 'owner@ancobo.com', 'Owner123!');

echo "</body></html>";
?>
```
