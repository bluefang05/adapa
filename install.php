<?php
http_response_code(410);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalador Deshabilitado</title>
</head>
<body>
    <h1>Instalador deshabilitado</h1>
    <p>El instalador automatico web fue retirado para evitar instalaciones incompletas o desactualizadas.</p>
    <p>La referencia canonica actual es el dump <code>adapa_db.sql</code>, generado desde la base real de XAMPP.</p>
    <p>Importa ese archivo manualmente en MySQL y luego ajusta las credenciales en <code>config/database.php</code> si tu entorno cambia.</p>
</body>
</html>
