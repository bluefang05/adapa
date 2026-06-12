<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$email = trim((string) getenv('ADMIN_EMAIL'));
$password = (string) getenv('ADMIN_PASSWORD');
$nombre = trim((string) (getenv('ADMIN_FIRST_NAME') ?: 'Admin'));
$apellido = trim((string) (getenv('ADMIN_LAST_NAME') ?: 'Principal'));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    fwrite(STDERR, "Define ADMIN_EMAIL y ADMIN_PASSWORD (minimo 8 caracteres).\n");
    exit(1);
}

try {
    $db = new Database();
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $db->query('SELECT id, instancia_id FROM instancias ORDER BY id ASC LIMIT 1');
    $instancia = $db->single();
    $instanciaId = $instancia ? (int) $instancia->instancia_id : 1;

    $db->query('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $db->bind(':email', $email);
    $existing = $db->single();

    if ($existing) {
        $db->query('
            UPDATE usuarios
            SET
                instancia_id = :instancia_id,
                password_hash = :password_hash,
                nombre = :nombre,
                apellido = :apellido,
                es_estudiante = 0,
                es_profesor = 0,
                es_admin_institucion = 1,
                billing_plan = :billing_plan,
                is_official = 1,
                activo = 1,
                email_verificado = 1
            WHERE id = :id
        ');
        $db->bind(':instancia_id', $instanciaId);
        $db->bind(':password_hash', $hash);
        $db->bind(':nombre', $nombre);
        $db->bind(':apellido', $apellido);
        $db->bind(':billing_plan', 'lifetime');
        $db->bind(':id', (int) $existing->id);
        $db->execute();
        echo "Admin actualizado: {$email}\n";
        exit(0);
    }

    $db->query('
        INSERT INTO usuarios (
            instancia_id, email, password_hash, nombre, apellido,
            idioma_base, idioma_interfaz,
            es_estudiante, es_profesor, es_admin_institucion,
            billing_plan, is_official, vista_default, activo, email_verificado, creado_por, creado_en
        ) VALUES (
            :instancia_id, :email, :password_hash, :nombre, :apellido,
            :idioma_base, :idioma_interfaz,
            :es_estudiante, :es_profesor, :es_admin_institucion,
            :billing_plan, :is_official, :vista_default, :activo, :email_verificado, :creado_por, NOW()
        )
    ');
    $db->bind(':instancia_id', $instanciaId);
    $db->bind(':email', $email);
    $db->bind(':password_hash', $hash);
    $db->bind(':nombre', $nombre);
    $db->bind(':apellido', $apellido);
    $db->bind(':idioma_base', 'espanol');
    $db->bind(':idioma_interfaz', 'espanol');
    $db->bind(':es_estudiante', 0);
    $db->bind(':es_profesor', 0);
    $db->bind(':es_admin_institucion', 1);
    $db->bind(':billing_plan', 'lifetime');
    $db->bind(':is_official', 1);
    $db->bind(':vista_default', 'estudiante');
    $db->bind(':activo', 1);
    $db->bind(':email_verificado', 1);
    $db->bind(':creado_por', null);
    $db->execute();

    echo "Admin creado: {$email}\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error al sembrar admin: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
