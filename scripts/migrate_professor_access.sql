ALTER TABLE usuarios
    ADD COLUMN billing_plan ENUM('free', 'paid', 'lifetime') NOT NULL DEFAULT 'free' AFTER es_admin_institucion,
    ADD COLUMN is_official TINYINT(1) NOT NULL DEFAULT 0 AFTER billing_plan;

UPDATE usuarios
SET billing_plan = 'paid'
WHERE es_admin_institucion = 1;

UPDATE usuarios
SET billing_plan = 'lifetime', is_official = 1
WHERE email = 'profesor@adapa.edu';
