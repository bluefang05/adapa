<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

class Actividad {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function crearActividad($datos) {
        $this->db->query("INSERT INTO actividades (leccion_id, titulo, descripcion, tipo_actividad, instrucciones, contenido, puntos_maximos, tiempo_limite_minutos, intentos_permitidos, es_calificable, orden, estado) VALUES (:leccion_id, :titulo, :descripcion, :tipo_actividad, :instrucciones, :contenido, :puntos_maximos, :tiempo_limite_minutos, :intentos_permitidos, :es_calificable, :orden, :estado)");

        $this->db->bind(':leccion_id', $datos['leccion_id']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':tipo_actividad', $datos['tipo_actividad']);
        $this->db->bind(':instrucciones', $datos['instrucciones'] ?? ($datos['descripcion'] ?? ''));
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':puntos_maximos', $datos['puntos_maximos']);
        $this->db->bind(':tiempo_limite_minutos', $datos['tiempo_limite_minutos'] ?: null);
        $this->db->bind(':intentos_permitidos', $datos['intentos_permitidos'] ?? 3);
        $this->db->bind(':es_calificable', $datos['es_calificable'] ?? 1);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':estado', $datos['estado'] ?? 'activa');

        return $this->db->execute();
    }

    public function obtenerActividadPorId($id) {
        $this->db->query("SELECT * FROM actividades WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function obtenerSiguienteActividadEnLeccion($leccion_id, $actividad_id) {
        $this->db->query("SELECT orden FROM actividades WHERE id = :id AND leccion_id = :leccion_id");
        $this->db->bind(':id', $actividad_id);
        $this->db->bind(':leccion_id', $leccion_id);
        $actual = $this->db->single();

        if (!$actual) {
            return null;
        }

        $this->db->query("SELECT * FROM actividades WHERE leccion_id = :leccion_id AND orden > :orden ORDER BY orden ASC LIMIT 1");
        $this->db->bind(':leccion_id', $leccion_id);
        $this->db->bind(':orden', $actual->orden);
        return $this->db->single();
    }

    public function obtenerActividadesPorLeccion($leccion_id) {
        $this->db->query("SELECT * FROM actividades WHERE leccion_id = :leccion_id ORDER BY orden ASC");
        $this->db->bind(':leccion_id', $leccion_id);
        return $this->db->resultSet();
    }

    public function moverActividad($id, $direction) {
        $actividad = $this->obtenerActividadPorId($id);
        if (!$actividad) {
            return false;
        }

        $operator = $direction === 'up' ? '<' : '>';
        $orderBy = $direction === 'up' ? 'DESC' : 'ASC';

        $this->db->query("
            SELECT id, orden
            FROM actividades
            WHERE leccion_id = :leccion_id
              AND orden {$operator} :orden
            ORDER BY orden {$orderBy}, id {$orderBy}
            LIMIT 1
        ");
        $this->db->bind(':leccion_id', $actividad->leccion_id);
        $this->db->bind(':orden', $actividad->orden);
        $vecina = $this->db->single();

        if (!$vecina) {
            return false;
        }

        $this->db->query("UPDATE actividades SET orden = :orden WHERE id = :id");
        $this->db->bind(':orden', $vecina->orden);
        $this->db->bind(':id', $actividad->id);
        $this->db->execute();

        $this->db->query("UPDATE actividades SET orden = :orden WHERE id = :id");
        $this->db->bind(':orden', $actividad->orden);
        $this->db->bind(':id', $vecina->id);
        return $this->db->execute();
    }

    public function obtenerActividadesConProgreso($leccion_id, $estudiante_id) {
        $this->db->query("
            SELECT a.*,
                   (SELECT COUNT(*) FROM respuestas WHERE actividad_id = a.id AND estudiante_id = :estudiante_id) as completada,
                   (SELECT puntuacion FROM respuestas WHERE actividad_id = a.id AND estudiante_id = :estudiante_id ORDER BY fecha_respuesta DESC LIMIT 1) as calificacion
            FROM actividades a
            WHERE a.leccion_id = :leccion_id
            ORDER BY a.orden ASC
        ");
        $this->db->bind(':leccion_id', $leccion_id);
        $this->db->bind(':estudiante_id', $estudiante_id);
        return $this->db->resultSet();
    }

    public function actualizarActividad($id, $datos) {
        $this->db->query("UPDATE actividades SET titulo = :titulo, descripcion = :descripcion, tipo_actividad = :tipo_actividad, instrucciones = :instrucciones, contenido = :contenido, orden = :orden, tiempo_limite_minutos = :tiempo_limite_minutos, puntos_maximos = :puntos_maximos, intentos_permitidos = :intentos_permitidos, es_calificable = :es_calificable, estado = :estado WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':tipo_actividad', $datos['tipo_actividad']);
        $this->db->bind(':instrucciones', $datos['instrucciones'] ?? ($datos['descripcion'] ?? ''));
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':tiempo_limite_minutos', $datos['tiempo_limite_minutos'] ?: null);
        $this->db->bind(':puntos_maximos', $datos['puntos_maximos']);
        $this->db->bind(':intentos_permitidos', $datos['intentos_permitidos'] ?? 3);
        $this->db->bind(':es_calificable', $datos['es_calificable'] ?? 1);
        $this->db->bind(':estado', $datos['estado'] ?? 'activa');

        return $this->db->execute();
    }

    public function eliminarActividad($id) {
        $this->db->query("DELETE FROM actividades WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function obtenerTotalActividadesPorLeccion($leccion_id) {
        $this->db->query("SELECT COUNT(*) as total FROM actividades WHERE leccion_id = :leccion_id");
        $this->db->bind(':leccion_id', $leccion_id);
        $resultado = $this->db->single();
        return $resultado ? $resultado->total : 0;
    }

    public function obtenerSiguienteOrdenPorLeccion($leccionId) {
        $this->db->query("SELECT MAX(orden) as max_orden FROM actividades WHERE leccion_id = :leccion_id");
        $this->db->bind(':leccion_id', $leccionId);
        $resultado = $this->db->single();
        return $resultado && $resultado->max_orden ? ((int) $resultado->max_orden + 1) : 1;
    }

    public function duplicarActividad($id, $targetLeccionId = null, $appendCopyLabel = true) {
        $actividad = $this->obtenerActividadPorId($id);
        if (!$actividad) {
            return null;
        }

        $leccionId = $targetLeccionId ?: $actividad->leccion_id;
        $titulo = trim((string) $actividad->titulo);
        if ($appendCopyLabel) {
            $titulo .= ' (copia)';
        }

        $datos = [
            'leccion_id' => $leccionId,
            'titulo' => $titulo,
            'descripcion' => $actividad->descripcion,
            'tipo_actividad' => $actividad->tipo_actividad,
            'instrucciones' => $actividad->instrucciones,
            'contenido' => $actividad->contenido,
            'puntos_maximos' => $actividad->puntos_maximos,
            'tiempo_limite_minutos' => $actividad->tiempo_limite_minutos,
            'intentos_permitidos' => $actividad->intentos_permitidos,
            'es_calificable' => $actividad->es_calificable,
            'orden' => $this->obtenerSiguienteOrdenPorLeccion($leccionId),
            'estado' => 'activa',
        ];

        if (!$this->crearActividad($datos)) {
            return null;
        }

        $nuevoId = (int) $this->db->lastInsertId();
        $this->duplicarOpcionesMultiples($id, $nuevoId);

        return $nuevoId;
    }

    private function duplicarOpcionesMultiples($actividadOrigenId, $actividadNuevaId) {
        $this->db->query("SELECT texto, es_correcta, orden FROM opciones_multiples WHERE actividad_id = :actividad_id ORDER BY orden ASC, id ASC");
        $this->db->bind(':actividad_id', $actividadOrigenId);
        $opciones = $this->db->resultSet();

        if (empty($opciones)) {
            return true;
        }

        $this->db->query("INSERT INTO opciones_multiples (actividad_id, texto, es_correcta, orden) VALUES (:actividad_id, :texto, :es_correcta, :orden)");
        foreach ($opciones as $opcion) {
            $this->db->bind(':actividad_id', $actividadNuevaId);
            $this->db->bind(':texto', $opcion->texto);
            $this->db->bind(':es_correcta', $opcion->es_correcta);
            $this->db->bind(':orden', $opcion->orden);
            $this->db->execute();
        }

        return true;
    }

    public function obtenerTiposActividadDisponibles() {
        return [
            'opcion_multiple' => 'Opcion Multiple',
            'verdadero_falso' => 'Verdadero/Falso',
            'completar_oracion' => 'Completar Oracion',
            'emparejamiento' => 'Emparejamiento',
            'ordenar_palabras' => 'Ordenar Palabras',
            'pronunciacion' => 'Pronunciacion',
            'escritura' => 'Escritura',
            'escucha' => 'Escucha',
            'arrastrar_soltar' => 'Arrastrar y Soltar',
            'respuesta_corta' => 'Respuesta Corta'
        ];
    }

    public function guardarRespuestaEstudiante($datos) {
        $actividad = $this->obtenerActividadPorId($datos['actividad_id']);
        $puntuacion = null;

        if ($actividad) {
            $puntuacion = !empty($datos['es_correcta']) ? ($actividad->puntos_maximos ?? 0) : 0;
        }

        $this->db->query("INSERT INTO respuestas (estudiante_id, actividad_id, respuesta_texto, puntuacion, fecha_respuesta) VALUES (:estudiante_id, :actividad_id, :respuesta_texto, :puntuacion, NOW())");
        $this->db->bind(':estudiante_id', $datos['estudiante_id']);
        $this->db->bind(':actividad_id', $datos['actividad_id']);
        $this->db->bind(':respuesta_texto', (string) $datos['respuesta']);
        $this->db->bind(':puntuacion', $puntuacion);

        return $this->db->execute();
    }

    public function obtenerRespuestaEstudiante($estudiante_id, $actividad_id) {
        $this->db->query("SELECT * FROM respuestas WHERE estudiante_id = :estudiante_id AND actividad_id = :actividad_id ORDER BY fecha_respuesta DESC LIMIT 1");
        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->bind(':actividad_id', $actividad_id);
        return $this->db->single();
    }
}
