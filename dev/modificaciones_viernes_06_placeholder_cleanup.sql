START TRANSACTION;

-- Limpieza de placeholders en actividades tipo opcion multiple
-- Fecha: Viernes 06 Marzo 2026

UPDATE actividades
SET contenido = REPLACE(contenido, 'opcion incorrecta 1', 'expresion no adecuada')
WHERE contenido LIKE '%opcion incorrecta 1%';

UPDATE actividades
SET contenido = REPLACE(contenido, 'opcion incorrecta 2', 'gramatica incorrecta')
WHERE contenido LIKE '%opcion incorrecta 2%';

UPDATE actividades
SET contenido = REPLACE(contenido, 'frase no natural', 'frase poco natural')
WHERE contenido LIKE '%frase no natural%';

UPDATE actividades
SET contenido = REPLACE(contenido, 'estructura rota', 'estructura gramatical incorrecta')
WHERE contenido LIKE '%estructura rota%';

COMMIT;

-- Validacion: debe devolver 0
SELECT COUNT(*) AS total_placeholder
FROM actividades
WHERE contenido LIKE '%opcion incorrecta%'
   OR contenido LIKE '%frase no natural%'
   OR contenido LIKE '%estructura rota%';
