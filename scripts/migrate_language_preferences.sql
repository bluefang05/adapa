ALTER TABLE usuarios
    ADD COLUMN idioma_base varchar(32) NOT NULL DEFAULT 'espanol' AFTER apellido,
    ADD COLUMN idioma_interfaz varchar(32) NOT NULL DEFAULT 'espanol' AFTER vista_default;

UPDATE usuarios
SET idioma_base = 'espanol'
WHERE idioma_base IS NULL OR idioma_base = '';

UPDATE usuarios
SET idioma_interfaz = 'espanol'
WHERE idioma_interfaz IS NULL OR idioma_interfaz = '';

ALTER TABLE cursos
    ADD COLUMN idioma_base varchar(32) NOT NULL DEFAULT 'espanol' AFTER idioma_objetivo;

UPDATE cursos
SET idioma_base = CASE
    WHEN idioma_base IS NOT NULL AND idioma_base <> '' THEN idioma_base
    WHEN idioma_ensenanza IS NOT NULL AND idioma_ensenanza <> '' THEN idioma_ensenanza
    ELSE 'espanol'
END;
