<?php

require_once __DIR__ . '/../core/Database.php';

class MediaRecurso {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerRecursosPorProfesor($profesorId, $instanciaId) {
        $this->db->query("
            SELECT *
            FROM media_recursos
            WHERE profesor_id = :profesor_id
              AND instancia_id = :instancia_id
            ORDER BY fecha_creacion DESC
        ");
        $this->db->bind(':profesor_id', $profesorId);
        $this->db->bind(':instancia_id', $instanciaId);
        return $this->db->resultSet();
    }

    public function obtenerImagenesPorProfesor($profesorId, $instanciaId) {
        $this->db->query("
            SELECT *
            FROM media_recursos
            WHERE profesor_id = :profesor_id
              AND instancia_id = :instancia_id
              AND tipo_media = 'imagen'
            ORDER BY fecha_creacion DESC
        ");
        $this->db->bind(':profesor_id', $profesorId);
        $this->db->bind(':instancia_id', $instanciaId);
        return $this->db->resultSet();
    }

    public function obtenerRecursoPorId($id, $profesorId, $instanciaId) {
        $this->db->query("
            SELECT *
            FROM media_recursos
            WHERE id = :id
              AND profesor_id = :profesor_id
              AND instancia_id = :instancia_id
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        $this->db->bind(':profesor_id', $profesorId);
        $this->db->bind(':instancia_id', $instanciaId);
        return $this->db->single();
    }

    public function crearRecurso($datos) {
        $this->db->query("
            INSERT INTO media_recursos (
                instancia_id,
                profesor_id,
                titulo,
                descripcion,
                tipo_media,
                ruta_archivo,
                mime_type,
                idioma,
                alt_text,
                metadata
            ) VALUES (
                :instancia_id,
                :profesor_id,
                :titulo,
                :descripcion,
                :tipo_media,
                :ruta_archivo,
                :mime_type,
                :idioma,
                :alt_text,
                :metadata
            )
        ");

        $this->db->bind(':instancia_id', $datos['instancia_id']);
        $this->db->bind(':profesor_id', $datos['profesor_id']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion'] ?? null);
        $this->db->bind(':tipo_media', $datos['tipo_media']);
        $this->db->bind(':ruta_archivo', $datos['ruta_archivo']);
        $this->db->bind(':mime_type', $datos['mime_type'] ?? null);
        $this->db->bind(':idioma', $datos['idioma'] ?? null);
        $this->db->bind(':alt_text', $datos['alt_text'] ?? null);
        $this->db->bind(':metadata', $datos['metadata'] ?? null);

        return $this->db->execute();
    }

    public function eliminarRecurso($id, $profesorId, $instanciaId) {
        $this->db->query("
            DELETE FROM media_recursos
            WHERE id = :id
              AND profesor_id = :profesor_id
              AND instancia_id = :instancia_id
        ");
        $this->db->bind(':id', $id);
        $this->db->bind(':profesor_id', $profesorId);
        $this->db->bind(':instancia_id', $instanciaId);
        return $this->db->execute();
    }

    public function obtenerResumenUso($id, $profesorId, $instanciaId) {
        $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM cursos WHERE portada_media_id = :id) AS cursos_portada,
                (SELECT COUNT(*) FROM contenido_bloques WHERE media_id = :id) AS bloques_contenido
        ");
        $this->db->bind(':id', $id);
        $resumen = $this->db->single();

        if (!$resumen) {
            return (object) [
                'cursos_portada' => 0,
                'bloques_contenido' => 0,
                'total_usos' => 0,
            ];
        }

        $resumen->cursos_portada = (int) ($resumen->cursos_portada ?? 0);
        $resumen->bloques_contenido = (int) ($resumen->bloques_contenido ?? 0);
        $resumen->total_usos = $resumen->cursos_portada + $resumen->bloques_contenido;

        return $resumen;
    }
}
