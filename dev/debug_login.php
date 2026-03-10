<?php
// SCRIPT DE DIAGNÓSTICO DE LOGIN ADAPTADO PARA ADAPA
// Subir a /dev/debug_login.php
// Ejecutar: https://yoursite.com/dev/debug_login.php

// Asegurar que se muestren errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configurar cabeceras para evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo "<html><head><title>🕵️ Adapa Login Debugger</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='bg-light p-5'>";

echo "<div class='container bg-white p-4 rounded shadow'>";
echo "<h1 class='mb-4'>🕵️ Adapa Login Debugger</h1>";

// 1. Cargar configuración de DB
// Definir constantes si no existen (para evitar errores al cargar config)
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

$dbPath = __DIR__ . '/../config/database.php';

echo "<div class='alert alert-secondary'>";
echo "<strong>📂 Cargando configuración de DB...</strong><br>";

if (!file_exists($dbPath)) {
    die("❌ No encuentro database.php en: $dbPath");
}

try {
    require_once $dbPath;
    
    // 0. Manejo de formulario de configuración de BD (antes de conectar)
    $db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $db_name = defined('DB_NAME') ? DB_NAME : '';
    $db_user = defined('DB_USER') ? DB_USER : '';
    $db_pass = defined('DB_PASS') ? DB_PASS : '';
    $db_charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

    // AUTO-CORRECCIÓN INTELIGENTE (Hotfix para Adapa)
    // Si detectamos la config antigua de Ancobo, forzamos la de Adapa
    if ($db_name === 'aspierd1_ancobo') {
        $db_name = 'aspierd1_adapa';
    }

    // Sobrescribir con POST si se envía el formulario
    if (isset($_POST['update_db_config'])) {
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        // Guardar en sesión o cookie sería ideal, pero por ahora solo POST
    }

    echo "<div class='card mb-4 border-warning'>";
    echo "<div class='card-header bg-warning text-dark'><strong>⚙️ Configuración de Base de Datos (Sobrescribir Temporalmente)</strong></div>";
    echo "<div class='card-body'>";
    echo "<form method='post' class='row g-3'>";
    echo "<div class='col-md-3'><label>Host</label><input type='text' name='db_host' class='form-control' value='$db_host'></div>";
    echo "<div class='col-md-3'><label>Usuario</label><input type='text' name='db_user' class='form-control' value='$db_user'></div>";
    echo "<div class='col-md-3'><label>Password</label><input type='text' name='db_pass' class='form-control' value='$db_pass'></div>";
    echo "<div class='col-md-3'><label>Base de Datos</label><input type='text' name='db_name' class='form-control' value='$db_name'></div>";
    echo "<div class='col-12'><button type='submit' name='update_db_config' class='btn btn-dark w-100'>🔄 Probar Conexión con estos datos</button></div>";
    echo "</form>";
    echo "</div></div>";
    
    // Conexión manual usando variables (no constantes)
    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        echo "<div class='alert alert-success'>✅ <strong>Conexión Exitosa</strong> a la BD: <code>$db_name</code></div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>❌ <strong>Error de Conexión:</strong> " . $e->getMessage() . "</div>";
        // Intentar conectar sin DB seleccionada para listar DBs
        try {
            $pdoNoDB = new PDO("mysql:host=$db_host;charset=$db_charset", $db_user, $db_pass);
            $dbs = $pdoNoDB->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<div class='alert alert-info'>ℹ️ <strong>Bases de Datos disponibles en este servidor ($db_host):</strong><br>" . implode(", ", $dbs) . "</div>";
        } catch (Exception $ex) {
            echo "<div class='alert alert-secondary'>No se pudieron listar otras bases de datos. Verifique usuario/password.</div>";
        }
        // Detener ejecución si no hay conexión válida
        die("</div></body></html>");
    }

    // DIAGNÓSTICO DE TABLAS (Nuevo)
    echo "<hr><h4>📊 Diagnóstico de Tablas</h4>";
    $tables = [];
    try {
        $stmtTables = $pdo->query("SHOW TABLES");
        $tables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<div class='alert alert-warning'>⚠️ La base de datos está vacía (0 tablas).</div>";
        } else {
            echo "<div class='alert alert-secondary' style='max-height: 200px; overflow-y: auto;'>";
            echo "<strong>Tablas encontradas (" . count($tables) . "):</strong><br>";
            echo implode(", ", $tables);
            echo "</div>";

            // Verificar si 'usuarios' existe
            if (!in_array('usuarios', $tables)) {
                echo "<div class='alert alert-danger'>❌ <strong>CRÍTICO:</strong> La tabla <code>usuarios</code> NO existe en esta base de datos. <br>Tablas similares: ";
                $similar = array_filter($tables, function($t) { return strpos($t, 'user') !== false || strpos($t, 'usu') !== false; });
                echo empty($similar) ? "Ninguna detectada." : implode(", ", $similar);
                echo "</div>";

                // INTENTO DE DIAGNÓSTICO AVANZADO: ¿Es la BD incorrecta?
                if (in_array('users', $tables)) {
                    echo "<div class='alert alert-warning'>⚠️ Se encontró una tabla <code>users</code>. Verificando si pertenece a Adapa...<br>";
                    try {
                        $stmtDesc = $pdo->query("DESCRIBE users");
                        $columns = $stmtDesc->fetchAll(PDO::FETCH_COLUMN);
                        echo "Columnas en <code>users</code>: " . implode(", ", $columns) . "<br>";
                        
                        if (in_array('es_estudiante', $columns)) {
                            echo "✅ <strong>¡Parece ser la tabla correcta pero con nombre en inglés!</strong> Deberías cambiar la configuración de tu modelo o renombrar la tabla.";
                        } else {
                            echo "❌ <strong>Esta NO es la tabla de usuarios de Adapa.</strong> Faltan columnas clave (es_estudiante, es_profesor). <br><strong>CONCLUSIÓN:</strong> Estás conectado a la base de datos de OTRO proyecto (posiblemente un ERP/Contabilidad por las tablas 'invoices', 'tenants').";
                        }
                    } catch (Exception $e) {
                        echo "No se pudo inspeccionar `users`.";
                    }
                    echo "</div>";
                }

                // Intentar listar otras BDs disponibles
                try {
                    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                    echo "<div class='alert alert-info'>ℹ️ <strong>Bases de Datos disponibles en este servidor:</strong><br>" . implode(", ", $dbs) . "</div>";
                } catch (Exception $e) {
                    echo "<div class='alert alert-secondary'>No se pudieron listar otras bases de datos (Permisos restringidos).</div>";
                }

            } else {
                echo "<div class='alert alert-success'>✅ Tabla <code>usuarios</code> detectada correctamente.</div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error al listar tablas: " . $e->getMessage() . "</div>";
    }

} catch (Exception $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
echo "</div>";

// Lógica de corrección (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fix_pass']) && isset($_POST['fix_email'])) {
        $newPass = $_POST['fix_pass'];
        $emailToFix = $_POST['fix_email'];
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        
        $stmtFix = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE email = ?");
        if ($stmtFix->execute([$newHash, $emailToFix])) {
            echo "<div class='alert alert-success'>✨ Contraseña actualizada correctamente para <strong>$emailToFix</strong>. <br>Nueva clave: <code>$newPass</code></div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error al actualizar contraseña.</div>";
        }
    }
    
    if (isset($_POST['activate_user']) && isset($_POST['fix_email'])) {
        $emailToFix = $_POST['fix_email'];
        $stmtFix = $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE email = ?");
        if ($stmtFix->execute([$emailToFix])) {
            echo "<div class='alert alert-success'>✨ Usuario <strong>$emailToFix</strong> activado correctamente.</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error al activar usuario.</div>";
        }
    }
}

// Formulario de prueba
$testEmail = $_POST['test_email'] ?? 'manuelx_05@hotmail.com';
$testPass = $_POST['test_pass'] ?? 'polilla05';

echo "<form method='post' class='mb-4 p-3 border rounded bg-light'>";
echo "<h3>🔍 Probar Credenciales</h3>";
echo "<div class='row'>";
echo "<div class='col-md-5'><input type='email' name='test_email' class='form-control' value='$testEmail' placeholder='Email'></div>";
echo "<div class='col-md-5'><input type='text' name='test_pass' class='form-control' value='$testPass' placeholder='Password'></div>";
echo "<div class='col-md-2'><button type='submit' class='btn btn-primary w-100'>Probar</button></div>";
echo "</div>";
echo "</form>";

if ($testEmail) {
    testUser($pdo, $testEmail, $testPass);
}

echo "</div></body></html>";

// Función para probar un usuario
function testUser($pdo, $email, $expectedPass) {
    echo "<hr><h2>👤 Resultados para: <span class='text-primary'>$email</span></h2>";
    
    // 1. Buscar usuario (Simulando lógica de Auth)
    // Ajustado para tabla 'usuarios' de Adapa
    $tableName = 'usuarios'; // Default
    
    // Intentar detectar nombre de tabla correcto si falló antes
    // (Esto es solo un fallback visual, la query real abajo debe ser correcta)
    
    try {
        $sqlLogin = "SELECT id, email, password_hash, nombre, apellido, activo, email_verificado 
                     FROM usuarios 
                     WHERE email = ? 
                     LIMIT 1";
                     
        $stmt = $pdo->prepare($sqlLogin);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>❌ <strong>Error SQL Crítico:</strong> " . $e->getMessage() . "<br>Probablemente la tabla 'usuarios' no existe. Revisa el diagnóstico de tablas arriba.</div>";
        return;
    }

    if ($user) {
        echo "<div class='alert alert-info'>";
        echo "<strong>✅ Usuario encontrado en BD:</strong>";
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Nombre: {$user['nombre']} {$user['apellido']}</li>";
        echo "<li>Activo: " . ($user['activo'] ? '✅ Sí' : '❌ No') . "</li>";
        echo "<li>Email Verificado: " . ($user['email_verificado'] ? '✅ Sí' : '❌ No') . "</li>";
        echo "</ul>";
        echo "</div>";

        // Alertas de estado
        if (!$user['activo']) {
            echo "<div class='alert alert-warning'>⚠️ <strong>El usuario está INACTIVO (activo = 0).</strong> Esto impide el login en la mayoría de los casos.";
            echo "<form method='post' class='mt-2'>";
            echo "<input type='hidden' name='fix_email' value='$email'>";
            echo "<input type='hidden' name='activate_user' value='1'>";
            echo "<input type='hidden' name='test_email' value='$email'>";
            echo "<input type='hidden' name='test_pass' value='$expectedPass'>";
            echo "<button type='submit' class='btn btn-success'>✨ Activar Usuario Ahora</button>";
            echo "</form>";
            echo "</div>";
        }

        // 2. Verificar password
        echo "<h3>🔑 Verificando contraseña: <code>$expectedPass</code></h3>";
        if (password_verify($expectedPass, $user['password_hash'])) {
            echo "<div class='alert alert-success'>✅ <strong>Contraseña CORRECTA.</strong> <br>El hash coincide. Si el login falla, el problema está en la sesión o redirección, no en las credenciales.</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ <strong>Contraseña INCORRECTA.</strong> <br>El hash en la base de datos no coincide con la contraseña proporcionada.</div>";
            echo "<p class='text-muted'>Hash en DB: " . substr($user['password_hash'], 0, 20) . "...</p>";
            
            // Botón para arreglar
            echo "<form method='post' class='mt-3'>";
            echo "<input type='hidden' name='fix_email' value='$email'>";
            echo "<input type='hidden' name='fix_pass' value='$expectedPass'>";
            echo "<input type='hidden' name='test_email' value='$email'>";
            echo "<input type='hidden' name='test_pass' value='$expectedPass'>";
            echo "<button type='submit' class='btn btn-danger'>🔧 Forzar contraseña a '$expectedPass'</button>";
            echo "</form>";
        }
    } else {
        echo "<div class='alert alert-danger'>❌ <strong>Usuario NO encontrado.</strong> No existe ningún registro con el email <code>$email</code> en la tabla <code>usuarios</code>.</div>";
    }
}
?>