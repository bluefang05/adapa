<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Actividad.php';
require_once __DIR__ . '/OpcionMultiple.php';

class Respuesta {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function guardarRespuesta($estudiante_id, $actividad_id, $respuesta_texto, $puntuacion = null) {
        try {
            $this->db->query("
                INSERT INTO respuestas (estudiante_id, actividad_id, respuesta_texto, puntuacion, fecha_respuesta)
                VALUES (:estudiante_id, :actividad_id, :respuesta_texto, :puntuacion, NOW())
            ");

            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':actividad_id', $actividad_id);
            $this->db->bind(':respuesta_texto', $respuesta_texto);
            $this->db->bind(':puntuacion', $puntuacion);

            $resultado = $this->db->execute();
            if ($resultado) {
                $this->registrarIntentoActividad($estudiante_id, $actividad_id, $respuesta_texto, $puntuacion);
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log('Error al guardar respuesta: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizarCalificacion($respuesta_id, $puntuacion, $comentarios) {
        try {
            $this->db->query("UPDATE respuestas SET puntuacion = :puntuacion, comentarios = :comentarios WHERE id = :id");
            $this->db->bind(':puntuacion', $puntuacion);
            $this->db->bind(':comentarios', $comentarios);
            $this->db->bind(':id', $respuesta_id);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Error al actualizar calificacion: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerRespuestaPorId($id) {
        try {
            $this->db->query("SELECT * FROM respuestas WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Error al obtener respuesta: ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerRespuestasPorCurso($curso_id) {
        try {
            $this->db->query("
                SELECT r.*, a.titulo as actividad_titulo, a.tipo_actividad,
                       l.titulo as leccion_titulo, u.nombre as estudiante_nombre, u.apellido as estudiante_apellido
                FROM respuestas r
                JOIN actividades a ON r.actividad_id = a.id
                JOIN lecciones l ON a.leccion_id = l.id
                JOIN usuarios u ON r.estudiante_id = u.id
                WHERE l.curso_id = :curso_id
                ORDER BY r.fecha_respuesta DESC
            ");
            $this->db->bind(':curso_id', $curso_id);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Error al obtener respuestas del curso: ' . $e->getMessage());
            return [];
        }
    }

    public function contarRespuestasPendientesPorCurso($curso_id) {
        try {
            $this->db->query("
                SELECT COUNT(*) as total
                FROM respuestas r
                JOIN actividades a ON r.actividad_id = a.id
                JOIN lecciones l ON a.leccion_id = l.id
                WHERE l.curso_id = :curso_id
                AND r.puntuacion IS NULL
                AND (a.tipo_actividad = 'escritura' OR a.tipo_actividad = 'escucha')
            ");
            $this->db->bind(':curso_id', $curso_id);
            $result = $this->db->single();
            return $result ? $result->total : 0;
        } catch (PDOException $e) {
            error_log('Error al contar respuestas pendientes: ' . $e->getMessage());
            return 0;
        }
    }

    public function estudianteYaRespondio($estudiante_id, $actividad_id) {
        try {
            $this->db->query("
                SELECT COUNT(*) as total
                FROM respuestas
                WHERE estudiante_id = :estudiante_id AND actividad_id = :actividad_id
            ");
            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':actividad_id', $actividad_id);
            $result = $this->db->single();
            return $result && $result->total > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar respuesta: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerRespuestaPorEstudianteYActividad($estudiante_id, $actividad_id) {
        try {
            $this->db->query("
                SELECT *
                FROM respuestas
                WHERE estudiante_id = :estudiante_id AND actividad_id = :actividad_id
                ORDER BY fecha_respuesta DESC
                LIMIT 1
            ");
            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':actividad_id', $actividad_id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log('Error al obtener respuesta: ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerRespuestasPorEstudianteYCurso($estudiante_id, $curso_id) {
        try {
            $this->db->query("
                SELECT r.*, a.titulo as actividad_titulo, l.titulo as leccion_titulo
                FROM respuestas r
                JOIN actividades a ON r.actividad_id = a.id
                JOIN lecciones l ON a.leccion_id = l.id
                WHERE r.estudiante_id = :estudiante_id AND l.curso_id = :curso_id
                ORDER BY r.fecha_respuesta DESC
            ");
            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':curso_id', $curso_id);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('Error al obtener respuestas: ' . $e->getMessage());
            return [];
        }
    }

    public function calcularPuntuacion($actividad_id, $respuesta_texto) {
        $actividadModel = new Actividad();
        $actividad = $actividadModel->obtenerActividadPorId($actividad_id);

        if (!$actividad) {
            return null;
        }

        $respuestasEstudiante = json_decode($respuesta_texto, true);
        $esMultiPregunta = is_array($respuestasEstudiante);

        switch ($actividad->tipo_actividad) {
            case 'opcion_multiple':
            case 'verdadero_falso':
                if ($actividad->tipo_actividad === 'verdadero_falso') {
                    $contenido = json_decode($actividad->contenido ?? '{}');
                    if (isset($contenido->preguntas)) {
                        return $this->calcularOpcionMultiple($actividad, $respuestasEstudiante, $respuesta_texto, $esMultiPregunta);
                    }
                    return $this->calcularVerdaderoFalso($actividad, $respuesta_texto);
                }
                return $this->calcularOpcionMultiple($actividad, $respuestasEstudiante, $respuesta_texto, $esMultiPregunta);
            case 'respuesta_corta':
                return $this->calcularRespuestaCorta($actividad, $respuesta_texto);
            case 'arrastrar_soltar':
                return $this->calcularArrastrarSoltar($actividad, $respuestasEstudiante);
            case 'ordenar_palabras':
                return $this->calcularOrdenarPalabras($actividad, $respuestasEstudiante);
            case 'emparejamiento':
                return $this->calcularEmparejamiento($actividad, $respuestasEstudiante);
            case 'completar_oracion':
                return $this->calcularCompletarOracion($actividad, $respuestasEstudiante);
            case 'pronunciacion':
                return $this->calcularPronunciacion($actividad, $respuestasEstudiante);
            case 'escritura':
                return null;
            case 'escucha':
                return $this->calcularEscucha($actividad, $respuesta_texto);
            default:
                return 0;
        }
    }

    private function calcularEscucha($actividad, $respuesta_texto) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $transcripcion = $contenido->transcripcion ?? '';

        $respuestaUsuario = trim(strip_tags($respuesta_texto));
        $respuestaNorm = strtolower(preg_replace('/[.,;!?]/', '', $respuestaUsuario));
        $transcripcionNorm = strtolower(preg_replace('/[.,;!?]/', '', $transcripcion));

        if ($respuestaNorm === $transcripcionNorm && $respuestaNorm !== '') {
            return $actividad->puntos_maximos ?? 10;
        }

        return null;
    }

    private function calcularCompletarOracion($actividad, $respuestasEstudiante) {
        $contenido = json_decode($actividad->contenido ?? '[]');
        if (!is_array($contenido)) {
            $contenido = [json_decode($actividad->contenido ?? '{}')];
        }

        $puntosTotales = 0;
        $preguntasCount = count($contenido);
        $puntosPorPregunta = ($actividad->puntos_maximos ?? 10) / max(1, $preguntasCount);

        foreach ($contenido as $idx => $item) {
            $id = $item->id ?? $idx;
            $respuestaCorrecta = $item->respuesta_correcta ?? '';
            $respuestaUsuario = '';

            if (is_array($respuestasEstudiante)) {
                $respuestaUsuario = $respuestasEstudiante[$id] ?? $respuestasEstudiante[$idx] ?? '';
            } else {
                $respuestaUsuario = $respuestasEstudiante;
            }

            $respuestaUsuario = strtolower(trim((string) $respuestaUsuario));
            $respuestaCorrecta = strtolower(trim((string) $respuestaCorrecta));

            if ($respuestaUsuario === $respuestaCorrecta && $respuestaUsuario !== '') {
                $puntosTotales += $puntosPorPregunta;
            }
        }

        return round($puntosTotales, 2);
    }

    private function calcularPronunciacion($actividad, $respuestasEstudiante) {
        $contenido = json_decode($actividad->contenido ?? '[]');
        if (!is_array($contenido)) {
            $contenido = [json_decode($actividad->contenido ?? '{}')];
        }

        $puntosTotales = 0;
        $preguntasCount = count($contenido);
        $puntosPorPregunta = ($actividad->puntos_maximos ?? 10) / max(1, $preguntasCount);

        foreach ($contenido as $idx => $item) {
            $id = $item->id ?? "q$idx";
            $fraseTarget = $item->frase ?? '';
            $respuestaUsuario = is_array($respuestasEstudiante) ? ($respuestasEstudiante[$id] ?? '') : $respuestasEstudiante;

            $target = strtolower(trim(preg_replace('/[^\w\s]/', '', $fraseTarget)));
            $response = strtolower(trim(preg_replace('/[^\w\s]/', '', (string) $respuestaUsuario)));

            if ($response !== '' && (($target === $response) || (levenshtein($target, $response) < 3))) {
                $puntosTotales += $puntosPorPregunta;
            }
        }

        return round($puntosTotales, 2);
    }

    private function calcularArrastrarSoltar($actividad, $respuestasEstudiante) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $puntosMaximos = $actividad->puntos_maximos ?? 10;

        if (isset($contenido->solucion)) {
            $solucion = (array) $contenido->solucion;
            if (empty($solucion)) {
                return 0;
            }

            $respuestas = $respuestasEstudiante;
            if (is_string($respuestas)) {
                $respuestas = json_decode($respuestas, true);
            }
            if (!is_array($respuestas)) {
                return 0;
            }

            $totalItems = count($solucion);
            $puntosPorItem = $puntosMaximos / $totalItems;
            $puntos = 0;

            foreach ($solucion as $item => $targetCorrecto) {
                if (isset($respuestas[$item]) && $respuestas[$item] === $targetCorrecto) {
                    $puntos += $puntosPorItem;
                }
            }

            return round($puntos, 2);
        }

        if (isset($contenido->pairs) && is_array($contenido->pairs)) {
            $pairs = $contenido->pairs;
            $totalPairs = count($pairs);
            if ($totalPairs === 0) {
                return 0;
            }

            $puntosPorPar = $puntosMaximos / $totalPairs;
            $puntos = 0;
            $mapa = [];

            foreach ($pairs as $pair) {
                if (isset($pair->id) && isset($pair->right)) {
                    $mapa[$pair->id] = $pair->right;
                }
            }

            $respuestas = $respuestasEstudiante;
            if (is_string($respuestas)) {
                $respuestas = json_decode($respuestas, true);
            }
            if (!is_array($respuestas)) {
                return 0;
            }

            foreach ($respuestas as $qId => $answer) {
                if (isset($mapa[$qId]) && $mapa[$qId] === $answer) {
                    $puntos += $puntosPorPar;
                }
            }

            return round($puntos, 2);
        }

        $correctos = 0;
        $totalMatches = 0;

        if (isset($contenido->matches) && (is_object($contenido->matches) || is_array($contenido->matches))) {
            $totalMatches = count((array) $contenido->matches);
            $matches = (array) $contenido->matches;
            $respuestas = $respuestasEstudiante;
            if (is_string($respuestas)) {
                $respuestas = json_decode($respuestas, true);
            }

            if (is_array($respuestas)) {
                foreach ($respuestas as $itemId => $targetId) {
                    if (isset($matches[$itemId]) && $matches[$itemId] === $targetId) {
                        $correctos++;
                    }
                }
            }
        } elseif (is_array($contenido)) {
            $totalMatches = count($contenido);
            $mapaCorrecto = [];
            foreach ($contenido as $pair) {
                if (isset($pair->id) && isset($pair->right)) {
                    $mapaCorrecto[$pair->id] = $pair->right;
                }
            }

            $respuestas = $respuestasEstudiante;
            if (is_string($respuestas)) {
                $respuestas = json_decode($respuestas, true);
            }

            if (is_array($respuestas)) {
                foreach ($respuestas as $itemId => $respuesta) {
                    if (isset($mapaCorrecto[$itemId]) && ($mapaCorrecto[$itemId] === $respuesta || $itemId === $respuesta)) {
                        $correctos++;
                    }
                }
            }
        }

        return $totalMatches > 0 ? ($correctos / $totalMatches) * $puntosMaximos : 0;
    }

    private function calcularEmparejamiento($actividad, $respuestasEstudiante) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $pares = $contenido->pares ?? [];

        if (empty($pares) || !is_array($pares)) {
            return 0;
        }

        $puntosTotales = 0;
        $puntosPorPar = ($actividad->puntos_maximos ?? 10) / max(1, count($pares));

        foreach ($pares as $idx => $par) {
            $respuestaUsuario = is_array($respuestasEstudiante) ? ($respuestasEstudiante[$idx] ?? '') : '';
            if (trim((string) $respuestaUsuario) === trim((string) ($par->right ?? ''))) {
                $puntosTotales += $puntosPorPar;
            }
        }

        return round($puntosTotales, 2);
    }

    private function calcularOpcionMultiple($actividad, $respuestasEstudiante, $respuesta_texto, $esMultiPregunta) {
        $opcionesModel = new OpcionMultiple();
        $opcionesCorrectasDB = $opcionesModel->obtenerOpcionesCorrectas($actividad->id);

        if (!empty($opcionesCorrectasDB)) {
            $respuesta = $esMultiPregunta ? ($respuestasEstudiante[0] ?? reset($respuestasEstudiante)) : $respuesta_texto;
            foreach ($opcionesCorrectasDB as $opcion) {
                if (($opcion->texto ?? '') === $respuesta) {
                    return $actividad->puntos_maximos ?? 10;
                }
            }
            return 0;
        }

        if (!empty($actividad->contenido)) {
            $contenido = json_decode($actividad->contenido);

            if (isset($contenido->preguntas) && is_array($contenido->preguntas)) {
                $preguntasCount = count($contenido->preguntas);
                $puntosPorPregunta = ($actividad->puntos_maximos ?? 10) / max(1, $preguntasCount);
                $puntosTotales = 0;

                foreach ($contenido->preguntas as $idx => $pregunta) {
                    $respuestaUsuario = $esMultiPregunta ? ($respuestasEstudiante[$idx] ?? null) : $respuesta_texto;
                    if (!$respuestaUsuario || !isset($pregunta->opciones) || !is_array($pregunta->opciones)) {
                        continue;
                    }

                    foreach ($pregunta->opciones as $opcion) {
                        $esCorrecta = (isset($opcion->es_correcta) && ($opcion->es_correcta === true || $opcion->es_correcta == 1))
                            || (isset($opcion->correcta) && ($opcion->correcta === true || $opcion->correcta == 1));
                        $texto = $opcion->texto ?? $opcion->opcion_texto ?? '';
                        if ($esCorrecta && $texto === $respuestaUsuario) {
                            $puntosTotales += $puntosPorPregunta;
                            break;
                        }
                    }
                }

                return round($puntosTotales, 2);
            }

            if (isset($contenido->opciones) && is_array($contenido->opciones)) {
                $respuestaUsuario = $esMultiPregunta ? ($respuestasEstudiante[0] ?? reset($respuestasEstudiante)) : $respuesta_texto;

                if (isset($contenido->respuesta_correcta) && is_numeric($contenido->respuesta_correcta)) {
                    $correctIndex = (int) $contenido->respuesta_correcta;
                    $correctOption = $contenido->opciones[$correctIndex] ?? null;
                    if (is_string($correctOption) && $correctOption === $respuestaUsuario) {
                        return $actividad->puntos_maximos ?? 10;
                    }
                }

                foreach ($contenido->opciones as $opcion) {
                    if (!is_object($opcion) && !is_array($opcion)) {
                        continue;
                    }

                    $opcion = (object) $opcion;
                    $esCorrecta = isset($opcion->correcta) && ($opcion->correcta === true || $opcion->correcta == 1);
                    if ($esCorrecta && isset($opcion->texto) && $opcion->texto === $respuestaUsuario) {
                        return $actividad->puntos_maximos ?? 10;
                    }
                }
            }
        }

        return 0;
    }

    private function calcularVerdaderoFalso($actividad, $respuesta_texto) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $correcta = $contenido->respuesta_correcta ?? null;

        if ($correcta && strcasecmp($respuesta_texto, $correcta) === 0) {
            return $actividad->puntos_maximos ?? 10;
        }

        return 0;
    }

    private function calcularRespuestaCorta($actividad, $respuesta_texto) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $aceptadas = [];

        if (isset($contenido->respuesta_correcta)) {
            $aceptadas[] = $contenido->respuesta_correcta;
        }

        if (isset($contenido->variaciones) && is_array($contenido->variaciones)) {
            $aceptadas = array_merge($aceptadas, $contenido->variaciones);
        }

        if (isset($contenido->respuestas_correctas)) {
            $aceptadas = array_merge($aceptadas, (array) $contenido->respuestas_correctas);
        } elseif (isset($contenido->respuestas_aceptadas)) {
            $aceptadas = array_merge($aceptadas, (array) $contenido->respuestas_aceptadas);
        }

        foreach ($aceptadas as $opcion) {
            if (mb_strtolower(trim($respuesta_texto), 'UTF-8') === mb_strtolower(trim($opcion), 'UTF-8')) {
                return $actividad->puntos_maximos ?? 10;
            }
        }

        return 0;
    }

    private function calcularOrdenarPalabras($actividad, $respuestasEstudiante) {
        $contenido = json_decode($actividad->contenido);
        if (is_array($contenido) && isset($contenido[0]->items)) {
            $preguntas = $contenido;
        } elseif (isset($contenido->items)) {
            $preguntas = [$contenido];
        } else {
            return 0;
        }

        $respuestasNormalizadas = [];
        if (is_array($respuestasEstudiante)) {
            $isSingleList = true;
            foreach ($respuestasEstudiante as $key => $value) {
                if (!is_int($key) || !is_string($value)) {
                    $isSingleList = false;
                    break;
                }
                if (is_string($value) && (str_starts_with(trim($value), '[') || str_starts_with(trim($value), '{'))) {
                    $isSingleList = false;
                    break;
                }
            }

            if ($isSingleList && count($preguntas) === 1) {
                $qId = $preguntas[0]->id ?? 'q0';
                $respuestasNormalizadas[$qId] = $respuestasEstudiante;
            } else {
                foreach ($respuestasEstudiante as $key => $value) {
                    $decoded = is_string($value) ? json_decode($value, true) : null;
                    $respuestasNormalizadas[$key] = is_array($decoded) ? $decoded : $value;
                }
            }
        }

        $totalQuestions = count($preguntas);
        if ($totalQuestions === 0) {
            return 0;
        }

        $correctCount = 0;
        foreach ($preguntas as $idx => $pregunta) {
            $qId = $pregunta->id ?? "q$idx";
            $correctOrder = $pregunta->items ?? [];
            $studentOrder = $respuestasNormalizadas[$qId] ?? ($respuestasNormalizadas["q$idx"] ?? []);
            if (array_values($correctOrder) === array_values($studentOrder)) {
                $correctCount++;
            }
        }

        return ($correctCount / $totalQuestions) * ($actividad->puntos_maximos ?? 10);
    }

    private function registrarIntentoActividad($estudiante_id, $actividad_id, $respuesta_texto, $puntuacion) {
        try {
            $this->db->query("SELECT COUNT(*) AS total, MAX(COALESCE(calificacion, 0)) AS mejor_calificacion FROM intentos_actividades WHERE estudiante_id = :estudiante_id AND actividad_id = :actividad_id");
            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':actividad_id', $actividad_id);
            $stats = $this->db->single();

            $numeroIntento = ($stats->total ?? 0) + 1;
            $mejorCalificacion = isset($stats->mejor_calificacion) ? (float) $stats->mejor_calificacion : null;
            $calificacionActual = $puntuacion !== null ? (float) $puntuacion : null;
            $esMejorIntento = $calificacionActual !== null && ($mejorCalificacion === null || $calificacionActual >= $mejorCalificacion) ? 1 : 0;

            $this->db->query("
                INSERT INTO intentos_actividades (
                    estudiante_id,
                    actividad_id,
                    intentos_numero,
                    respuestas,
                    puntos_obtenidos,
                    calificacion,
                    fecha_inicio,
                    fecha_fin,
                    tiempo_empleado_minutos,
                    es_mejor_intento
                ) VALUES (
                    :estudiante_id,
                    :actividad_id,
                    :intentos_numero,
                    :respuestas,
                    :puntos_obtenidos,
                    :calificacion,
                    NOW(),
                    NOW(),
                    NULL,
                    :es_mejor_intento
                )
            ");
            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':actividad_id', $actividad_id);
            $this->db->bind(':intentos_numero', $numeroIntento);
            $this->db->bind(':respuestas', $respuesta_texto);
            $this->db->bind(':puntos_obtenidos', $calificacionActual);
            $this->db->bind(':calificacion', $calificacionActual);
            $this->db->bind(':es_mejor_intento', $esMejorIntento);
            $this->db->execute();

            if ($esMejorIntento) {
                $this->db->query("
                    UPDATE intentos_actividades
                    SET es_mejor_intento = CASE WHEN id = :id_actual THEN 1 ELSE 0 END
                    WHERE estudiante_id = :estudiante_id AND actividad_id = :actividad_id
                ");
                $this->db->bind(':id_actual', $this->db->lastInsertId());
                $this->db->bind(':estudiante_id', $estudiante_id);
                $this->db->bind(':actividad_id', $actividad_id);
                $this->db->execute();
            }
        } catch (PDOException $e) {
            error_log('Error al registrar intento de actividad: ' . $e->getMessage());
        }
    }
}
