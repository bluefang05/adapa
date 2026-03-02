<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Inscripcion.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Teoria.php';
require_once __DIR__ . '/../../models/Actividad.php';
require_once __DIR__ . '/../../models/Respuesta.php';
require_once __DIR__ . '/../../models/OpcionMultiple.php';

class EstudianteController extends Controller {
    private $cursoModel;
    private $inscripcionModel;
    private $leccionModel;

    public function __construct() {
        $this->requireRole('estudiante');
        $this->cursoModel = new Curso();
        $this->inscripcionModel = new Inscripcion();
        $this->leccionModel = new Leccion();
    }

    public function index() {
        $estudiante_id = Auth::getUserId();
        $cursosInscritos = $this->cursoModel->obtenerResumenCursosPorEstudiante($estudiante_id);
        $cursosPublicos = $this->cursoModel->obtenerCursosPublicos();

        // Filtrar cursos públicos para no mostrar los ya inscritos
        $cursosInscritosIds = array_map(function($curso) {
            return $curso->id;
        }, $cursosInscritos);

        $cursosDisponibles = array_filter($cursosPublicos, function($curso) use ($cursosInscritosIds) {
            return !in_array($curso->id, $cursosInscritosIds);
        });

        require_once __DIR__ . '/../../views/estudiante/index.php';
    }

    public function inscribir($curso_id) {
        $this->requirePost();
        require_csrf();
        $estudiante_id = Auth::getUserId();

        if ($this->inscripcionModel->inscribirEstudiante($curso_id, $estudiante_id)) {
            $this->flash('mensaje', 'Inscripcion completada.');
            $this->redirect('/estudiante');
        } else {
            $this->flash('error', 'Error al inscribirse al curso.');
            $this->redirect('/estudiante');
        }
    }

    public function continuarCurso($curso_id) {
        $estudiante_id = Auth::getUserId();
        
        // Verificar si el estudiante está inscrito en el curso
        if (!$this->inscripcionModel->verificarInscripcion($curso_id, $estudiante_id)) {
            $this->redirect('/estudiante');
        }

        $lecciones = $this->leccionModel->obtenerLeccionesPorCurso($curso_id);
        
        foreach ($lecciones as $leccion) {
            $siguienteItem = $this->_determinarSiguienteItem($leccion->id, $estudiante_id);

            if ($siguienteItem['tipo'] === 'teoria') {
                $this->redirect('/estudiante/lecciones/' . $leccion->id . '/contenido#teoria-' . $siguienteItem['id']);
            } elseif ($siguienteItem['tipo'] === 'actividad') {
                $this->redirect('/estudiante/actividades/' . $siguienteItem['id']);
            }
        }

        // If all completed, go to course index
        $this->redirect('/estudiante/cursos/' . $curso_id . '/lecciones');
    }

    public function lecciones($curso_id) {
        $estudiante_id = Auth::getUserId();
        // Verificar si el estudiante está inscrito en el curso
        if (!$this->inscripcionModel->verificarInscripcion($curso_id, $estudiante_id)) {
            $this->redirect('/estudiante');
        }

        $lecciones = $this->leccionModel->obtenerLeccionesPorCurso($curso_id);
        
        // Agregar estado de completitud a cada lección
        foreach ($lecciones as $leccion) {
            $resumen = $this->leccionModel->obtenerResumenProgreso($leccion->id, $estudiante_id);
            $leccion->completada = $resumen->completada;
            $leccion->estado = $resumen->estado;
            $leccion->porcentaje_completado = $resumen->porcentaje;
            $leccion->total_items = $resumen->total_items;
            $leccion->completados = $resumen->completados;
        }

        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        $resumenCurso = null;
        foreach ($this->cursoModel->obtenerResumenCursosPorEstudiante($estudiante_id) as $cursoResumen) {
            if ((int) $cursoResumen->id === (int) $curso_id) {
                $resumenCurso = $cursoResumen;
                break;
            }
        }

        require_once __DIR__ . '/../../views/estudiante/lecciones.php';
    }

    public function contenidoLeccion($leccion_id) {
        $estudiante_id = Auth::getUserId();
        $leccion = $this->leccionModel->obtenerLeccionPorId($leccion_id);

        if (!$leccion) {
            $this->redirect('/estudiante');
        }

        // Verificar si el estudiante está inscrito en el curso de la lección
        if (!$this->inscripcionModel->verificarInscripcion($leccion->curso_id, $estudiante_id)) {
            $this->redirect('/estudiante');
        }

        $teoriaModel = new Teoria();
        $teorias = $teoriaModel->obtenerTeoriasConProgreso($leccion_id, $estudiante_id);

        $actividadModel = new Actividad();
        $actividades = $actividadModel->obtenerActividadesPorLeccion($leccion_id);

        // Verificar estado de completado para cada actividad
        $respuestaModel = new Respuesta();
        foreach ($actividades as $actividad) {
            $respuesta = $respuestaModel->obtenerRespuestaPorEstudianteYActividad($estudiante_id, $actividad->id);
            $actividad->completada = $respuesta ? true : false;
            $actividad->calificacion = $respuesta ? $respuesta->puntuacion : null; // Asumiendo que el campo es 'puntuacion'
        }

        // Determinar siguiente paso sugerido
        $siguienteItem = null;
        
        // 1. Buscar teoría no leída
        foreach ($teorias as $teoria) {
            if (empty($teoria->leido)) {
                $siguienteItem = [
                    'tipo' => 'teoria',
                    'titulo' => $teoria->titulo,
                    'id' => $teoria->id,
                    'mensaje' => 'Continuar leyendo'
                ];
                break;
            }
        }
        
        // 2. Si todas las teorías están leídas, buscar actividad no completada
        if (!$siguienteItem) {
            foreach ($actividades as $actividad) {
                if (empty($actividad->completada)) {
                    $siguienteItem = [
                        'tipo' => 'actividad',
                        'titulo' => $actividad->titulo,
                        'id' => $actividad->id,
                        'mensaje' => 'Siguiente actividad'
                    ];
                    break;
                }
            }
        }

        // 3. Si todo está completado, sugerir siguiente lección (si existe)
        if (!$siguienteItem) {
            // Buscar la siguiente lección en el curso
            $siguienteLeccion = null;
            $leccionesCurso = $this->leccionModel->obtenerLeccionesPorCurso($leccion->curso_id);
            $encontradaActual = false;
            
            foreach ($leccionesCurso as $l) {
                if ($encontradaActual) {
                    $siguienteLeccion = $l;
                    break;
                }
                if ($l->id == $leccion_id) {
                    $encontradaActual = true;
                }
            }
            
            if ($siguienteLeccion) {
                $siguienteItem = [
                    'tipo' => 'leccion',
                    'titulo' => $siguienteLeccion->titulo,
                    'id' => $siguienteLeccion->id,
                    'mensaje' => 'Siguiente lección'
                ];
            } else {
                // Curso completado
                 $siguienteItem = [
                    'tipo' => 'curso_completado',
                    'titulo' => 'Curso Completado',
                    'id' => $leccion->curso_id,
                    'mensaje' => 'Volver al inicio del curso'
                ];
            }
        }

        $resumenProgreso = $this->leccionModel->obtenerResumenProgreso($leccion_id, $estudiante_id);

        require_once __DIR__ . '/../../views/estudiante/contenido_leccion.php';
    }

    public function realizarActividad($actividad_id) {
        $estudiante_id = Auth::getUserId();
        $actividadModel = new Actividad();
        $actividad = $actividadModel->obtenerActividadPorId($actividad_id);

        if (!$actividad) {
            $this->redirect('/estudiante');
        }

        $leccion = $this->leccionModel->obtenerLeccionPorId($actividad->leccion_id);
        
        // Obtener información del curso para el idioma
        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);

        // Verificar si el estudiante está inscrito en el curso de la lección
        if (!$this->inscripcionModel->verificarInscripcion($leccion->curso_id, $estudiante_id)) {
            $this->redirect('/estudiante');
        }

        // Ya no bloqueamos si ya respondió, permitimos reintentos.
        // Podemos obtener la respuesta anterior si quisiéramos mostrarla, pero por ahora solo permitimos nuevo intento.
        $respuestaModel = new Respuesta();
        $respuestaExistente = $respuestaModel->obtenerRespuestaPorEstudianteYActividad($estudiante_id, $actividad_id);
        
        // Obtener configuración específica según el tipo de actividad
        $configActividad = null;
        if ($actividad->tipo_actividad === 'opcion_multiple' || $actividad->tipo_actividad === 'verdadero_falso') {
            $opcionMultipleModel = new OpcionMultiple();
            $dbOptions = $opcionMultipleModel->obtenerOpcionesPorActividad($actividad_id);
            
            $configActividad = []; // Array de preguntas

            // 1. Intentar cargar desde DB (Estructura de una sola pregunta)
            if (!empty($dbOptions)) {
                $configActividad[] = (object)[
                    'id' => 'db',
                    'texto' => $actividad->descripcion, 
                    'opciones' => $dbOptions
                ];
            } 
            // 2. Intentar cargar desde JSON
            else if (!empty($actividad->contenido)) {
                $contenido = json_decode($actividad->contenido);
                
                // Caso A: Estructura de múltiples preguntas ("preguntas": [...])
                if (isset($contenido->preguntas) && is_array($contenido->preguntas)) {
                    foreach ($contenido->preguntas as $idx => $preg) {
                        $opciones = [];
                        if (isset($preg->opciones) && is_array($preg->opciones)) {
                            foreach ($preg->opciones as $optIdx => $opt) {
                                $esCorrecta = 0;
                                if (isset($opt->es_correcta)) {
                                    $esCorrecta = ($opt->es_correcta === true || $opt->es_correcta == 1) ? 1 : 0;
                                } elseif (isset($opt->correcta)) {
                                    $esCorrecta = ($opt->correcta === true || $opt->correcta == 1) ? 1 : 0;
                                }

                                $opciones[] = (object)[
                                    'id' => "q{$idx}_opt{$optIdx}",
                                    'opcion_texto' => $opt->texto ?? $opt->opcion_texto ?? '',
                                    'es_correcta' => $esCorrecta
                                ];
                            }
                        }
                        $configActividad[] = (object)[
                            'id' => $idx,
                            'texto' => $preg->texto ?? '',
                            'opciones' => $opciones
                        ];
                    }
                } 
                // Caso B: Estructura de opciones simples ("opciones": [...])
                elseif (isset($contenido->opciones) && is_array($contenido->opciones)) {
                    $opciones = [];
                    foreach ($contenido->opciones as $index => $opcion) {
                        $obj = new stdClass();
                        $obj->id = 'json_' . $index;
                        
                        if (is_string($opcion)) {
                            $obj->opcion_texto = $opcion;
                            // Check if this index matches the correct answer index
                            $esCorrecta = isset($contenido->respuesta_correcta) && (int)$contenido->respuesta_correcta === $index;
                            $obj->es_correcta = $esCorrecta ? 1 : 0;
                        } else {
                            $obj->opcion_texto = $opcion->texto ?? '';
                            $obj->es_correcta = isset($opcion->correcta) && ($opcion->correcta === true || $opcion->correcta == 1) ? 1 : 0;
                        }
                        
                        $opciones[] = $obj;
                    }
                    $configActividad[] = (object)[
                        'id' => 'json_single',
                        'texto' => $actividad->descripcion,
                        'opciones' => $opciones
                    ];
                }
            }
        } elseif ($actividad->tipo_actividad === 'arrastrar_soltar') {
            $contenido = json_decode($actividad->contenido ?? '{}');
            $configActividad = [];

            // Case 1: Array of pairs (New format)
            if (is_array($contenido)) {
                $configActividad = $contenido;
            } 
            // Case 2: Object with 'pairs' property
            elseif (isset($contenido->pairs) && is_array($contenido->pairs)) {
                $configActividad = $contenido->pairs;
            }
            // Case 3: Object with 'matches' property (Legacy format: key => value)
            elseif (isset($contenido->matches) && (is_array($contenido->matches) || is_object($contenido->matches))) {
                foreach ((array)$contenido->matches as $key => $value) {
                    $configActividad[] = (object)[
                        'id' => $key,
                        'left' => $this->_findContentById($contenido->items ?? [], $key) ?? $key,
                        'right' => $this->_findContentById($contenido->targets ?? [], $value) ?? $value
                    ];
                }
            }
            // Case 4: Object with 'items' and 'targets' but no matches (Legacy format: index-based match)
            elseif (isset($contenido->items) && isset($contenido->targets) && is_array($contenido->items) && is_array($contenido->targets)) {
                 foreach ($contenido->items as $idx => $item) {
                     if (isset($contenido->targets[$idx])) {
                         $configActividad[] = (object)[
                             'id' => $item->id ?? "pair_$idx",
                             'left' => $item->content ?? $item->texto ?? '',
                             'right' => $contenido->targets[$idx]->content ?? $contenido->targets[$idx]->texto ?? ''
                         ];
                     }
                 }
            }
            
            // Mezclar las opciones de la derecha para que no estén alineadas por defecto
            // Pero necesitamos mantener la referencia de cuáles son las opciones disponibles.
            // En la vista haremos shuffle de las opciones de la derecha.
        } elseif ($actividad->tipo_actividad === 'emparejamiento') {
            $contenido = json_decode($actividad->contenido ?? '{}');
            $configActividad = [];
            
            if (isset($contenido->pares) && is_array($contenido->pares)) {
                foreach ($contenido->pares as $idx => $par) {
                    $configActividad[] = (object)[
                        'id' => $idx, // Use index as ID if not provided
                        'left' => $par->left ?? '',
                        'right' => $par->right ?? ''
                    ];
                }
            }
        } elseif ($actividad->tipo_actividad === 'ordenar_palabras') {
            $contenido = json_decode($actividad->contenido ?? '[]');
            $configActividad = [];
            
            // Normalize to array of questions
            $preguntas = [];
            if (is_array($contenido) && isset($contenido[0]->items)) {
                $preguntas = $contenido;
            } elseif (isset($contenido->items)) {
                $preguntas = [$contenido];
            }
            
            foreach ($preguntas as $idx => $q) {
                $qId = $q->id ?? "q$idx";
                $items = [];
                if (isset($q->items) && is_array($q->items)) {
                    foreach ($q->items as $wIdx => $word) {
                        $items[] = (object)[
                            'id' => "{$qId}_word_{$wIdx}",
                            'text' => $word
                        ];
                    }
                    // Shuffle items for display
                    shuffle($items);
                }
                
                $configActividad[] = (object)[
                    'id' => $qId,
                    'instruction' => $q->instruction ?? $q->pregunta ?? 'Ordena correctamente:',
                    'items' => $items
                ];
            }
        } elseif ($actividad->tipo_actividad === 'completar_oracion') {
            $configActividad = json_decode($actividad->contenido);
        } elseif ($actividad->tipo_actividad === 'pronunciacion') {
            $contenido = json_decode($actividad->contenido ?? '[]');
            $configActividad = [];
            
            // Normalize to array of questions
            $preguntas = [];
            if (is_array($contenido) && isset($contenido[0]->frase)) {
                $preguntas = $contenido;
            } elseif (isset($contenido->frase)) {
                $preguntas = [$contenido];
            }
            
            foreach ($preguntas as $idx => $q) {
                $qId = $q->id ?? "q$idx";
                $configActividad[] = (object)[
                    'id' => $qId,
                    'frase' => $q->frase
                ];
            }
        } elseif ($actividad->tipo_actividad === 'escucha' || $actividad->tipo_actividad === 'escritura') {
            $configActividad = json_decode($actividad->contenido);
        }

        // Determine next item if activity is completed
        $siguienteItem = null;
        $respuestasUsuario = [];
        if ($respuestaExistente) {
            $siguienteItem = $this->_determinarSiguienteItem($leccion->id, $estudiante_id);
            
            // Decodificar la respuesta del usuario para mostrar feedback
            $respuestasUsuario = json_decode($respuestaExistente->respuesta_texto, true);
            
            // Handle raw strings or values that aren't JSON arrays
            if (json_last_error() !== JSON_ERROR_NONE && !empty($respuestaExistente->respuesta_texto)) {
                // If JSON decode failed, assume it's a raw string
                $respuestasUsuario = [$respuestaExistente->respuesta_texto];
            } elseif (!is_array($respuestasUsuario)) {
                // If decoded successfully but not an array (e.g. "true", "123", or single string literal)
                // Note: json_decode('"sol"') returns "sol", which is not an array.
                $respuestasUsuario = [$respuestasUsuario];
            }
            
            // Reordenar preguntas para mostrar en el orden en que fueron respondidas (si no es retry)
            if (!isset($_GET['retry']) && is_array($respuestasUsuario) && is_array($configActividad) && !empty($configActividad)) {
                $orderedConfig = [];
                $configMap = [];
                
                // Mapear configuración por ID
                foreach ($configActividad as $item) {
                    $configMap[$item->id] = $item;
                }
                
                // Añadir items en el orden de las respuestas
                foreach ($respuestasUsuario as $key => $val) {
                    if (isset($configMap[$key])) {
                        $orderedConfig[] = $configMap[$key];
                        unset($configMap[$key]);
                    }
                }
                
                // Añadir preguntas restantes (si las hay)
                foreach ($configMap as $item) {
                    $orderedConfig[] = $item;
                }
                
                // Solo actualizar si logramos reordenar algo
                if (!empty($orderedConfig)) {
                    $configActividad = $orderedConfig;
                }
            }
            
            // Si el usuario solicitó retry, barajar preguntas
            if (isset($_GET['retry']) && $_GET['retry'] == '1') {
                if (is_array($configActividad)) {
                    shuffle($configActividad);
                }
            }
        } else {
            // Si es primera vez, también barajar
            if (is_array($configActividad)) {
                shuffle($configActividad);
            }
        }

        // Pasar la configuración a la vista
        $data = [
            'actividad' => $actividad,
            'leccion' => $leccion,
            'curso' => $curso,
            'configActividad' => $configActividad,
            'respuestaExistente' => $respuestaExistente,
            'respuestasUsuario' => $respuestasUsuario,
            'siguienteItem' => $siguienteItem
        ];

        // Extraer variables para la vista
        extract($data);
        
        require_once __DIR__ . '/../../views/estudiante/realizar_actividad.php';
    }

    /**
     * Procesar la respuesta de un estudiante a una actividad
     */
    public function responderActividad($actividad_id) {
        // Verificar que el método sea POST
        $this->requirePost();
        require_csrf();

        $estudiante_id = Auth::getUserId();
        
        // Obtener la actividad
        $actividadModel = new Actividad();
        $actividad = $actividadModel->obtenerActividadPorId($actividad_id);

        if (!$actividad) {
            $this->flash('error', 'Actividad no encontrada.');
            $this->redirect('/estudiante');
        }

        // Obtener la lección
        $leccionModel = new Leccion();
        $leccion = $leccionModel->obtenerLeccionPorId($actividad->leccion_id);

        // Verificar si el estudiante está inscrito
        if (!$this->inscripcionModel->verificarInscripcion($leccion->curso_id, $estudiante_id)) {
            $_SESSION['error'] = 'No estás inscrito en este curso.';
            $this->redirect('/estudiante');
        }

        // Ya no bloqueamos si ya respondió, permitimos reintentos.
        // Si quisiéramos sobrescribir, deberíamos actualizar. 
        // Por ahora, el modelo Respuesta parece insertar una nueva respuesta o quizás deberíamos revisar si hace update.
        // Si el usuario quiere "reintentar", asumimos que quiere mejorar su nota.
        // Respuesta::guardarRespuesta debería manejar lógica de actualización o inserción.
        // Vamos a revisar Respuesta::guardarRespuesta después.

        // Obtener la respuesta del formulario
        $respuesta_input = $_POST['respuesta'] ?? '';
        
        if (empty($respuesta_input)) {
            $this->flash('error', 'Por favor, proporciona una respuesta.');
            $this->redirect('/estudiante/actividades/' . $actividad_id);
        }

        // Si es array (multi-pregunta), asegurarse de que las claves sean numéricas y ordenadas
        if (is_array($respuesta_input)) {
            // Guardar tal cual viene del formulario para preservar el orden (especialmente si se barajaron)
            // No hacemos ksort() para mantener el orden de presentación al usuario
            $respuesta_texto = json_encode($respuesta_input, JSON_UNESCAPED_UNICODE);
        } else {
            $respuesta_texto = $respuesta_input;
        }

        // Instanciar Respuesta Model
        $respuestaModel = new Respuesta();

        // Calcular puntuación según el tipo de actividad
        // Ahora Respuesta::calcularPuntuacion maneja todos los tipos soportados
        $puntuacion = $respuestaModel->calcularPuntuacion($actividad_id, $respuesta_texto);

        // Guardar la respuesta
        $resultado = $respuestaModel->guardarRespuesta($estudiante_id, $actividad_id, $respuesta_texto, $puntuacion);

        if ($resultado) {
            $this->leccionModel->sincronizarProgresoEstudiante($actividad->leccion_id, $estudiante_id);
            $_SESSION['mensaje'] = 'Respuesta enviada exitosamente.' . ($puntuacion !== null ? " Puntuación: {$puntuacion}" : '');
        } else {
            $_SESSION['error'] = 'Error al guardar la respuesta.';
        }

        // Redirigir de vuelta a la actividad para ver resultados
        $this->redirect('/estudiante/actividades/' . $actividad_id);
    }

    public function marcarTeoria($teoria_id) {
        $this->requirePost();
        require_csrf();

        $estudiante_id = Auth::getUserId();
        
        $teoriaModel = new Teoria();
        $teoriaModel->marcarComoLeida($estudiante_id, $teoria_id);
        $teoria = $teoriaModel->obtenerTeoriaPorId($teoria_id);
        if ($teoria) {
            $this->leccionModel->sincronizarProgresoEstudiante($teoria->leccion_id, $estudiante_id);
        }
        
        // Redireccionar de vuelta a la página anterior
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        } else {
            // Fallback: obtener la lección para redirigir
            if ($teoria) {
                $this->redirect('/estudiante/lecciones/' . $teoria->leccion_id . '/contenido');
            } else {
                $this->redirect('/estudiante');
            }
        }
    }

    private function _determinarSiguienteItem($leccion_id, $estudiante_id) {
        $leccionModel = new Leccion();
        $leccion = $leccionModel->obtenerLeccionPorId($leccion_id);
        
        $teoriaModel = new Teoria();
        $teorias = $teoriaModel->obtenerTeoriasConProgreso($leccion_id, $estudiante_id);

        $actividadModel = new Actividad();
        $actividades = $actividadModel->obtenerActividadesPorLeccion($leccion_id);
        
        $respuestaModel = new Respuesta();
        foreach ($actividades as $actividad) {
            $respuesta = $respuestaModel->obtenerRespuestaPorEstudianteYActividad($estudiante_id, $actividad->id);
            $actividad->completada = $respuesta ? true : false;
        }

        // 1. Buscar teoría no leída
        foreach ($teorias as $teoria) {
            if (empty($teoria->leido)) {
                return [
                    'tipo' => 'teoria',
                    'titulo' => $teoria->titulo,
                    'id' => $teoria->id,
                    'mensaje' => 'Continuar leyendo'
                ];
            }
        }
        
        // 2. Si todas las teorías están leídas, buscar actividad no completada
        foreach ($actividades as $actividad) {
            if (empty($actividad->completada)) {
                return [
                    'tipo' => 'actividad',
                    'titulo' => $actividad->titulo,
                    'id' => $actividad->id,
                    'mensaje' => 'Siguiente actividad'
                ];
            }
        }

        // 3. Si todo está completado, sugerir siguiente lección (si existe)
        $siguienteLeccion = null;
        $leccionesCurso = $leccionModel->obtenerLeccionesPorCurso($leccion->curso_id);
        $encontradaActual = false;
        
        foreach ($leccionesCurso as $l) {
            if ($encontradaActual) {
                $siguienteLeccion = $l;
                break;
            }
            if ($l->id == $leccion_id) {
                $encontradaActual = true;
            }
        }
        
        if ($siguienteLeccion) {
            return [
                'tipo' => 'leccion',
                'titulo' => $siguienteLeccion->titulo,
                'id' => $siguienteLeccion->id,
                'mensaje' => 'Siguiente lección'
            ];
        } else {
            // Curso completado
             return [
                'tipo' => 'curso_completado',
                'titulo' => 'Curso Completado',
                'id' => $leccion->curso_id,
                'mensaje' => 'Volver al inicio del curso'
            ];
        }
    }

    private function _findContentById($array, $id) {
        if (!is_array($array)) return null;
        foreach ($array as $item) {
            if (is_object($item) && isset($item->id) && $item->id == $id) {
                return $item->content ?? $item->texto ?? null;
            }
        }
        return null;
    }
}
