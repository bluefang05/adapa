ALTER TABLE cursos
    ADD COLUMN IF NOT EXISTS estado_editorial ENUM('borrador','en_revision','publicable','publicado','archivado') NULL AFTER estado;

ALTER TABLE lecciones
    ADD COLUMN IF NOT EXISTS estado_editorial ENUM('borrador','en_revision','publicable','publicado','archivado') NULL AFTER estado;

UPDATE cursos
SET estado_editorial = CASE
    WHEN estado = 'archivado' THEN 'archivado'
    WHEN estado = 'activo' AND es_publico = 1 THEN 'publicado'
    WHEN estado = 'activo' THEN 'publicable'
    WHEN estado IN ('pausado', 'finalizado') THEN 'en_revision'
    ELSE 'borrador'
END
WHERE estado_editorial IS NULL;

UPDATE lecciones
SET estado_editorial = CASE
    WHEN estado = 'archivada' THEN 'archivado'
    WHEN estado = 'publicada' THEN 'publicado'
    ELSE 'borrador'
END
WHERE estado_editorial IS NULL;

ALTER TABLE cursos
    MODIFY COLUMN estado_editorial ENUM('borrador','en_revision','publicable','publicado','archivado') NOT NULL DEFAULT 'borrador';

ALTER TABLE lecciones
    MODIFY COLUMN estado_editorial ENUM('borrador','en_revision','publicable','publicado','archivado') NOT NULL DEFAULT 'borrador';
