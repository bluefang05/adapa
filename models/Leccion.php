<?php

require_once __DIR__ . '/../core/Database.php';

class Leccion {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerLeccionesPorCurso($curso_id) {
        $this->db->query("SELECT * FROM lecciones WHERE curso_id = :curso_id ORDER BY orden ASC, id ASC");
        $this->db->bind(':curso_id', $curso_id);
        return $this->db->resultSet();
    }

    public function obtenerLeccionPorId($id) {
        $this->db->query("SELECT * FROM lecciones WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function crearLeccion($datos) {
        $this->db->query("INSERT INTO lecciones (curso_id, titulo, descripcion, orden, duracion_minutos, es_obligatoria, estado) VALUES (:curso_id, :titulo, :descripcion, :orden, :duracion_minutos, :es_obligatoria, :estado)");

        $this->db->bind(':curso_id', $datos['curso_id']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':duracion_minutos', $datos['duracion_minutos']);
        $this->db->bind(':es_obligatoria', $datos['es_obligatoria']);
        $this->db->bind(':estado', $datos['estado']);

        return $this->db->execute();
    }

    public function obtenerUltimaLeccionCreada() {
        return $this->db->lastInsertId();
    }

    public function actualizarLeccion($id, $datos) {
        $this->db->query("UPDATE lecciones SET titulo = :titulo, descripcion = :descripcion, orden = :orden, duracion_minutos = :duracion_minutos, es_obligatoria = :es_obligatoria, estado = :estado WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':duracion_minutos', $datos['duracion_minutos']);
        $this->db->bind(':es_obligatoria', $datos['es_obligatoria']);
        $this->db->bind(':estado', $datos['estado']);

        return $this->db->execute();
    }

    public function eliminarLeccion($id) {
        $this->db->query("DELETE FROM lecciones WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function obtenerSiguienteOrden($curso_id) {
        $this->db->query("SELECT MAX(orden) as max_orden FROM lecciones WHERE curso_id = :curso_id");
        $this->db->bind(':curso_id', $curso_id);
        $resultado = $this->db->single();
        return $resultado->max_orden ? $resultado->max_orden + 1 : 1;
    }

    public function obtenerLeccionesConContenido($curso_id) {
        $this->db->query("SELECT l.*, 
                        (SELECT COUNT(*) FROM teoria WHERE leccion_id = l.id) as total_teorias,
                        (SELECT COUNT(*) FROM actividades WHERE leccion_id = l.id) as total_actividades
                        FROM lecciones l 
                        WHERE l.curso_id = :curso_id 
                        ORDER BY l.orden ASC, l.id ASC");
        $this->db->bind(':curso_id', $curso_id);
        return $this->db->resultSet();
    }

    public function obtenerProgresoEstudiante($estudiante_id, $leccion_id) {
        $this->db->query("SELECT * FROM progreso_lecciones WHERE estudiante_id = :estudiante_id AND leccion_id = :leccion_id");
        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->bind(':leccion_id', $leccion_id);
        return $this->db->single();
    }

    public function actualizarProgreso($estudiante_id, $leccion_id, $estado, $porcentaje = 0) {
        $this->db->query("INSERT INTO progreso_lecciones (estudiante_id, leccion_id, estado, porcentaje_completado) 
                         VALUES (:estudiante_id, :leccion_id, :estado, :porcentaje) 
                         ON DUPLICATE KEY UPDATE 
                         estado = :estado, 
                         porcentaje_completado = :porcentaje,
                         fecha_completado = CASE WHEN :estado = 'completada' THEN CURRENT_TIMESTAMP ELSE fecha_completado END");

        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->bind(':leccion_id', $leccion_id);
        $this->db->bind(':estado', $estado);
        $this->db->bind(':porcentaje', $porcentaje);

        return $this->db->execute();
    }

    public function obtenerResumenProgreso($leccion_id, $estudiante_id) {
        $this->db->query("
            SELECT
                COUNT(DISTINCT t.id) AS total_teorias,
                COUNT(DISTINCT CASE WHEN pt.leido = 1 THEN t.id END) AS teorias_completadas,
                COUNT(DISTINCT a.id) AS total_actividades,
                COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN a.id END) AS actividades_completadas
            FROM lecciones l
            LEFT JOIN teoria t ON t.leccion_id = l.id
            LEFT JOIN progreso_teoria pt ON pt.teoria_id = t.id AND pt.estudiante_id = :estudiante_id
            LEFT JOIN actividades a ON a.leccion_id = l.id
            LEFT JOIN respuestas r ON r.actividad_id = a.id AND r.estudiante_id = :estudiante_id
            WHERE l.id = :leccion_id
            GROUP BY l.id
        ");
        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->bind(':leccion_id', $leccion_id);
        $resumen = $this->db->single();

        if (!$resumen) {
            return (object) [
                'total_teorias' => 0,
                'teorias_completadas' => 0,
                'total_actividades' => 0,
                'actividades_completadas' => 0,
                'total_items' => 0,
                'completados' => 0,
                'porcentaje' => 0,
                'estado' => 'pendiente',
                'completada' => false,
            ];
        }

        $totalItems = (int) $resumen->total_teorias + (int) $resumen->total_actividades;
        $completados = (int) $resumen->teorias_completadas + (int) $resumen->actividades_completadas;
        $porcentaje = $totalItems > 0 ? (int) round(($completados / $totalItems) * 100) : 0;

        if ($totalItems === 0) {
            $estado = 'pendiente';
        } elseif ($porcentaje >= 100) {
            $estado = 'completada';
        } elseif ($porcentaje > 0) {
            $estado = 'en_progreso';
        } else {
            $estado = 'pendiente';
        }

        $resumen->total_items = $totalItems;
        $resumen->completados = $completados;
        $resumen->porcentaje = $porcentaje;
        $resumen->estado = $estado;
        $resumen->completada = $estado === 'completada';

        return $resumen;
    }

    public function sincronizarProgresoEstudiante($leccion_id, $estudiante_id) {
        $resumen = $this->obtenerResumenProgreso($leccion_id, $estudiante_id);
        $this->actualizarProgreso($estudiante_id, $leccion_id, $resumen->estado, $resumen->porcentaje);
        return $resumen;
    }

    public function verificarCompletitud($leccion_id, $estudiante_id) {
        $resumen = $this->obtenerResumenProgreso($leccion_id, $estudiante_id);
        return (bool) $resumen->completada;
    }
}
