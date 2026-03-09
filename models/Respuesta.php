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

    public function guardarRespuesta($estudiante_id, $actividad_id, $respuesta_texto, $puntuacion = null, $comentarios = null) {
        try {
            $this->db->query("
                INSERT INTO respuestas (estudiante_id, actividad_id, respuesta_texto, puntuacion, comentarios, fecha_respuesta)
                VALUES (:estudiante_id, :actividad_id, :respuesta_texto, :puntuacion, :comentarios, NOW())
            ");

            $this->db->bind(':estudiante_id', $estudiante_id);
            $this->db->bind(':actividad_id', $actividad_id);
            $this->db->bind(':respuesta_texto', $respuesta_texto);
            $this->db->bind(':puntuacion', $puntuacion);
            $this->db->bind(':comentarios', $comentarios);

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
        $evaluacion = $this->evaluarRespuesta($actividad_id, $respuesta_texto);
        return $evaluacion['puntuacion'];
    }

    public function evaluarRespuesta($actividad_id, $respuesta_texto) {
        $actividadModel = new Actividad();
        $actividad = $actividadModel->obtenerActividadPorId($actividad_id);

        if (!$actividad) {
            return [
                'puntuacion' => null,
                'comentarios' => null,
            ];
        }

        $respuestasEstudiante = json_decode($respuesta_texto, true);
        $esMultiPregunta = is_array($respuestasEstudiante);

        if ($actividad->tipo_actividad === 'escritura') {
            return $this->evaluarEscritura($actividad, $respuesta_texto);
        }

        if ($actividad->tipo_actividad === 'escucha') {
            return $this->evaluarEscucha($actividad, $respuesta_texto);
        }

        if ($actividad->tipo_actividad === 'pronunciacion') {
            return $this->evaluarPronunciacion($actividad, $respuesta_texto);
        }

        return [
            'puntuacion' => $this->calcularPuntuacionAutomatica($actividad, $respuesta_texto, $respuestasEstudiante, $esMultiPregunta),
            'comentarios' => null,
        ];
    }

    private function calcularPuntuacionAutomatica($actividad, $respuesta_texto, $respuestasEstudiante, $esMultiPregunta) {
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
            default:
                return 0;
        }
    }

    private function evaluarEscucha($actividad, $respuesta_texto) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $preguntas = isset($contenido->preguntas) && is_array($contenido->preguntas) ? $contenido->preguntas : [];
        $puntosMaximos = (float) ($actividad->puntos_maximos ?? 10);

        if (!empty($preguntas)) {
            $respuestas = json_decode($respuesta_texto, true);
            if (!is_array($respuestas)) {
                $respuestas = [];
            }

            $puntosPorPregunta = $puntosMaximos / max(1, count($preguntas));
            $puntosTotales = 0.0;
            $detalles = [];

            foreach ($preguntas as $idx => $pregunta) {
                $pregunta = (object) $pregunta;
                $id = $pregunta->id ?? ('listen_' . ($idx + 1));
                $evaluacion = $this->evaluarEscuchaFragmento(
                    (string) ($pregunta->transcripcion ?? $pregunta->texto_tts ?? ''),
                    (string) ($respuestas[$id] ?? ''),
                    (array) ($pregunta->palabras_clave ?? [])
                );
                $puntosTotales += round($puntosPorPregunta * $evaluacion['ratio'], 2);
                $detalles[] = [
                    'id' => $id,
                    'label' => $pregunta->descripcion ?? ('Bloque ' . ($idx + 1)),
                    'ratio' => $evaluacion['ratio'],
                    'missing' => $evaluacion['missing'],
                    'response' => $respuestas[$id] ?? '',
                ];
            }

            $ratioPromedio = empty($detalles) ? 0.0 : array_sum(array_column($detalles, 'ratio')) / count($detalles);

            return [
                'puntuacion' => round($puntosTotales, 2),
                'comentarios' => $this->construirComentarioEscuchaMulti($ratioPromedio, $detalles),
            ];
        }

        $evaluacion = $this->evaluarEscuchaFragmento(
            (string) ($contenido->transcripcion ?? ''),
            (string) $respuesta_texto,
            (array) ($contenido->palabras_clave ?? [])
        );

        return [
            'puntuacion' => round($puntosMaximos * $evaluacion['ratio'], 2),
            'comentarios' => $this->construirComentarioEscucha($contenido, $evaluacion['ratio'], $evaluacion['overlap'], $evaluacion['response_norm']),
        ];
    }

    private function evaluarEscuchaFragmento(string $transcripcion, string $respuestaUsuario, array $palabrasClave = []): array
    {
        $transcripcion = trim($transcripcion);
        $respuestaUsuario = trim(strip_tags($respuestaUsuario));

        if ($respuestaUsuario === '' || $transcripcion === '') {
            return [
                'ratio' => 0.0,
                'overlap' => ['recall' => 0.0, 'precision' => 0.0, 'missing' => $this->tokenizarTextoEvaluacion($transcripcion), 'extra' => []],
                'response_norm' => '',
                'missing' => array_values(array_filter(array_map('strval', $palabrasClave), static fn($item) => trim($item) !== '')),
            ];
        }

        $respuestaNorm = $this->normalizarTextoEvaluacion($respuestaUsuario);
        $transcripcionNorm = $this->normalizarTextoEvaluacion($transcripcion);
        $respuestaTokens = $this->tokenizarTextoEvaluacion($respuestaUsuario);
        $transcripcionTokens = $this->tokenizarTextoEvaluacion($transcripcion);
        $overlap = $this->medirSolapamientoTokens($transcripcionTokens, $respuestaTokens);
        $stringScore = $this->scoreParecidoTexto($transcripcionNorm, $respuestaNorm);
        $completitud = min(1, count($respuestaTokens) / max(1, count($transcripcionTokens)));

        $ratio = $transcripcionNorm === $respuestaNorm
            ? 1.0
            : (($overlap['recall'] * 0.55) + ($overlap['precision'] * 0.15) + ($stringScore * 0.20) + ($completitud * 0.10));

        if ($overlap['recall'] < 0.25 && $stringScore < 0.20) {
            $ratio = min($ratio, 0.20);
        }

        $ratio = max(0.0, min(1.0, $ratio));

        $missingKeywords = [];
        foreach (array_values(array_filter(array_map('strval', $palabrasClave), static fn($item) => trim($item) !== '')) as $keyword) {
            if (!$this->contienePatron($respuestaNorm, $keyword)) {
                $missingKeywords[] = $keyword;
            }
        }

        return [
            'ratio' => $ratio,
            'overlap' => $overlap,
            'response_norm' => $respuestaNorm,
            'missing' => !empty($missingKeywords) ? $missingKeywords : array_slice((array) ($overlap['missing'] ?? []), 0, 4),
        ];
    }

    private function evaluarEscritura($actividad, $respuesta_texto) {
        $contenido = json_decode($actividad->contenido ?? '{}');
        $texto = trim(strip_tags((string) $respuesta_texto));
        $puntosMaximos = (float) ($actividad->puntos_maximos ?? 10);

        if ($texto === '') {
            return [
                'puntuacion' => 0.0,
                'comentarios' => 'No se detecto texto para evaluar. Escribe una respuesta completa en aleman antes de enviar.',
            ];
        }

        $textoNorm = $this->normalizarTextoEvaluacion($texto);
        $minPalabras = (int) ($contenido->min_palabras ?? 0);
        $minOraciones = (int) ($contenido->min_oraciones ?? max(3, (int) ceil(max(1, $minPalabras) / 25)));
        $palabrasClave = array_values(array_filter(array_map('strval', (array) ($contenido->palabras_clave ?? [])), static fn($item) => trim($item) !== ''));
        $conectores = array_values(array_filter(array_map('strval', (array) ($contenido->conectores_sugeridos ?? [])), static fn($item) => trim($item) !== ''));
        $estructuraSugerida = array_values(array_filter(array_map('strval', (array) ($contenido->estructura_sugerida ?? [])), static fn($item) => trim($item) !== ''));
        $registro = (string) ($contenido->registro ?? 'neutral');
        $patronesRegistro = array_values(array_filter(array_map('strval', (array) ($contenido->patrones_registro_objetivo ?? [])), static fn($item) => trim($item) !== ''));
        $patronesRegistroEvitar = array_values(array_filter(array_map('strval', (array) ($contenido->patrones_registro_evitar ?? [])), static fn($item) => trim($item) !== ''));
        $patronesMorfosintacticos = $this->normalizarPatronesMorfosintacticos($contenido->patrones_morfosintacticos ?? []);
        $diagnosticosGramaticales = $this->normalizarDiagnosticosGramaticales($contenido->diagnosticos_gramaticales ?? []);
        $movimientos = $this->normalizarMovimientosEscritura($contenido->movimientos_clave ?? []);

        $palabras = $this->contarPalabrasEvaluacion($texto);
        $oraciones = $this->contarOracionesEvaluacion($texto);
        $idioma = $this->medirIdiomaObjetivo($textoNorm, (string) ($contenido->idioma_objetivo ?? 'aleman'));
        $coincidenciasClave = $this->matchPatterns($textoNorm, $palabrasClave);
        $coincidenciasConectores = $this->matchPatterns($textoNorm, $conectores);
        $movimientosEvaluados = $this->evaluarMovimientosEscritura($textoNorm, $movimientos);
        $registroEvaluado = $this->evaluarRegistroEscritura($textoNorm, $registro, $patronesRegistro, $patronesRegistroEvitar);
        $pulido = $this->evaluarPulidoEscritura($texto, $registro);
        $morfosintaxis = $this->evaluarPatronesMorfosintacticos($textoNorm, $patronesMorfosintacticos, $texto);
        $diagnosticos = $this->evaluarDiagnosticosGramaticales($texto, $textoNorm, $diagnosticosGramaticales);

        $scoreLongitud = $minPalabras > 0 ? min(1, $palabras / $minPalabras) : 1.0;
        $scoreOraciones = $minOraciones > 0 ? min(1, $oraciones / $minOraciones) : 1.0;
        $objetivoClave = empty($palabrasClave) ? 1 : max(1, min(3, count($palabrasClave)));
        $objetivoConectores = empty($conectores) ? 1 : max(1, min(2, count($conectores)));
        $scoreClave = empty($palabrasClave) ? 1.0 : min(1, $coincidenciasClave / $objetivoClave);
        $scoreConectores = empty($conectores) ? 1.0 : min(1, $coincidenciasConectores / $objetivoConectores);
        $scoreMovimientos = empty($movimientos) ? 1.0 : ((float) ($movimientosEvaluados['covered'] ?? 0) / max(1, count($movimientos)));
        $scoreRegistro = (float) ($registroEvaluado['score'] ?? 1.0);
        $scorePulido = (float) ($pulido['score'] ?? 1.0);
        $scoreMorfosintaxis = empty($patronesMorfosintacticos) ? 1.0 : ((float) ($morfosintaxis['covered'] ?? 0) / max(1, count($patronesMorfosintacticos)));
        $scoreDiagnosticos = (float) ($diagnosticos['score'] ?? 1.0);

        $ratio = ($scoreLongitud * 0.18)
            + ($scoreOraciones * 0.09)
            + ($idioma['score'] * 0.14)
            + ($scoreClave * 0.10)
            + ($scoreConectores * 0.06)
            + ($scoreMovimientos * 0.12)
            + ($scoreRegistro * 0.10)
            + ($scorePulido * 0.08)
            + ($scoreMorfosintaxis * 0.08)
            + ($scoreDiagnosticos * 0.05);

        if ($minPalabras > 0 && $palabras < (int) ceil($minPalabras * 0.40)) {
            $ratio = min($ratio, 0.45);
        }

        if ($idioma['score'] < 0.35) {
            $ratio = min($ratio, 0.50);
        }

        if (!empty($registroEvaluado['forbidden_hits']) && in_array($registro, ['formal', 'academico', 'analitico', 'argumentativo'], true)) {
            $ratio = min($ratio, 0.78);
        }

        $ratio = max(0.0, min(1.0, $ratio));

        return [
            'puntuacion' => round($puntosMaximos * $ratio, 2),
            'comentarios' => $this->construirComentarioEscritura(
                $contenido,
                $textoNorm,
                $ratio,
                $palabras,
                $oraciones,
                $minPalabras,
                $minOraciones,
                $idioma,
                $palabrasClave,
                $coincidenciasClave,
                $conectores,
                $coincidenciasConectores,
                $estructuraSugerida,
                $movimientosEvaluados,
                $registro,
                $registroEvaluado,
                $pulido,
                $morfosintaxis,
                $diagnosticos
            ),
        ];
    }

    private function construirComentarioEscucha($contenido, float $ratio, array $overlap, string $respuestaNorm): string
    {
        $lineas = [];
        if ($ratio >= 0.95) {
            $lineas[] = 'Transcripcion muy precisa. Captaste casi toda la frase con buen orden.';
        } elseif ($ratio >= 0.78) {
            $lineas[] = 'Buen intento. Captaste la mayor parte del audio, aunque conviene ajustar algunas palabras o el orden final.';
        } elseif ($ratio >= 0.55) {
            $lineas[] = 'Hay una base correcta, pero todavia faltan bloques importantes de la frase.';
        } else {
            $lineas[] = 'La transcripcion todavia esta lejos del audio objetivo. Conviene volver a escuchar con pausas y escribir por bloques.';
        }

        $lineas[] = 'Cobertura aproximada: ' . (int) round($overlap['recall'] * 100) . '% de las palabras objetivo.';

        $palabrasClave = array_values(array_filter(array_map('strval', (array) ($contenido->palabras_clave ?? [])), static fn($item) => trim($item) !== ''));
        $faltantesClave = [];
        foreach ($palabrasClave as $clave) {
            if (!$this->contienePatron($respuestaNorm, $clave)) {
                $faltantesClave[] = $clave;
            }
        }

        if (!empty($faltantesClave)) {
            $lineas[] = 'Revisa bloques como "' . implode('", "', array_slice($faltantesClave, 0, 3)) . '".';
        } elseif (!empty($overlap['missing'])) {
            $lineas[] = 'Revisa sobre todo estas palabras: "' . implode('", "', array_slice($overlap['missing'], 0, 4)) . '".';
        }

        if (!empty($overlap['extra']) && $ratio < 0.95) {
            $lineas[] = 'Tambien conviene recortar o corregir: "' . implode('", "', array_slice($overlap['extra'], 0, 3)) . '".';
        }

        return implode("\n", $lineas);
    }

    private function construirComentarioEscuchaMulti(float $ratioPromedio, array $detalles): string
    {
        $lineas = [];
        $ratioMinimo = empty($detalles) ? 0.0 : min(array_map(static fn($item): float => (float) ($item['ratio'] ?? 0), $detalles));

        if ($ratioPromedio >= 0.92 && $ratioMinimo >= 0.80) {
            $lineas[] = 'Escucha muy bien resuelta. Captaste los bloques con bastante precision.';
        } elseif ($ratioPromedio >= 0.75) {
            $lineas[] = 'Buen trabajo. La mayor parte de la escucha ya se sostiene, aunque algunos bloques todavia necesitan ajuste.';
        } elseif ($ratioPromedio >= 0.55) {
            $lineas[] = 'Hay una base util, pero conviene repetir algunos bloques y fijar mejor las palabras clave.';
        } else {
            $lineas[] = 'La escucha todavia esta verde. Conviene trabajar bloque por bloque antes de unir toda la idea.';
        }

        foreach (array_slice($detalles, 0, 4) as $detalle) {
            $label = (string) ($detalle['label'] ?? 'Bloque');
            $ratio = (float) ($detalle['ratio'] ?? 0);
            if (($detalle['response'] ?? '') === '') {
                $lineas[] = $label . ': no registraste respuesta.';
                continue;
            }
            if ($ratio >= 0.90) {
                $lineas[] = $label . ': bien resuelto.';
            } elseif (!empty($detalle['missing'])) {
                $lineas[] = $label . ': revisa "' . implode('", "', array_slice((array) $detalle['missing'], 0, 3)) . '".';
            } else {
                $lineas[] = $label . ': ajusta algunas palabras o el orden del bloque.';
            }
        }

        return implode("\n", $lineas);
    }

    private function construirComentarioEscritura(
        $contenido,
        string $textoNorm,
        float $ratio,
        int $palabras,
        int $oraciones,
        int $minPalabras,
        int $minOraciones,
        array $idioma,
        array $palabrasClave,
        int $coincidenciasClave,
        array $conectores,
        int $coincidenciasConectores,
        array $estructuraSugerida,
        array $movimientosEvaluados,
        string $registro,
        array $registroEvaluado,
        array $pulido,
        array $morfosintaxis,
        array $diagnosticos
    ): string {
        $lineas = [];
        if ($ratio >= 0.88) {
            $lineas[] = 'Texto solido y bien enfocado para esta consigna.';
        } elseif ($ratio >= 0.72) {
            $lineas[] = 'Buen texto. Ya cumple gran parte del objetivo, aunque todavia puede sonar mas natural y completo.';
        } elseif ($ratio >= 0.55) {
            $lineas[] = 'La idea principal aparece, pero conviene desarrollar mejor el texto y cerrar con mas control.';
        } else {
            $lineas[] = 'El texto todavia necesita mas desarrollo y un control mas claro del aleman objetivo.';
        }

        if ($minPalabras > 0) {
            $lineas[] = 'Longitud: ' . $palabras . '/' . $minPalabras . ' palabras.';
            if ($palabras < $minPalabras) {
                $lineas[] = 'Amplia un poco mas la respuesta para cubrir toda la tarea.';
            }
        }

        if ($oraciones < $minOraciones) {
            $lineas[] = 'Conviene organizar la idea en al menos ' . $minOraciones . ' oraciones claras.';
        }

        if ($idioma['score'] < 0.50) {
            $lineas[] = 'Procura mantener todo el texto en aleman; ahora mismo se cuelan demasiados apoyos en espanol o estructuras poco naturales.';
        }

        if (($registroEvaluado['score'] ?? 1) < 0.65) {
            $lineas[] = 'El registro todavia no suena lo bastante ' . $registro . ' para esta tarea.';
        }

        if (($pulido['punctuation_ratio'] ?? 1) < 0.75) {
            $lineas[] = 'Marca mejor los cierres de oracion con punto, interrogacion o exclamacion cuando cambies de idea.';
        }

        if (($pulido['uppercase_ratio'] ?? 1) < 0.80) {
            $lineas[] = 'Cuida mayusculas al inicio de cada oracion para que el texto se lea con mas solidez.';
        }

        if (!empty($pulido['formal_case_hits'])) {
            $lineas[] = 'En registro formal conviene escribir Sie, Ihnen o Ihr con mayuscula cuando te diriges a la otra persona.';
        }

        if (!empty($pulido['repeated_starts'])) {
            $lineas[] = 'Evita abrir demasiadas frases igual; ahora se repiten arranques como "' . implode('", "', array_slice((array) $pulido['repeated_starts'], 0, 2)) . '".';
        }

        $faltantesClave = [];
        foreach ($palabrasClave as $palabraClave) {
            if (!$this->contienePatron($textoNorm, $palabraClave)) {
                $faltantesClave[] = $palabraClave;
            }
        }

        if (!empty($palabrasClave) && $coincidenciasClave < min(3, count($palabrasClave))) {
            $lineas[] = 'Te ayudara incluir piezas como "' . implode('", "', array_slice($faltantesClave, 0, 3)) . '".';
        }

        if (!empty($conectores) && $coincidenciasConectores === 0) {
            $lineas[] = 'Para unir mejor las ideas, usa conectores como "' . implode('", "', array_slice($conectores, 0, 3)) . '".';
        }

        $movimientosFaltantes = [];
        foreach ((array) ($movimientosEvaluados['details'] ?? []) as $detalle) {
            if (empty($detalle['covered']) && !empty($detalle['label'])) {
                $movimientosFaltantes[] = (string) $detalle['label'];
            }
        }

        if (!empty($movimientosFaltantes)) {
            $lineas[] = 'Todavia conviene cubrir movimientos como: ' . implode('; ', array_slice($movimientosFaltantes, 0, 3)) . '.';
        }

        $morfosintaxisFaltante = [];
        foreach ((array) ($morfosintaxis['details'] ?? []) as $detalle) {
            if (empty($detalle['covered']) && !empty($detalle['label'])) {
                $morfosintaxisFaltante[] = (string) $detalle['label'];
            }
        }
        if (!empty($morfosintaxisFaltante)) {
            $lineas[] = 'Revisa tambien patrones como: ' . implode('; ', array_slice($morfosintaxisFaltante, 0, 2)) . '.';
        }

        $diagnosticosDetectados = [];
        foreach ((array) ($diagnosticos['triggered'] ?? []) as $detalle) {
            if (!empty($detalle['label'])) {
                $diagnosticosDetectados[] = rtrim((string) $detalle['label'], '. ');
            }
        }
        if (!empty($diagnosticosDetectados)) {
            $lineas[] = 'Cuidado con estos puntos gramaticales: ' . implode('; ', array_slice($diagnosticosDetectados, 0, 2)) . '.';
        }

        if (!empty($registroEvaluado['missing_targets']) && (($registroEvaluado['score'] ?? 1) < 0.78)) {
            $lineas[] = 'Para este registro ayudan giros como "' . implode('", "', array_slice((array) $registroEvaluado['missing_targets'], 0, 3)) . '".';
        }

        if (!empty($registroEvaluado['forbidden_hits'])) {
            $lineas[] = 'Evita giros que rompen el tono esperado, por ejemplo: "' . implode('", "', array_slice((array) $registroEvaluado['forbidden_hits'], 0, 3)) . '".';
        }

        if (!empty($estructuraSugerida) && (!empty($movimientosFaltantes) || $ratio < 0.82)) {
            $lineas[] = 'Recorrido sugerido: ' . implode(' -> ', array_slice($estructuraSugerida, 0, 4)) . '.';
        }

        if (!empty($contenido->registro ?? '')) {
            $lineas[] = 'Registro esperado: ' . ucfirst((string) $contenido->registro) . '.';
        }

        return implode("\n", $lineas);
    }

    private function normalizarTextoEvaluacion(string $texto): string
    {
        $texto = trim(strip_tags($texto));
        if ($texto !== '' && !mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
        }
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'ñ' => 'n',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'ñ' => 'n',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'ñ' => 'n',
        ]);
        $texto = preg_replace('/[^a-z0-9\\s]/u', ' ', $texto);
        $texto = preg_replace('/\\s+/u', ' ', $texto);
        return trim((string) $texto);
    }

    private function tokenizarTextoEvaluacion(string $texto): array
    {
        $normalizado = $this->normalizarTextoEvaluacion($texto);
        if ($normalizado === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $normalizado), static fn($token) => $token !== ''));
    }

    private function frecuenciaTokens(array $tokens): array
    {
        $frecuencias = [];
        foreach ($tokens as $token) {
            $frecuencias[$token] = ($frecuencias[$token] ?? 0) + 1;
        }

        return $frecuencias;
    }

    private function medirSolapamientoTokens(array $esperado, array $recibido): array
    {
        $esperadoFreq = $this->frecuenciaTokens($esperado);
        $recibidoFreq = $this->frecuenciaTokens($recibido);
        $matched = 0;
        $missing = [];
        $extra = [];

        foreach ($esperadoFreq as $token => $count) {
            $presentes = min($count, $recibidoFreq[$token] ?? 0);
            $matched += $presentes;
            if ($presentes < $count) {
                $missing[] = $token;
            }
        }

        foreach ($recibidoFreq as $token => $count) {
            $presentes = min($count, $esperadoFreq[$token] ?? 0);
            if ($presentes < $count) {
                $extra[] = $token;
            }
        }

        return [
            'matched' => $matched,
            'recall' => $matched / max(1, count($esperado)),
            'precision' => $matched / max(1, count($recibido)),
            'missing' => array_values(array_unique($missing)),
            'extra' => array_values(array_unique($extra)),
        ];
    }

    private function scoreParecidoTexto(string $esperado, string $recibido): float
    {
        if ($esperado === '' || $recibido === '') {
            return 0.0;
        }

        if ($esperado === $recibido) {
            return 1.0;
        }

        $percent = 0.0;
        similar_text($esperado, $recibido, $percent);
        $maxLen = max(strlen($esperado), strlen($recibido));
        $levenshteinScore = $maxLen > 0 ? 1 - (min($maxLen, levenshtein($esperado, $recibido)) / $maxLen) : 0.0;

        return max(0.0, min(1.0, (($percent / 100) * 0.60) + ($levenshteinScore * 0.40)));
    }

    private function contarPalabrasEvaluacion(string $texto): int
    {
        return count($this->tokenizarTextoEvaluacion($texto));
    }

    private function contarOracionesEvaluacion(string $texto): int
    {
        $partes = preg_split('/[.!?\\n]+/u', trim($texto));
        if (!is_array($partes)) {
            return 0;
        }

        return count(array_filter(array_map('trim', $partes), static fn($parte) => $parte !== ''));
    }

    private function medirIdiomaObjetivo(string $textoNormalizado, string $idiomaObjetivo = 'aleman'): array
    {
        $tokens = array_values(array_filter(explode(' ', $textoNormalizado), static fn($token) => $token !== ''));
        $markersAleman = [
            'ich', 'du', 'wir', 'ihr', 'nicht', 'kein', 'eine', 'einen', 'der', 'die', 'das',
            'bin', 'bist', 'ist', 'sind', 'habe', 'hast', 'hat', 'haben', 'weil', 'dass',
            'mit', 'fuer', 'und', 'aber', 'heute', 'gestern', 'morgen', 'dann', 'moechte',
            'werde', 'koennen', 'kann', 'schreibe', 'frage', 'bitte', 'leider',
        ];
        $markersEspanol = [
            'quiero', 'puedo', 'tengo', 'hago', 'porque', 'pero', 'aunque', 'despues',
            'ayer', 'manana', 'para', 'sobre', 'escribo', 'pregunta', 'solucion',
            'cita', 'mensaje', 'texto', 'curso', 'clase', 'profesor', 'amigo', 'semana',
        ];

        $hitsAleman = 0;
        $hitsEspanol = 0;
        foreach ($tokens as $token) {
            if (in_array($token, $markersAleman, true)) {
                $hitsAleman++;
            }
            if (in_array($token, $markersEspanol, true)) {
                $hitsEspanol++;
            }
        }

        $score = 0.60;
        if ($idiomaObjetivo === 'aleman') {
            if ($hitsAleman === 0 && $hitsEspanol > 0) {
                $score = 0.20;
            } elseif ($hitsAleman >= ($hitsEspanol + 3)) {
                $score = 1.0;
            } elseif ($hitsAleman > $hitsEspanol) {
                $score = 0.85;
            } elseif ($hitsAleman > 0 && $hitsAleman === $hitsEspanol) {
                $score = 0.65;
            } elseif ($hitsAleman > 0) {
                $score = 0.50;
            }
        }

        return [
            'score' => $score,
            'hits_target' => $hitsAleman,
            'hits_other' => $hitsEspanol,
        ];
    }

    private function matchPatterns(string $textoNormalizado, array $patterns): int
    {
        $matches = 0;
        foreach ($patterns as $pattern) {
            if ($this->contienePatron($textoNormalizado, $pattern)) {
                $matches++;
            }
        }

        return $matches;
    }

    private function contienePatron(string $textoNormalizado, string $pattern): bool
    {
        $patternNorm = $this->normalizarTextoEvaluacion($pattern);
        if ($patternNorm === '' || $textoNormalizado === '') {
            return false;
        }

        return strpos(' ' . $textoNormalizado . ' ', ' ' . $patternNorm . ' ') !== false;
    }

    private function normalizarMovimientosEscritura($movimientos): array
    {
        if (is_object($movimientos)) {
            $movimientos = (array) $movimientos;
        }

        if (!is_array($movimientos)) {
            return [];
        }

        $normalizados = [];
        foreach ($movimientos as $movimiento) {
            if (is_object($movimiento)) {
                $movimiento = (array) $movimiento;
            }
            if (!is_array($movimiento)) {
                continue;
            }

            $label = trim((string) ($movimiento['label'] ?? ''));
            $patterns = array_values(array_filter(array_map('strval', (array) ($movimiento['patterns'] ?? [])), static fn($item) => trim($item) !== ''));
            if ($label === '' || empty($patterns)) {
                continue;
            }

            $requiredHits = (int) ($movimiento['required_hits'] ?? 1);
            $requiredHits = max(1, min(count($patterns), $requiredHits));

            $normalizados[] = [
                'label' => $label,
                'patterns' => $patterns,
                'required_hits' => $requiredHits,
            ];
        }

        return $normalizados;
    }

    private function evaluarMovimientosEscritura(string $textoNormalizado, array $movimientos): array
    {
        $details = [];
        $covered = 0;

        foreach ($movimientos as $movimiento) {
            $matched = [];
            $missing = [];
            foreach ((array) ($movimiento['patterns'] ?? []) as $pattern) {
                if ($this->contienePatron($textoNormalizado, (string) $pattern)) {
                    $matched[] = (string) $pattern;
                } else {
                    $missing[] = (string) $pattern;
                }
            }

            $requiredHits = (int) ($movimiento['required_hits'] ?? 1);
            $isCovered = count($matched) >= $requiredHits;
            if ($isCovered) {
                $covered++;
            }

            $details[] = [
                'label' => (string) ($movimiento['label'] ?? ''),
                'covered' => $isCovered,
                'matched' => $matched,
                'missing' => $missing,
                'required_hits' => $requiredHits,
            ];
        }

        return [
            'covered' => $covered,
            'details' => $details,
        ];
    }

    private function evaluarRegistroEscritura(string $textoNormalizado, string $registro, array $targetPatterns, array $forbiddenPatterns): array
    {
        $targetPatterns = array_values(array_filter(array_map('strval', $targetPatterns), static fn($item) => trim($item) !== ''));
        $forbiddenPatterns = array_values(array_filter(array_map('strval', $forbiddenPatterns), static fn($item) => trim($item) !== ''));

        $targetHits = [];
        foreach ($targetPatterns as $pattern) {
            if ($this->contienePatron($textoNormalizado, $pattern)) {
                $targetHits[] = $pattern;
            }
        }

        $forbiddenHits = [];
        foreach ($forbiddenPatterns as $pattern) {
            if ($this->contienePatron($textoNormalizado, $pattern)) {
                $forbiddenHits[] = $pattern;
            }
        }

        $targetGoal = empty($targetPatterns) ? 1 : max(1, min(2, count($targetPatterns)));
        $score = empty($targetPatterns) ? 0.85 : min(1.0, count($targetHits) / $targetGoal);

        if (in_array($registro, ['formal', 'academico', 'analitico', 'argumentativo'], true) && count($targetHits) === 0) {
            $score = min($score, 0.35);
        }

        if (!empty($forbiddenHits)) {
            $score -= min(0.60, count($forbiddenHits) * 0.25);
        }

        return [
            'score' => max(0.0, min(1.0, $score)),
            'target_hits' => array_values(array_unique($targetHits)),
            'missing_targets' => array_values(array_diff($targetPatterns, $targetHits)),
            'forbidden_hits' => array_values(array_unique($forbiddenHits)),
        ];
    }

    private function normalizarPatronesMorfosintacticos($patterns): array
    {
        if (is_object($patterns)) {
            $patterns = (array) $patterns;
        }

        if (!is_array($patterns)) {
            return [];
        }

        $normalizados = [];
        foreach ($patterns as $pattern) {
            if (is_object($pattern)) {
                $pattern = (array) $pattern;
            }
            if (!is_array($pattern)) {
                continue;
            }

            $label = trim((string) ($pattern['label'] ?? ''));
            $rule = trim((string) ($pattern['rule'] ?? ''));
            if ($label === '' && $rule === '') {
                continue;
            }

            $normalizados[] = [
                'label' => $label !== '' ? $label : $rule,
                'rule' => $rule,
            ];
        }

        return $normalizados;
    }

    private function normalizarDiagnosticosGramaticales($diagnostics): array
    {
        if (is_object($diagnostics)) {
            $diagnostics = (array) $diagnostics;
        }

        if (!is_array($diagnostics)) {
            return [];
        }

        $normalizados = [];
        foreach ($diagnostics as $diagnostico) {
            if (is_object($diagnostico)) {
                $diagnostico = (array) $diagnostico;
            }
            if (!is_array($diagnostico)) {
                continue;
            }

            $label = trim((string) ($diagnostico['label'] ?? ''));
            $rule = trim((string) ($diagnostico['rule'] ?? ''));
            if ($label === '' || $rule === '') {
                continue;
            }

            $normalizados[] = [
                'label' => $label,
                'rule' => $rule,
            ];
        }

        return $normalizados;
    }

    private function evaluarPatronesMorfosintacticos(string $textoNormalizado, array $patterns, string $textoOriginal = ''): array
    {
        $details = [];
        $covered = 0;

        foreach ($patterns as $pattern) {
            $label = (string) ($pattern['label'] ?? 'patron');
            $rule = (string) ($pattern['rule'] ?? '');
            $match = $this->cumplePatronMorfosintactico($textoNormalizado, $rule, $textoOriginal);

            $details[] = [
                'label' => $label,
                'covered' => $match,
                'rule' => $rule,
            ];

            if ($match) {
                $covered++;
            }
        }

        return [
            'covered' => $covered,
            'details' => $details,
        ];
    }

    private function evaluarDiagnosticosGramaticales(string $textoOriginal, string $textoNormalizado, array $diagnostics): array
    {
        if (empty($diagnostics)) {
            return [
                'score' => 1.0,
                'triggered' => [],
            ];
        }

        $triggered = [];
        foreach ($diagnostics as $diagnostico) {
            $label = (string) ($diagnostico['label'] ?? '');
            $rule = (string) ($diagnostico['rule'] ?? '');
            if ($label === '' || $rule === '') {
                continue;
            }

            if ($this->detectarIncidenciaGramatical($textoOriginal, $textoNormalizado, $rule)) {
                $triggered[] = [
                    'label' => $label,
                    'rule' => $rule,
                ];
            }
        }

        $score = max(0.35, 1 - (count($triggered) * 0.22));

        return [
            'score' => $score,
            'triggered' => $triggered,
        ];
    }

    private function cumplePatronMorfosintactico(string $textoNormalizado, string $rule, string $textoOriginal = ''): bool
    {
        if ($textoNormalizado === '' || $rule === '') {
            return false;
        }

        $textoOriginalLower = trim($textoOriginal !== '' ? $textoOriginal : $textoNormalizado);
        if ($textoOriginalLower !== '' && !mb_check_encoding($textoOriginalLower, 'UTF-8')) {
            $textoOriginalLower = mb_convert_encoding($textoOriginalLower, 'UTF-8', 'Windows-1252');
        }
        $textoOriginalLower = mb_strtolower($textoOriginalLower, 'UTF-8');

        $countHits = function (array $patterns) use ($textoNormalizado): int {
            $hits = 0;
            foreach ($patterns as $pattern) {
                if ($this->contienePatron($textoNormalizado, (string) $pattern)) {
                    $hits++;
                }
            }
            return $hits;
        };

        switch ($rule) {
            case 'temporal_sequence_day':
                return $countHits(['am morgen', 'am nachmittag', 'am abend', 'zuerst', 'dann', 'danach', 'zum schluss']) >= 2;
            case 'temporal_sequence_basic':
                return $countHits(['jeden tag', 'normalerweise', 'oft', 'zuerst', 'danach', 'spaeter']) >= 1;
            case 'present_identity_frame':
                return $countHits(['ich heisse', 'ich komme aus', 'ich bin']) >= 2;
            case 'modal_need_frame':
                return $countHits(['ich moechte', 'ich brauche', 'ich will']) >= 1;
            case 'perfekt':
                return preg_match('/\b(habe|hast|hat|haben|bin|bist|ist|sind|seid)\b(?:\s+\w+){0,5}\s+(?:ge\w+(?:t|en)|\w+iert|gewesen)\b/u', $textoNormalizado) === 1;
            case 'temporal_sequence_past':
                return $countHits(['gestern', 'am wochenende', 'dann', 'danach', 'spaeter']) >= 1;
            case 'formal_request':
                return $countHits(['koennten sie', 'wuerden sie', 'ich moechte', 'waere ich ihnen dankbar', 'koennen wir']) >= 1;
            case 'day_time_reference':
                return $countHits(['am montag', 'am dienstag', 'am mittwoch', 'am donnerstag', 'am freitag', 'am samstag', 'am sonntag', 'um', 'heute abend']) >= 1;
            case 'problem_statement':
                return $countHits(['leider', 'problem', 'funktioniert nicht', 'stimmt nicht', 'rechnung']) >= 1;
            case 'invitation_frame':
                return $countHits(['hast du zeit', 'moechtest du', 'wir koennen', 'ich moechte']) >= 1;
            case 'future_werden':
                return preg_match('/\b(werde|wirst|wird|werden)\b(?:\s+\w+){0,4}\s+\w+en\b/u', $textoNormalizado) === 1;
            case 'subordinate_clause':
                return preg_match('/\b(weil|dass)\b(?:\s+\w+){2,8}\s+\w+(?:e|st|t|en)\b/u', $textoNormalizado) === 1;
            case 'subordinate_clause_strict':
                return preg_match('/\b(weil|dass|wenn|obwohl|damit)\b(?:\s+\S+){1,9}\s+\S+(?:e|st|t|en)\b(?=[,.!?]|$)/u', $textoOriginalLower) === 1;
            case 'contrast_frame':
                return $countHits(['einerseits', 'andererseits', 'dennoch', 'allerdings', 'zwar', 'aber']) >= 1;
            case 'formal_closing':
                return $countHits(['mit freundlichen gruessen']) >= 1;
            case 'formal_salutation':
                return preg_match('/\b(sehr geehrte[nmrs]?|guten tag)\b/u', $textoOriginalLower) === 1;
            case 'polite_konjunktiv':
                return preg_match('/\b(k[oö]nnten sie|koennten sie|w[uü]rden sie|wuerden sie|w[aä]re es moeglich|waere es moeglich|ich waere ihnen dankbar)\b/u', $textoOriginalLower) === 1;
            case 'proposal_frame':
                return $countHits(['ich schlage vor', 'koennen wir', 'wir sollten', 'deshalb']) >= 1;
            case 'source_reference':
                return $countHits(['der text', 'der beitrag', 'der artikel', 'der bericht']) >= 1;
            case 'summary_closing':
                return $countHits(['zusammenfassend', 'insgesamt', 'zum schluss']) >= 1;
            case 'argument_opinion_frame':
                return $countHits(['ich finde', 'meiner meinung nach', 'meiner ansicht nach']) >= 1;
            case 'analytic_frame':
                return $countHits(['die daten zeigen', 'auffaellig ist', 'insgesamt']) >= 1;
            case 'relative_clause':
                if (preg_match_all('/,\s*(der|die|das|den|dem|deren|dessen)\b([^,.!?;:]*)/u', $textoOriginalLower, $matches, PREG_SET_ORDER) !== false) {
                    foreach ($matches as $match) {
                        $clausula = $this->normalizarTextoEvaluacion((string) ($match[2] ?? ''));
                        $tokens = array_values(array_filter(explode(' ', $clausula), static fn($token) => $token !== ''));
                        if (count($tokens) < 3) {
                            continue;
                        }

                        $ultimoToken = $tokens[count($tokens) - 1] ?? '';
                        $penultimoToken = $tokens[count($tokens) - 2] ?? '';
                        if ($this->esVerboFinitoAleman($ultimoToken) || $this->esVerboFinitoAleman($penultimoToken)) {
                            return true;
                        }
                    }
                }
                return false;
            case 'advantage_disadvantage':
                return $countHits(['ein vorteil', 'ein nachteil', 'kritisch ist', 'positiv ist']) >= 2;
            case 'argument_balance_frame':
                return ($countHits(['einerseits']) >= 1 && $countHits(['andererseits']) >= 1)
                    || ($countHits(['nicht nur']) >= 1 && $countHits(['sondern auch']) >= 1)
                    || ($countHits(['zwar']) >= 1 && $countHits(['aber']) >= 1);
            case 'consequence_frame':
                return $countHits(['folglich', 'deshalb', 'daher']) >= 1;
            case 'frequency_plan_frame':
                return $countHits(['jede woche', 'zweimal pro woche', 'einmal im monat', 'mein ziel', 'mein plan']) >= 1;
            case 'source_voice_split':
                return $countHits(['der text vertritt die these', 'der autor argumentiert']) >= 1
                    && $countHits(['aus meiner sicht', 'kritisch sehe ich', 'ich wuerde']) >= 1;
            case 'nominalization_frame':
                return preg_match_all('/\b\p{L}+(ung|keit|heit|tion|tät|taet|ismus|schaft)\b/u', $textoOriginalLower, $matches) >= 2;
            case 'passive_voice':
                return preg_match('/\b(wird|werden|wurde|wurden|ist|sind)\b(?:\s+\S+){0,4}\s+(ge\w+(?:t|en)|\w+iert)\b/u', $textoOriginalLower) === 1;
            case 'skill_list_frame':
                return $countHits(['hoeren', 'lesen', 'schreiben', 'sprechen']) >= 2;
            case 'reflection_frame':
                return $countHits(['ich kann bereits', 'ich arbeite noch an', 'ich moechte weiter', 'mein plan']) >= 2;
            case 'everyday_action_frame':
                return $countHits(['ich gehe', 'ich fahre', 'ich kaufe', 'ich lerne', 'ich arbeite', 'ich esse', 'ich trinke']) >= 1;
            default:
                return false;
        }
    }

    private function detectarIncidenciaGramatical(string $textoOriginal, string $textoNormalizado, string $rule): bool
    {
        if ($textoNormalizado === '' || $rule === '') {
            return false;
        }

        $textoOriginalLower = trim(strip_tags($textoOriginal !== '' ? $textoOriginal : $textoNormalizado));
        if ($textoOriginalLower !== '' && !mb_check_encoding($textoOriginalLower, 'UTF-8')) {
            $textoOriginalLower = mb_convert_encoding($textoOriginalLower, 'UTF-8', 'Windows-1252');
        }
        $textoOriginalLower = mb_strtolower($textoOriginalLower, 'UTF-8');

        switch ($rule) {
            case 'verb_second_fronted_time':
                $segmentos = preg_split('/[.!?\n]+/u', $textoNormalizado);
                if (!is_array($segmentos)) {
                    return false;
                }

                $iniciosSimples = ['heute', 'gestern', 'morgen', 'dann', 'danach', 'spaeter', 'zuerst', 'jetzt', 'normalerweise', 'oft', 'manchmal'];
                $iniciosCompuestos = ['montag', 'dienstag', 'mittwoch', 'donnerstag', 'freitag', 'samstag', 'sonntag', 'morgen', 'abend', 'wochenende'];
                $sujetos = ['ich', 'du', 'er', 'sie', 'es', 'wir', 'ihr', 'man'];

                foreach ($segmentos as $segmento) {
                    $tokens = array_values(array_filter(explode(' ', trim((string) $segmento)), static fn($token) => $token !== ''));
                    if (count($tokens) < 3) {
                        continue;
                    }

                    $offset = 0;
                    if (in_array($tokens[0], $iniciosSimples, true)) {
                        $offset = 1;
                    } elseif (($tokens[0] ?? '') === 'am' && in_array($tokens[1] ?? '', $iniciosCompuestos, true)) {
                        $offset = 2;
                    }

                    if ($offset === 0) {
                        continue;
                    }

                    $sujeto = $tokens[$offset] ?? '';
                    if (in_array($sujeto, $sujetos, true)) {
                        return true;
                    }
                }

                return false;

            case 'subordinate_verb_final':
                if (preg_match('/\b(weil|dass|wenn|obwohl|damit)\s+(ich|du|er|sie|es|wir|ihr|man)\s+(bin|bist|ist|sind|seid|habe|hast|hat|haben|werde|wirst|wird|werden|kann|kannst|koennen|will|willst|wollen|muss|musst|muessen|soll|sollst|sollen)\b/u', $textoNormalizado) === 1) {
                    return true;
                }
                if (preg_match_all('/\b(weil|dass|wenn|obwohl|damit)\b([^,.!?;:]*)/u', $textoOriginalLower, $matches, PREG_SET_ORDER) !== false) {
                    foreach ($matches as $match) {
                        $clausula = $this->normalizarTextoEvaluacion((string) ($match[2] ?? ''));
                        $tokens = array_values(array_filter(explode(' ', $clausula), static fn($token) => $token !== ''));
                        if (count($tokens) < 4) {
                            continue;
                        }

                        $ultPosicionVerbo = null;
                        foreach ($tokens as $index => $token) {
                            if ($this->esVerboFinitoAleman($token)) {
                                $ultPosicionVerbo = $index;
                            }
                        }

                        if ($ultPosicionVerbo !== null && $ultPosicionVerbo < (count($tokens) - 1)) {
                            return true;
                        }
                    }
                }

                return false;

            case 'relative_clause_commas':
                return preg_match('/,\s*(der|die|das|den|dem|deren|dessen)\b/u', $textoOriginalLower) !== 1;

            case 'accusative_preposition_case':
                return preg_match('/\b(fuer|ohne|gegen|um)\s+(dem|der|einem|einer|diesem|dieser|meinem|meiner|deinem|deiner|ihrem|ihrer)\b/u', $textoNormalizado) === 1;

            case 'dative_preposition_case':
                return preg_match('/\b(mit|nach|bei|von|zu|aus)\s+(einen|meinen|deinen|seinen|ihren|diesen|jenen)\b/u', $textoNormalizado) === 1;

            default:
                return false;
        }
    }

    private function esVerboFinitoAleman(string $token): bool
    {
        static $verbos = [
            'bin', 'bist', 'ist', 'sind', 'seid', 'war', 'warst', 'waren', 'wart',
            'habe', 'hast', 'hat', 'haben', 'habt',
            'werde', 'wirst', 'wird', 'werden', 'werdet', 'wurde', 'wurden',
            'kann', 'kannst', 'koennen', 'koennt', 'koennte', 'koennten',
            'will', 'willst', 'wollen', 'wollt', 'wollte', 'wollten',
            'muss', 'musst', 'muessen', 'muesst', 'musste', 'mussten',
            'darf', 'darfst', 'duerfen', 'duerft', 'durfte', 'durften',
            'soll', 'sollst', 'sollen', 'sollt', 'sollte', 'sollten',
            'gehe', 'gehst', 'geht', 'gehen', 'fahre', 'fahrst', 'fahrt', 'fahren',
            'komme', 'kommst', 'kommt', 'kommen', 'bleibe', 'bleibst', 'bleibt', 'bleiben',
            'schreibe', 'schreibst', 'schreibt', 'schreiben',
        ];

        return in_array($token, $verbos, true);
    }

    private function evaluarPulidoEscritura(string $texto, string $registro): array
    {
        $texto = trim($texto);
        if ($texto === '') {
            return [
                'score' => 0.0,
                'punctuation_ratio' => 0.0,
                'uppercase_ratio' => 0.0,
                'formal_case_hits' => [],
                'repeated_starts' => [],
            ];
        }

        $segmentos = preg_split('/(?<=[.!?])\s+|\n+/u', $texto);
        if (!is_array($segmentos)) {
            $segmentos = [$texto];
        }
        $segmentos = array_values(array_filter(array_map('trim', $segmentos), static fn($item) => $item !== ''));

        $sentenceCount = max(1, count($segmentos));
        $punctuationMarks = preg_match_all('/[.!?](?=\s|$)/u', $texto, $matches);
        $punctuationRatio = min(1.0, ((int) $punctuationMarks) / $sentenceCount);

        $uppercaseStarts = 0;
        $firstWords = [];
        foreach ($segmentos as $segmento) {
            $limpio = ltrim($segmento, " \t\n\r\0\x0B\"'([{¿¡");
            $firstChar = mb_substr($limpio, 0, 1, 'UTF-8');
            if ($firstChar !== '' && preg_match('/^[A-ZÁÉÍÓÚÄÖÜÑ]/u', $firstChar) === 1) {
                $uppercaseStarts++;
            }

            $tokens = preg_split('/\s+/u', $this->normalizarTextoEvaluacion($segmento));
            if (is_array($tokens) && !empty($tokens[0])) {
                $firstWords[] = $tokens[0];
            }
        }
        $uppercaseRatio = $sentenceCount > 0 ? ($uppercaseStarts / $sentenceCount) : 1.0;

        $repeatedStarts = [];
        if (!empty($firstWords)) {
            $freq = array_count_values($firstWords);
            foreach ($freq as $word => $count) {
                if ($count >= 3 && count($firstWords) >= 4) {
                    $repeatedStarts[] = $word;
                }
            }
        }

        $formalCaseHits = [];
        if ($registro === 'formal') {
            if (preg_match_all('/\b(sie|ihnen|ihr)\b/u', $texto, $formalMatches) > 0 && !empty($formalMatches[1])) {
                $formalCaseHits = array_values(array_unique(array_map('strval', $formalMatches[1])));
            }
        }

        $score = ($punctuationRatio * 0.45) + ($uppercaseRatio * 0.35) + (empty($repeatedStarts) ? 0.20 : 0.10);
        if (!empty($formalCaseHits)) {
            $score -= 0.18;
        }

        return [
            'score' => max(0.0, min(1.0, $score)),
            'punctuation_ratio' => $punctuationRatio,
            'uppercase_ratio' => $uppercaseRatio,
            'formal_case_hits' => $formalCaseHits,
            'repeated_starts' => $repeatedStarts,
        ];
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

    private function evaluarPronunciacion($actividad, $respuesta_texto) {
        $respuestasEstudiante = json_decode($respuesta_texto, true);
        if (!is_array($respuestasEstudiante)) {
            $respuestasEstudiante = $respuesta_texto;
        }
        $detalle = $this->calcularPronunciacionDetalle($actividad, $respuestasEstudiante);

        return [
            'puntuacion' => $detalle['puntuacion'],
            'comentarios' => $this->construirComentarioPronunciacion($detalle),
        ];
    }

    private function calcularPronunciacion($actividad, $respuestasEstudiante) {
        $detalle = $this->calcularPronunciacionDetalle($actividad, $respuestasEstudiante);
        return $detalle['puntuacion'];
    }

    private function calcularPronunciacionDetalle($actividad, $respuestasEstudiante): array
    {
        $contenido = json_decode($actividad->contenido ?? '[]');
        if (!is_array($contenido)) {
            $contenido = [json_decode($actividad->contenido ?? '{}')];
        }

        $preguntasCount = count($contenido);
        if ($preguntasCount === 0) {
            return [
                'puntuacion' => 0.0,
                'ratio_promedio' => 0.0,
                'frases' => [],
            ];
        }

        $puntosMaximos = (float) ($actividad->puntos_maximos ?? 10);
        $puntosPorPregunta = $puntosMaximos / max(1, $preguntasCount);
        $puntosTotales = 0.0;
        $frases = [];

        foreach ($contenido as $idx => $item) {
            $id = $item->id ?? "q$idx";
            $fraseTarget = trim((string) ($item->frase ?? ''));
            $respuestaUsuario = is_array($respuestasEstudiante) ? ($respuestasEstudiante[$id] ?? '') : $respuestasEstudiante;
            $respuestaUsuario = trim((string) $respuestaUsuario);
            $targetNorm = $this->normalizarTextoEvaluacion($fraseTarget);
            $responseNorm = $this->normalizarTextoEvaluacion($respuestaUsuario);
            $targetTokens = $this->tokenizarTextoEvaluacion($fraseTarget);
            $responseTokens = $this->tokenizarTextoEvaluacion($respuestaUsuario);

            if ($responseNorm === '' || $targetNorm === '') {
                $ratio = 0.0;
                $overlap = ['recall' => 0.0, 'precision' => 0.0, 'missing' => $targetTokens, 'extra' => []];
            } else {
                $overlap = $this->medirSolapamientoTokens($targetTokens, $responseTokens);
                $stringScore = $this->scoreParecidoTexto($targetNorm, $responseNorm);
                $completitud = min(1, count($responseTokens) / max(1, count($targetTokens)));
                $ratio = $targetNorm === $responseNorm
                    ? 1.0
                    : (($overlap['recall'] * 0.45) + ($overlap['precision'] * 0.15) + ($stringScore * 0.30) + ($completitud * 0.10));

                if ($overlap['recall'] < 0.30 && $stringScore < 0.25) {
                    $ratio = min($ratio, 0.25);
                }
            }

            $ratio = max(0.0, min(1.0, $ratio));
            $puntosFrase = round($puntosPorPregunta * $ratio, 2);
            $puntosTotales += $puntosFrase;

            $keywords = array_values(array_filter(array_map('strval', (array) ($item->palabras_clave ?? [])), static fn($value) => trim($value) !== ''));
            $focuses = array_values(array_filter(array_map('strval', (array) ($item->focos ?? [])), static fn($value) => trim($value) !== ''));
            $missingKeywords = [];
            foreach ($keywords as $keyword) {
                if (!$this->contienePatron($responseNorm, $keyword)) {
                    $missingKeywords[] = $keyword;
                }
            }

            $frases[] = [
                'id' => $id,
                'target' => $fraseTarget,
                'response' => $respuestaUsuario,
                'ratio' => $ratio,
                'score' => $puntosFrase,
                'missing' => !empty($missingKeywords) ? $missingKeywords : array_slice((array) ($overlap['missing'] ?? []), 0, 4),
                'focuses' => $focuses,
                'hint' => trim((string) ($item->pista ?? '')),
            ];
        }

        $ratioPromedio = $preguntasCount > 0 ? array_sum(array_column($frases, 'ratio')) / $preguntasCount : 0.0;

        return [
            'puntuacion' => round($puntosTotales, 2),
            'ratio_promedio' => $ratioPromedio,
            'frases' => $frases,
        ];
    }

    private function construirComentarioPronunciacion(array $detalle): string
    {
        $lineas = [];
        $ratio = (float) ($detalle['ratio_promedio'] ?? 0);
        $ratiosFrase = array_map(static fn($frase): float => (float) ($frase['ratio'] ?? 0), (array) ($detalle['frases'] ?? []));
        $ratioMinimo = empty($ratiosFrase) ? 0.0 : min($ratiosFrase);

        if ($ratio >= 0.90 && $ratioMinimo >= 0.82) {
            $lineas[] = 'Pronunciacion muy solida para este bloque. El reconocimiento capto casi todas las frases con claridad.';
        } elseif ($ratio >= 0.72) {
            $lineas[] = 'Buen trabajo. La base oral ya se entiende, aunque todavia conviene limpiar algunas partes de la articulacion.';
        } elseif ($ratio >= 0.50) {
            $lineas[] = 'La produccion oral va en camino, pero aun se pierden palabras o finales importantes.';
        } else {
            $lineas[] = 'Todavia cuesta que el sistema reconozca bien varias frases. Conviene repetir mas lento y por bloques cortos.';
        }

        foreach (array_slice((array) ($detalle['frases'] ?? []), 0, 3) as $index => $frase) {
            $prefix = 'Frase ' . ($index + 1) . ': ';
            if (empty($frase['response'])) {
                $lineas[] = $prefix . 'no se registro una respuesta oral.';
                continue;
            }

            if (($frase['ratio'] ?? 0) >= 0.90) {
                $lineas[] = $prefix . 'bien resuelta.';
                continue;
            }

            $segmentos = [];
            if (!empty($frase['missing'])) {
                $segmentos[] = 'revisa "' . implode('", "', array_slice((array) $frase['missing'], 0, 3)) . '"';
            }
            if (!empty($frase['focuses']) && empty($frase['hint'])) {
                $segmentos[] = 'cuida ' . implode(', ', array_slice((array) $frase['focuses'], 0, 2));
            }
            if (!empty($frase['hint'])) {
                $segmentos[] = rtrim((string) $frase['hint'], '.');
            }

            $lineas[] = $prefix . ucfirst(implode('. ', array_filter($segmentos)));
        }

        return implode("\n", $lineas);
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
