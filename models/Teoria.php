<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

class Teoria {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function crearTeoria($datos) {
        $this->db->query("INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, orden, duracion_minutos) VALUES (:leccion_id, :titulo, :contenido, :tipo_contenido, :orden, :duracion_minutos)");
        
        $this->db->bind(':leccion_id', $datos['leccion_id']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':tipo_contenido', $datos['tipo_contenido']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':duracion_minutos', $datos['duracion_minutos']);

        if (!$this->db->execute()) {
            return false;
        }

        $teoriaId = (int) $this->db->lastInsertId();
        $this->sincronizarBloques($teoriaId, $datos['bloques'] ?? []);

        return $teoriaId;
    }

    public function obtenerTeoriaPorId($id) {
        $this->db->query("SELECT * FROM teoria WHERE id = :id");
        $this->db->bind(':id', $id);
        $teoria = $this->db->single();
        return $this->adjuntarBloques($teoria);
    }

    public function obtenerTeoriasPorLeccion($leccion_id) {
        $this->db->query("SELECT * FROM teoria WHERE leccion_id = :leccion_id ORDER BY orden ASC");
        $this->db->bind(':leccion_id', $leccion_id);
        return $this->adjuntarBloquesATeorias($this->db->resultSet());
    }

    public function moverTeoria($id, $direction) {
        $teoria = $this->obtenerTeoriaPorId($id);
        if (!$teoria) {
            return false;
        }

        $operator = $direction === 'up' ? '<' : '>';
        $orderBy = $direction === 'up' ? 'DESC' : 'ASC';

        $this->db->query("
            SELECT id, orden
            FROM teoria
            WHERE leccion_id = :leccion_id
              AND orden {$operator} :orden
            ORDER BY orden {$orderBy}, id {$orderBy}
            LIMIT 1
        ");
        $this->db->bind(':leccion_id', $teoria->leccion_id);
        $this->db->bind(':orden', $teoria->orden);
        $vecina = $this->db->single();

        if (!$vecina) {
            return false;
        }

        $this->db->query("UPDATE teoria SET orden = :orden WHERE id = :id");
        $this->db->bind(':orden', $vecina->orden);
        $this->db->bind(':id', $teoria->id);
        $this->db->execute();

        $this->db->query("UPDATE teoria SET orden = :orden WHERE id = :id");
        $this->db->bind(':orden', $teoria->orden);
        $this->db->bind(':id', $vecina->id);
        return $this->db->execute();
    }

    public function actualizarTeoria($id, $datos) {
        $this->db->query("UPDATE teoria SET titulo = :titulo, contenido = :contenido, tipo_contenido = :tipo_contenido, orden = :orden, duracion_minutos = :duracion_minutos WHERE id = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':tipo_contenido', $datos['tipo_contenido']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':duracion_minutos', $datos['duracion_minutos']);

        if (!$this->db->execute()) {
            return false;
        }

        $this->sincronizarBloques($id, $datos['bloques'] ?? []);

        return true;
    }

    public function eliminarTeoria($id) {
        $this->db->query("DELETE FROM teoria WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function obtenerTotalTeoriasPorLeccion($leccion_id) {
        $this->db->query("SELECT COUNT(*) as total FROM teoria WHERE leccion_id = :leccion_id");
        $this->db->bind(':leccion_id', $leccion_id);
        $resultado = $this->db->single();
        return $resultado ? $resultado->total : 0;
    }

    public function obtenerSiguienteOrdenPorLeccion($leccionId) {
        $this->db->query("SELECT MAX(orden) as max_orden FROM teoria WHERE leccion_id = :leccion_id");
        $this->db->bind(':leccion_id', $leccionId);
        $resultado = $this->db->single();
        return $resultado && $resultado->max_orden ? ((int) $resultado->max_orden + 1) : 1;
    }

    public function duplicarTeoria($id, $targetLeccionId = null, $appendCopyLabel = true) {
        $teoria = $this->obtenerTeoriaPorId($id);
        if (!$teoria) {
            return null;
        }

        $leccionId = $targetLeccionId ?: $teoria->leccion_id;
        $titulo = trim((string) $teoria->titulo);
        if ($appendCopyLabel) {
            $titulo .= ' (copia)';
        }

        $bloques = array_map(function ($bloque) {
            return [
                'tipo_bloque' => $bloque->tipo_bloque ?? 'explicacion',
                'titulo' => $bloque->titulo ?? '',
                'contenido' => $bloque->contenido ?? '',
                'idioma_bloque' => $bloque->idioma_bloque ?? '',
                'tts_habilitado' => !empty($bloque->tts_habilitado) ? 1 : 0,
                'media_id' => $bloque->media_id ?? null,
            ];
        }, $teoria->bloques ?? []);

        return $this->crearTeoria([
            'leccion_id' => $leccionId,
            'titulo' => $titulo,
            'contenido' => $teoria->contenido,
            'tipo_contenido' => $teoria->tipo_contenido,
            'orden' => $this->obtenerSiguienteOrdenPorLeccion($leccionId),
            'duracion_minutos' => $teoria->duracion_minutos,
            'bloques' => $bloques,
        ]);
    }

    public function marcarComoLeida($estudiante_id, $teoria_id) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to ensure it's marked as read
        $this->db->query("INSERT INTO progreso_teoria (estudiante_id, teoria_id, leido) VALUES (:estudiante_id, :teoria_id, 1) ON DUPLICATE KEY UPDATE leido = 1, fecha_leido = CURRENT_TIMESTAMP");
        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->bind(':teoria_id', $teoria_id);
        return $this->db->execute();
    }

    public function obtenerTeoriasConProgreso($leccion_id, $estudiante_id) {
        $this->db->query("
            SELECT t.*, pt.leido, pt.fecha_leido 
            FROM teoria t
            LEFT JOIN progreso_teoria pt ON t.id = pt.teoria_id AND pt.estudiante_id = :estudiante_id
            WHERE t.leccion_id = :leccion_id 
            ORDER BY t.orden ASC
        ");
        $this->db->bind(':leccion_id', $leccion_id);
        $this->db->bind(':estudiante_id', $estudiante_id);
        return $this->adjuntarBloquesATeorias($this->db->resultSet());
    }

    private function adjuntarBloquesATeorias($teorias) {
        if (empty($teorias)) {
            return $teorias;
        }

        foreach ($teorias as $teoria) {
            $this->adjuntarBloques($teoria);
        }

        return $teorias;
    }

    private function adjuntarBloques($teoria) {
        if (!$teoria || empty($teoria->id)) {
            return $teoria;
        }

        $this->db->query("
            SELECT cb.*, mr.titulo AS media_titulo, mr.tipo_media, mr.ruta_archivo, mr.alt_text, mr.metadata
            FROM contenido_bloques cb
            LEFT JOIN media_recursos mr ON mr.id = cb.media_id
            WHERE cb.teoria_id = :teoria_id
            ORDER BY cb.orden ASC, cb.id ASC
        ");
        $this->db->bind(':teoria_id', $teoria->id);
        $teoria->bloques = $this->db->resultSet();
        $teoria->tiene_bloques = !empty($teoria->bloques);

        return $teoria;
    }

    public function sincronizarBloques($teoriaId, $bloques) {
        $this->db->query("DELETE FROM contenido_bloques WHERE teoria_id = :teoria_id");
        $this->db->bind(':teoria_id', $teoriaId);
        $this->db->execute();

        $bloques = $this->normalizarBloques($bloques);
        if (empty($bloques)) {
            return true;
        }

        $this->db->query("
            INSERT INTO contenido_bloques (teoria_id, tipo_bloque, titulo, contenido, idioma_bloque, tts_habilitado, media_id, orden)
            VALUES (:teoria_id, :tipo_bloque, :titulo, :contenido, :idioma_bloque, :tts_habilitado, :media_id, :orden)
        ");

        foreach ($bloques as $index => $bloque) {
            $this->db->bind(':teoria_id', $teoriaId);
            $this->db->bind(':tipo_bloque', $bloque['tipo_bloque']);
            $this->db->bind(':titulo', $bloque['titulo']);
            $this->db->bind(':contenido', $bloque['contenido']);
            $this->db->bind(':idioma_bloque', $bloque['idioma_bloque']);
            $this->db->bind(':tts_habilitado', $bloque['tts_habilitado']);
            $this->db->bind(':media_id', $bloque['media_id']);
            $this->db->bind(':orden', $index + 1);
            $this->db->execute();
        }

        return true;
    }

    private function normalizarBloques($bloques) {
        if (!is_array($bloques)) {
            return [];
        }

        $normalizados = [];

        foreach ($bloques as $bloque) {
            if (!is_array($bloque)) {
                continue;
            }

            $contenido = trim((string) ($bloque['contenido'] ?? ''));
            $titulo = trim((string) ($bloque['titulo'] ?? ''));

            if ($contenido === '' && $titulo === '') {
                continue;
            }

            $normalizados[] = [
                'tipo_bloque' => $bloque['tipo_bloque'] ?? 'explicacion',
                'titulo' => $titulo !== '' ? $titulo : null,
                'contenido' => $contenido !== '' ? $contenido : null,
                'idioma_bloque' => !empty($bloque['idioma_bloque']) ? $bloque['idioma_bloque'] : null,
                'tts_habilitado' => !empty($bloque['tts_habilitado']) ? 1 : 0,
                'media_id' => !empty($bloque['media_id']) ? (int) $bloque['media_id'] : null,
            ];
        }

        return $normalizados;
    }
}
