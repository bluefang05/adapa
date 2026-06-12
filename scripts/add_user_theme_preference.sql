ALTER TABLE usuarios
    ADD COLUMN theme_preference VARCHAR(16) NOT NULL DEFAULT 'warm'
    AFTER idioma_interfaz;

UPDATE usuarios
SET theme_preference = 'warm'
WHERE theme_preference IS NULL
   OR theme_preference NOT IN ('warm', 'paper', 'sky', 'dark');
