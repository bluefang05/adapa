ALTER TABLE cursos
    ADD COLUMN IF NOT EXISTS idioma_objetivo VARCHAR(32) NULL AFTER idioma,
    ADD COLUMN IF NOT EXISTS idioma_ensenanza VARCHAR(32) NOT NULL DEFAULT 'espanol' AFTER idioma_objetivo,
    ADD COLUMN IF NOT EXISTS portada_media_id INT(11) NULL AFTER idioma_ensenanza,
    ADD COLUMN IF NOT EXISTS nivel_cefr_desde ENUM('A1','A2','B1','B2','C1','C2') NULL AFTER portada_media_id,
    ADD COLUMN IF NOT EXISTS nivel_cefr_hasta ENUM('A1','A2','B1','B2','C1','C2') NULL AFTER nivel_cefr_desde;

UPDATE cursos
SET idioma_objetivo = idioma
WHERE idioma_objetivo IS NULL OR idioma_objetivo = '';

UPDATE cursos
SET nivel_cefr_desde = nivel_cefr,
    nivel_cefr_hasta = nivel_cefr
WHERE nivel_cefr_desde IS NULL
   OR nivel_cefr_hasta IS NULL;

CREATE TABLE IF NOT EXISTS media_recursos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    instancia_id INT(11) NOT NULL,
    profesor_id INT(11) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    tipo_media ENUM('imagen', 'audio', 'video', 'pdf', 'documento') NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NULL,
    idioma VARCHAR(32) NULL,
    alt_text VARCHAR(255) NULL,
    metadata LONGTEXT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_media_instancia (instancia_id),
    KEY idx_media_profesor (profesor_id),
    KEY idx_media_tipo (tipo_media),
    CONSTRAINT fk_media_instancia FOREIGN KEY (instancia_id) REFERENCES instancias (id),
    CONSTRAINT fk_media_profesor FOREIGN KEY (profesor_id) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS contenido_bloques (
    id INT(11) NOT NULL AUTO_INCREMENT,
    teoria_id INT(11) NOT NULL,
    tipo_bloque ENUM('explicacion', 'ejemplo', 'traduccion', 'vocabulario', 'dialogo', 'instruccion', 'imagen', 'audio', 'recurso') NOT NULL DEFAULT 'explicacion',
    titulo VARCHAR(255) NULL,
    contenido LONGTEXT NULL,
    idioma_bloque VARCHAR(32) NULL,
    tts_habilitado TINYINT(1) NOT NULL DEFAULT 0,
    media_id INT(11) NULL,
    orden INT(11) NOT NULL DEFAULT 0,
    metadata LONGTEXT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_bloques_teoria (teoria_id),
    KEY idx_bloques_media (media_id),
    KEY idx_bloques_orden (teoria_id, orden),
    CONSTRAINT fk_bloques_teoria FOREIGN KEY (teoria_id) REFERENCES teoria (id) ON DELETE CASCADE,
    CONSTRAINT fk_bloques_media FOREIGN KEY (media_id) REFERENCES media_recursos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE cursos
    ADD CONSTRAINT fk_cursos_portada_media
    FOREIGN KEY (portada_media_id) REFERENCES media_recursos (id) ON DELETE SET NULL;
