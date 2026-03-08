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

    private static function contenidoComoArray($actividad) {
        $contenido = $actividad->contenido ?? [];
        if (is_array($contenido)) {
            return $contenido;
        }

        if (is_string($contenido) && trim($contenido) !== '') {
            $decoded = json_decode($contenido, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private static function recursoApoyo($contenido) {
        if (!is_array($contenido)) {
            return null;
        }

        $title = trim((string) ($contenido['recurso_apoyo_titulo'] ?? ''));
        $url = trim((string) ($contenido['recurso_apoyo_url'] ?? ''));
        if ($title === '' && $url === '') {
            return null;
        }

        return [
            'title' => $title !== '' ? $title : 'Recurso de apoyo',
            'url' => $url,
            'type' => trim((string) ($contenido['recurso_apoyo_tipo'] ?? '')),
            'media_id' => (int) ($contenido['recurso_apoyo_media_id'] ?? 0),
        ];
    }

    public static function resumenDocente($actividad) {
        $contenido = self::contenidoComoArray($actividad);
        $tipo = (string) ($actividad->tipo_actividad ?? '');
        $questionCount = 0;
        $itemCount = 0;
        $configReady = false;

        switch ($tipo) {
            case 'opcion_multiple':
            case 'verdadero_falso':
            case 'escucha':
                $questionCount = count((array) ($contenido['preguntas'] ?? []));
                if ($questionCount === 0 && !empty($contenido['afirmacion'])) {
                    $questionCount = 1;
                }
                $configReady = $questionCount > 0;
                break;

            case 'ordenar_palabras':
                $itemCount = count((array) ($contenido['items'] ?? []));
                $configReady = $itemCount >= 2;
                break;

            case 'arrastrar_soltar':
                $itemCount = count((array) ($contenido['items'] ?? []));
                $configReady = $itemCount >= 2 && count((array) ($contenido['targets'] ?? [])) >= 1;
                break;

            case 'emparejamiento':
                $itemCount = count((array) ($contenido['items'] ?? []));
                $configReady = $itemCount >= 2;
                break;

            case 'respuesta_corta':
            case 'respuesta_larga':
                $configReady = !empty($contenido['pregunta']) || !empty($contenido['respuestas_correctas']);
                $questionCount = $configReady ? 1 : 0;
                break;

            case 'completar_oracion':
                $configReady = !empty($contenido['texto_completo']) || !empty($contenido['respuestas_correctas']);
                $questionCount = $configReady ? 1 : 0;
                break;

            case 'proyecto':
            case 'codigo':
                $configReady = !empty($contenido['instrucciones']) || !empty($contenido['codigo_inicial']);
                $questionCount = $configReady ? 1 : 0;
                break;

            default:
                $configReady = !empty($contenido);
                break;
        }

        $supportResource = self::recursoApoyo($contenido);
        $score = 0;

        if (trim((string) ($actividad->titulo ?? '')) !== '') {
            $score += 10;
        }
        if (trim((string) ($actividad->descripcion ?? '')) !== '') {
            $score += 10;
        }
        if ((int) ($actividad->puntos_maximos ?? 0) > 0) {
            $score += 10;
        }
        if ((int) ($actividad->tiempo_limite_minutos ?? 0) > 0) {
            $score += 5;
        }
        if ($configReady) {
            $score += 40;
        }
        if ($questionCount > 0 || $itemCount > 0) {
            $score += 15;
        }
        if ($supportResource) {
            $score += 10;
        }
        $score = min(100, $score);

        $tone = 'warning';
        $label = 'Necesita configuracion';
        $message = 'Todavia le falta estructura interna antes de probarla como alumno.';
        $actionLabel = 'Configurar';

        if (!$configReady) {
            $message = 'Define preguntas, items o consigna real antes de darla por lista.';
        } elseif ($score >= 85) {
            $tone = 'success';
            $label = 'Lista para probar';
            $message = 'Tiene base suficiente para revisarla como alumno y afinar detalles.';
            $actionLabel = 'Probar';
        } elseif (!$supportResource && in_array($tipo, ['escucha', 'arrastrar_soltar', 'emparejamiento', 'respuesta_corta', 'respuesta_larga', 'codigo', 'proyecto'], true)) {
            $tone = 'info';
            $label = 'Base lista';
            $message = 'La actividad funciona, pero un recurso de apoyo puede volverla mas clara o memorable.';
            $actionLabel = 'Vincular apoyo';
        } else {
            $tone = 'accent';
            $label = 'Lista para pulir';
            $message = 'La configuracion ya existe. Conviene probar copy, tiempo y claridad de la consigna.';
            $actionLabel = 'Pulir';
        }

        return [
            'score' => $score,
            'tone' => $tone,
            'label' => $label,
            'message' => $message,
            'action_label' => $actionLabel,
            'config_ready' => $configReady,
            'question_count' => $questionCount,
            'item_count' => $itemCount,
            'support_resource' => $supportResource,
            'has_support_resource' => $supportResource !== null,
        ];
    }
}
