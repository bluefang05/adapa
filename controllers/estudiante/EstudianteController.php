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
require_once __DIR__ . '/../../models/ProfesorPlan.php';

class EstudianteController extends Controller {
    private $cursoModel;
    private $inscripcionModel;
    private $leccionModel;
    private $planModel;

    public function __construct() {
        $this->requireRole('estudiante');
        $this->cursoModel = new Curso();
        $this->inscripcionModel = new Inscripcion();
        $this->leccionModel = new Leccion();
        $this->planModel = new ProfesorPlan();
    }

    public function index() {
        $estudiante_id = Auth::getUserId();
        $cursosInscritos = $this->cursoModel->obtenerResumenCursosPorEstudiante($estudiante_id);
        $filtrosCatalogo = [
            'idioma_objetivo' => trim($_GET['idioma_objetivo'] ?? ''),
            'idioma_base' => trim($_GET['idioma_base'] ?? ''),
            'nivel_objetivo' => trim($_GET['nivel_objetivo'] ?? ''),
            'tipo_recorrido' => trim($_GET['tipo_recorrido'] ?? ''),
        ];
        $cursosPublicos = $this->cursoModel->obtenerCursosDisponiblesParaExplorar($filtrosCatalogo);
        $opcionesCefr = Curso::obtenerOpcionesCefr();

        // Filtrar cursos públicos para no mostrar los ya inscritos
        $cursosInscritosIds = array_map(function($curso) {
            return $curso->id;
        }, $cursosInscritos);

        $cursosDisponibles = array_filter($cursosPublicos, function($curso) use ($cursosInscritosIds) {
            return !in_array($curso->id, $cursosInscritosIds);
        });

        $dashboardFocus = $this->_buildDashboardFocus($cursosInscritos, $estudiante_id);
        $resourceLanguage = $dashboardFocus && !empty($dashboardFocus['course'])
            ? Curso::obtenerIdiomaObjetivo($dashboardFocus['course'])
            : null;
        $recommendedResources = app_useful_resources_for_language($resourceLanguage, 4);
        $courseActionMap = [];
        foreach ($cursosInscritos as $curso) {
            $courseActionMap[$curso->id] = $this->_buildCourseNextStep($curso->id, $estudiante_id);
        }

        require_once __DIR__ . '/../../views/estudiante/index.php';
    }

    public function inscribir($curso_id) {
        $this->requirePost();
        require_csrf();
        $estudiante_id = Auth::getUserId();
        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);

        if (
            !$curso
            || !$curso->es_publico
            || app_course_editorial_state_value($curso) !== 'publicado'
            || !$this->cursoModel->cursoTieneLeccionesPublicadas((int) $curso_id)
            || (int) $curso->requiere_codigo === 1
            || (int) $curso->inscripcion_abierta !== 1
        ) {
            $this->flash('error', 'Este curso no admite inscripcion directa. Usa el codigo de acceso del profesor.');
            $this->redirect('/estudiante');
        }

        [$puedeInscribir, $mensajePlan] = $this->planModel->puedeAgregarEstudiante($curso_id);
        if (!$puedeInscribir) {
            $this->flash('error', $mensajePlan);
            $this->redirect('/estudiante');
        }

        if ($this->inscripcionModel->inscribirEstudiante($curso_id, $estudiante_id)) {
            $this->flash('mensaje', 'Inscripcion completada.');
            $this->redirect('/estudiante');
        } else {
            $this->flash('error', 'Error al inscribirse al curso.');
            $this->redirect('/estudiante');
        }
    }

    public function canjearCodigo() {
        $this->requirePost();
        require_csrf();

        $estudiante_id = Auth::getUserId();
        $instanciaId = Auth::getInstanciaId();
        $codigo = trim($_POST['codigo_acceso'] ?? '');

        if ($codigo === '') {
            $this->flash('error', 'Introduce un codigo de acceso valido.');
            $this->redirect('/estudiante');
        }

        $curso = $this->cursoModel->validarCodigoDeAcceso($codigo, $estudiante_id, $instanciaId);

        if (!$curso) {
            $this->flash('error', 'El codigo no existe, no esta activo o no corresponde a tu acceso.');
            $this->redirect('/estudiante');
        }

        if (!$this->cursoModel->cursoTieneLeccionesPublicadas((int) $curso->id)) {
            $this->flash('error', 'Ese curso todavia no tiene lecciones visibles para estudiantes.');
            $this->redirect('/estudiante');
        }

        [$puedeInscribir, $mensajePlan] = $this->planModel->puedeAgregarEstudiante($curso->id);
        if (!$puedeInscribir) {
            $this->flash('error', $mensajePlan);
            $this->redirect('/estudiante');
        }

        if ($this->inscripcionModel->verificarInscripcion($curso->id, $estudiante_id)) {
            $this->flash('mensaje', 'Ya estabas inscrito en "' . $curso->titulo . '".');
            $this->redirect('/estudiante');
        }

        if (!$this->inscripcionModel->inscribirEstudiante($curso->id, $estudiante_id)) {
            $this->flash('error', 'No se pudo activar el curso con ese codigo.');
            $this->redirect('/estudiante');
        }

        if (isset($curso->codigo_id)) {
            $this->cursoModel->registrarUsoCodigo($curso->codigo_id, $estudiante_id);
        }

        $this->flash('mensaje', 'Acceso concedido a "' . $curso->titulo . '". Ya puedes tomar la clase de tu profesor.');
        $this->redirect('/estudiante/cursos/' . $curso->id . '/lecciones');
    }

    public function continuarCurso($curso_id) {
        $estudiante_id = Auth::getUserId();
        
        // Verificar si el estudiante está inscrito en el curso
        if (!$this->inscripcionModel->verificarInscripcion($curso_id, $estudiante_id)) {
            $this->redirect('/estudiante');
        }

        $lecciones = $this->leccionModel->obtenerLeccionesPublicadasPorCurso($curso_id);

        if (empty($lecciones)) {
            $this->flash('mensaje', 'Este curso todavia no tiene lecciones visibles. Vuelve mas tarde.');
            $this->redirect('/estudiante');
        }
        
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

        $lecciones = $this->leccionModel->obtenerLeccionesPublicadasPorCurso($curso_id);

        if (empty($lecciones)) {
            $this->flash('mensaje', 'Este curso todavia no tiene lecciones visibles para continuar.');
            $this->redirect('/estudiante');
        }
        
        // Agregar estado de completitud a cada lección
        foreach ($lecciones as $leccion) {
            $resumen = $this->leccionModel->obtenerResumenProgreso($leccion->id, $estudiante_id);
            $leccion->completada = $resumen->completada;
            $leccion->estado = $resumen->estado;
            $leccion->porcentaje_completado = $resumen->porcentaje;
            $leccion->total_items = $resumen->total_items;
            $leccion->completados = $resumen->completados;
            $stateMeta = $this->_buildLessonStateMeta($leccion, $resumen, $estudiante_id);
            $leccion->state_label = $stateMeta['label'];
            $leccion->state_tone = $stateMeta['tone'];
            $leccion->summary_hint = $stateMeta['summary_hint'];
            $leccion->cta_label = $stateMeta['cta_label'];
            $leccion->is_recommended = !empty($stateMeta['is_recommended']);
        }

        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        $resumenCurso = null;
        foreach ($this->cursoModel->obtenerResumenCursosPorEstudiante($estudiante_id) as $cursoResumen) {
            if ((int) $cursoResumen->id === (int) $curso_id) {
                $resumenCurso = $cursoResumen;
                break;
            }
        }

        $courseResources = app_useful_resources_for_language(Curso::obtenerIdiomaObjetivo($curso), 4);
        $courseJourney = $this->_buildCourseNextStep($curso_id, $estudiante_id);

        require_once __DIR__ . '/../../views/estudiante/lecciones.php';
    }

    public function contenidoLeccion($leccion_id) {
        $estudiante_id = Auth::getUserId();
        $leccion = $this->leccionModel->obtenerLeccionPublicadaPorId($leccion_id);

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
            $leccionesCurso = $this->leccionModel->obtenerLeccionesPublicadasPorCurso($leccion->curso_id);
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
        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        $courseResources = app_useful_resources_for_language(Curso::obtenerIdiomaObjetivo($curso), 4);
        $lessonJourney = $this->_buildLessonJourney($leccion, $teorias, $actividades, $resumenProgreso, $siguienteItem);

        foreach ($teorias as $teoria) {
            $teoria->is_next = $siguienteItem && $siguienteItem['tipo'] === 'teoria' && (int) $siguienteItem['id'] === (int) $teoria->id;
        }

        foreach ($actividades as $actividad) {
            $actividad->is_next = $siguienteItem && $siguienteItem['tipo'] === 'actividad' && (int) $siguienteItem['id'] === (int) $actividad->id;
            if (!empty($actividad->completada)) {
                $actividad->student_status_label = 'Completada';
                $actividad->student_status_copy = isset($actividad->calificacion)
                    ? 'Ya la resolviste. Resultado registrado: ' . $actividad->calificacion . ' pts.'
                    : 'Ya la resolviste. Puedes entrar para revisar o practicar otra vez.';
            } elseif ($actividad->is_next) {
                $actividad->student_status_label = 'Sigue aqui';
                $actividad->student_status_copy = 'Esta es la practica que mejor mantiene tu ritmo ahora mismo.';
            } else {
                $actividad->student_status_label = 'Pendiente';
                $actividad->student_status_copy = 'Aun no la respondes. Puedes dejarla para despues si primero quieres cerrar otra teoria.';
            }
        }

        require_once __DIR__ . '/../../views/estudiante/contenido_leccion.php';
    }

    public function recursos() {
        $estudiante_id = Auth::getUserId();
        $language = trim($_GET['idioma'] ?? '');
        $language = $language !== '' ? $language : null;

        $resources = app_useful_resources_for_language($language);
        $groupedResources = app_group_useful_resources_by_category($resources);
        $resourceCategories = app_useful_resource_category_labels();
        $languageLabel = $language ? app_language_label($language, ucfirst($language)) : 'Todos los idiomas';
        $relatedCourses = [];
        $resourceContextCourse = null;

        if ($language !== null) {
            $studentCourses = $this->cursoModel->obtenerResumenCursosPorEstudiante($estudiante_id);
            $relatedCourses = array_values(array_filter($studentCourses, static function ($curso) use ($language): bool {
                return Curso::obtenerIdiomaObjetivo($curso) === $language;
            }));
            $resourceContextCourse = count($relatedCourses) === 1 ? $relatedCourses[0] : null;
        }

        require_once __DIR__ . '/../../views/estudiante/recursos.php';
    }

    public function realizarActividad($actividad_id) {
        $estudiante_id = Auth::getUserId();
        $actividadModel = new Actividad();
        $actividad = $actividadModel->obtenerActividadPorId($actividad_id);

        if (!$actividad) {
            $this->redirect('/estudiante');
        }

        $leccion = $this->leccionModel->obtenerLeccionPublicadaPorId($actividad->leccion_id);
        if (!$leccion) {
            $this->flash('error', 'La leccion de esta actividad ya no esta disponible para estudiantes.');
            $this->redirect('/estudiante');
        }
        
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
                            'image_url' => $preg->image_url ?? null,
                            'image_alt' => $preg->image_alt ?? ($preg->texto ?? 'Imagen de apoyo'),
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
                        'image_url' => $contenido->image_url ?? null,
                        'image_alt' => $contenido->image_alt ?? ($actividad->descripcion ?: 'Imagen de apoyo'),
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
        $resumenLeccion = $this->leccionModel->obtenerResumenProgreso($leccion->id, $estudiante_id);
        $nextActionUrl = $this->_buildStudentNextActionUrl($siguienteItem, $leccion->id);
        $activityOutcome = $this->_buildActivityOutcome($actividad, $respuestaExistente, $siguienteItem, $resumenLeccion);
        $activitySummaryCta = $this->_buildStudentNextActionLabel($siguienteItem, $respuestaExistente, $leccion->id);
        $activityGuidance = $this->_buildActivityGuidance($actividad, $respuestaExistente, $siguienteItem, $resumenLeccion);
        $activityLanguageResources = app_useful_resources_for_language(Curso::obtenerIdiomaObjetivo($curso), 3);

        $data = [
            'actividad' => $actividad,
            'leccion' => $leccion,
            'curso' => $curso,
            'configActividad' => $configActividad,
            'respuestaExistente' => $respuestaExistente,
            'respuestasUsuario' => $respuestasUsuario,
            'siguienteItem' => $siguienteItem,
            'resumenLeccion' => $resumenLeccion,
            'activityOutcome' => $activityOutcome,
            'nextActionUrl' => $nextActionUrl,
            'activitySummaryCta' => $activitySummaryCta,
            'activityGuidance' => $activityGuidance,
            'activityLanguageResources' => $activityLanguageResources
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
        $leccion = $leccionModel->obtenerLeccionPublicadaPorId($actividad->leccion_id);
        if (!$leccion) {
            $this->flash('error', 'La leccion de esta actividad ya no esta disponible para estudiantes.');
            $this->redirect('/estudiante');
        }

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
        $evaluacion = $respuestaModel->evaluarRespuesta($actividad_id, $respuesta_texto);
        $puntuacion = $evaluacion['puntuacion'] ?? null;
        $comentarios = $evaluacion['comentarios'] ?? null;

        // Guardar la respuesta
        $resultado = $respuestaModel->guardarRespuesta($estudiante_id, $actividad_id, $respuesta_texto, $puntuacion, $comentarios);

        if ($resultado) {
            $this->leccionModel->sincronizarProgresoEstudiante($actividad->leccion_id, $estudiante_id);
            $_SESSION['mensaje'] = 'Respuesta enviada exitosamente.' . ($puntuacion !== null ? " Puntuación: {$puntuacion}" : '');
        } else {
            $_SESSION['error'] = 'Error al guardar la respuesta.';
        }

        if ($resultado) {
            $resumenLeccion = $this->leccionModel->obtenerResumenProgreso($leccion->id, $estudiante_id);
            $siguienteItem = $this->_determinarSiguienteItem($leccion->id, $estudiante_id);
            $message = $this->_buildResponseSuccessMessage($actividad, $puntuacion, $resumenLeccion, $siguienteItem);

            unset($_SESSION['mensaje']);
            $this->flash('success', $message);
        } else {
            unset($_SESSION['error']);
            $this->flash('error', 'Error al guardar la respuesta.');
        }

        // Redirigir de vuelta a la actividad para ver resultados
        $this->redirect('/estudiante/actividades/' . $actividad_id);
    }

    public function marcarTeoria($teoria_id) {
        $this->requirePost();
        require_csrf();

        $estudiante_id = Auth::getUserId();
        
        $teoriaModel = new Teoria();
        $teoria = $teoriaModel->obtenerTeoriaPorId($teoria_id);

        if (!$teoria) {
            $this->flash('error', 'La teoria indicada no existe o ya no esta disponible.');
            $this->redirect('/estudiante');
        }

        $leccion = $this->leccionModel->obtenerLeccionPublicadaPorId($teoria->leccion_id);
        if (!$leccion || !$this->inscripcionModel->verificarInscripcion($leccion->curso_id, $estudiante_id)) {
            $this->flash('error', 'No puedes marcar teoria en una leccion que ya no esta disponible para tu cuenta.');
            $this->redirect('/estudiante');
        }

        $teoriaModel->marcarComoLeida($estudiante_id, $teoria_id);
        $this->leccionModel->sincronizarProgresoEstudiante($teoria->leccion_id, $estudiante_id);
        
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
        $leccion = $leccionModel->obtenerLeccionPublicadaPorId($leccion_id);
        if (!$leccion) {
            return null;
        }
        
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
        $leccionesCurso = $leccionModel->obtenerLeccionesPublicadasPorCurso($leccion->curso_id);
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

    private function _buildStudentNextActionUrl($siguienteItem, $leccion_id) {
        if (!$siguienteItem || empty($siguienteItem['tipo'])) {
            return url('/estudiante/lecciones/' . $leccion_id . '/contenido');
        }

        if ($siguienteItem['tipo'] === 'actividad') {
            return url('/estudiante/actividades/' . $siguienteItem['id']);
        }

        if ($siguienteItem['tipo'] === 'leccion') {
            return url('/estudiante/lecciones/' . $siguienteItem['id'] . '/contenido');
        }

        if ($siguienteItem['tipo'] === 'teoria') {
            return url('/estudiante/lecciones/' . $leccion_id . '/contenido#teoria-' . $siguienteItem['id']);
        }

        return url('/estudiante/cursos/' . $siguienteItem['id'] . '/lecciones');
    }

    private function _buildActivityOutcome($actividad, $respuestaExistente, $siguienteItem, $resumenLeccion) {
        if (!$respuestaExistente) {
            return [
                'label' => 'Lista para resolver',
                'tone' => 'info',
                'headline' => 'Completa esta practica para consolidar la leccion.',
                'summary' => 'Cuando envies tu respuesta, aqui veras un cierre mas claro con tu avance y el siguiente paso recomendado.',
                'next_hint' => 'Empieza por responder con calma y luego revisa tu resultado.',
                'score' => null,
                'max_score' => (float) ($actividad->puntos_maximos ?? 0),
                'lesson_progress' => (int) ($resumenLeccion->porcentaje ?? 0),
            ];
        }

        $puntuacion = $respuestaExistente->puntuacion;
        $maxPuntos = (float) ($actividad->puntos_maximos ?? 0);

        if ($puntuacion === null && in_array($actividad->tipo_actividad, ['escritura', 'escucha'], true)) {
            $label = 'En revision';
            $tone = 'info';
            $headline = 'Tu respuesta ya esta guardada y quedo pendiente de revision.';
            $summary = 'Mientras llega la calificacion, puedes seguir con la leccion sin perder continuidad.';
        } elseif ($maxPuntos > 0 && (float) $puntuacion >= $maxPuntos) {
            $label = 'Bien resuelta';
            $tone = 'success';
            $headline = 'Actividad completada con el puntaje maximo.';
            $summary = 'Buen cierre. Mantienes el ritmo de esta leccion con una respuesta solida.';
        } elseif ((float) $puntuacion > 0) {
            $label = 'Resuelta con margen';
            $tone = 'accent';
            $headline = 'La actividad ya cuenta, pero aun hay espacio para mejorar.';
            $summary = 'Te conviene comparar tus errores con la solucion correcta y luego seguir.';
        } else {
            $label = 'Conviene repasar';
            $tone = 'warning';
            $headline = 'La respuesta quedo registrada, pero esta actividad todavia no esta dominada.';
            $summary = 'No hace falta atascarse: repasa la pista correcta y vuelve a intentarla cuando quieras.';
        }

        $nextHint = 'Vuelve a la leccion para seguir con el flujo normal.';
        if ($siguienteItem && !empty($siguienteItem['tipo'])) {
            if ($siguienteItem['tipo'] === 'teoria') {
                $nextHint = 'Siguiente paso: termina la teoria pendiente antes de la proxima practica.';
            } elseif ($siguienteItem['tipo'] === 'actividad') {
                $nextHint = 'Siguiente paso: pasa a la proxima actividad de esta leccion.';
            } elseif ($siguienteItem['tipo'] === 'leccion') {
                $nextHint = 'Siguiente paso: esta leccion ya esta cerrada; puedes abrir la siguiente.';
            } elseif ($siguienteItem['tipo'] === 'curso_completado') {
                $nextHint = 'Has cerrado esta leccion y ya puedes volver al curso completo.';
            }
        }

        return [
            'label' => $label,
            'tone' => $tone,
            'headline' => $headline,
            'summary' => $summary,
            'next_hint' => $nextHint,
            'score' => $puntuacion,
            'max_score' => $maxPuntos > 0 ? $maxPuntos : null,
            'lesson_progress' => (int) ($resumenLeccion->porcentaje ?? 0),
        ];
    }

    private function _buildDashboardFocus($cursosInscritos, $estudiante_id) {
        if (empty($cursosInscritos)) {
            return null;
        }

        $focusCourse = null;
        foreach ($cursosInscritos as $curso) {
            if (($curso->estado_progreso ?? '') === 'en_progreso') {
                $focusCourse = $curso;
                break;
            }
        }

        if ($focusCourse === null) {
            $focusCourse = $cursosInscritos[0];
        }

        $nextStep = $this->_buildCourseNextStep($focusCourse->id, $estudiante_id);

        return [
            'course' => $focusCourse,
            'next_step' => $nextStep,
            'headline' => $nextStep['headline'] ?? ('Retoma ' . $focusCourse->titulo),
            'summary' => $nextStep['summary'] ?? 'Entra directo a lo siguiente que te conviene completar.',
            'cta_label' => $nextStep['cta_label'] ?? 'Continuar ahora',
            'url' => $nextStep['url'] ?? url('/estudiante/cursos/' . $focusCourse->id . '/continuar'),
        ];
    }

    private function _buildCourseNextStep($curso_id, $estudiante_id) {
        $lecciones = $this->leccionModel->obtenerLeccionesPublicadasPorCurso($curso_id);

        foreach ($lecciones as $leccion) {
            $resumen = $this->leccionModel->obtenerResumenProgreso($leccion->id, $estudiante_id);
            if (!empty($resumen->completada)) {
                continue;
            }

            $siguienteItem = $this->_determinarSiguienteItem($leccion->id, $estudiante_id);
            $url = $this->_buildStudentNextActionUrl($siguienteItem, $leccion->id);

            if ($siguienteItem && ($siguienteItem['tipo'] ?? '') === 'teoria') {
                return [
                    'label' => 'Continua con teoria',
                    'tone' => 'info',
                    'headline' => 'Sigue en ' . $leccion->titulo,
                    'summary' => 'Empieza por "' . $siguienteItem['titulo'] . '" para entrar a la practica con contexto.',
                    'cta_label' => 'Ir a teoria',
                    'url' => $url,
                    'lesson_id' => $leccion->id,
                    'lesson_title' => $leccion->titulo,
                    'type' => 'teoria',
                ];
            }

            if ($siguienteItem && ($siguienteItem['tipo'] ?? '') === 'actividad') {
                return [
                    'label' => 'Continua con practica',
                    'tone' => 'accent',
                    'headline' => 'Tu siguiente practica esta en ' . $leccion->titulo,
                    'summary' => 'Ya puedes entrar a "' . $siguienteItem['titulo'] . '" para mantener el ritmo.',
                    'cta_label' => 'Resolver actividad',
                    'url' => $url,
                    'lesson_id' => $leccion->id,
                    'lesson_title' => $leccion->titulo,
                    'type' => 'actividad',
                ];
            }

            return [
                'label' => 'Retoma el curso',
                'tone' => 'info',
                'headline' => 'Vuelve a ' . $leccion->titulo,
                'summary' => 'Abre la leccion para ver todo lo pendiente y seguir sin perder el hilo.',
                'cta_label' => 'Abrir leccion',
                'url' => url('/estudiante/lecciones/' . $leccion->id . '/contenido'),
                'lesson_id' => $leccion->id,
                'lesson_title' => $leccion->titulo,
                'type' => 'leccion',
            ];
        }

        return [
            'label' => 'Curso al dia',
            'tone' => 'success',
            'headline' => 'Has completado todo lo disponible en este curso.',
            'summary' => 'Puedes repasar cualquier leccion o explorar el recorrido completo otra vez.',
            'cta_label' => 'Ver lecciones',
            'url' => url('/estudiante/cursos/' . $curso_id . '/lecciones'),
            'type' => 'curso_completado',
        ];
    }

    private function _buildLessonStateMeta($leccion, $resumen, $estudiante_id) {
        $nextStep = $this->_determinarSiguienteItem($leccion->id, $estudiante_id);

        if (($resumen->estado ?? '') === 'completada') {
            return [
                'label' => 'Completada',
                'tone' => 'success',
                'summary_hint' => 'Ya cerraste esta leccion. Puedes repasarla o saltar al siguiente bloque.',
                'cta_label' => 'Repasar leccion',
                'is_recommended' => false,
            ];
        }

        if ($nextStep && ($nextStep['tipo'] ?? '') === 'teoria') {
            return [
                'label' => 'Empieza por teoria',
                'tone' => 'info',
                'summary_hint' => 'Tu siguiente paso mas claro es "' . $nextStep['titulo'] . '".',
                'cta_label' => 'Leer teoria',
                'is_recommended' => true,
            ];
        }

        if ($nextStep && ($nextStep['tipo'] ?? '') === 'actividad') {
            return [
                'label' => 'Lista para practicar',
                'tone' => 'accent',
                'summary_hint' => 'Ya puedes entrar a "' . $nextStep['titulo'] . '".',
                'cta_label' => 'Resolver actividad',
                'is_recommended' => true,
            ];
        }

        return [
            'label' => ($resumen->estado ?? '') === 'en_progreso' ? 'En progreso' : 'Pendiente',
            'tone' => ($resumen->estado ?? '') === 'en_progreso' ? 'accent' : 'warning',
            'summary_hint' => 'Abre la leccion para ver teoria, practica y siguiente paso recomendado.',
            'cta_label' => 'Abrir leccion',
            'is_recommended' => false,
        ];
    }

    private function _buildLessonJourney($leccion, $teorias, $actividades, $resumenProgreso, $siguienteItem) {
        $remainingTheory = max(0, (int) ($resumenProgreso->total_teorias ?? 0) - (int) ($resumenProgreso->teorias_completadas ?? 0));
        $remainingActivities = max(0, (int) ($resumenProgreso->total_actividades ?? 0) - (int) ($resumenProgreso->actividades_completadas ?? 0));

        $stateLabel = 'Pendiente';
        $stateTone = 'warning';
        if (($resumenProgreso->estado ?? '') === 'completada') {
            $stateLabel = 'Completada';
            $stateTone = 'success';
        } elseif (($resumenProgreso->estado ?? '') === 'en_progreso') {
            $stateLabel = 'En progreso';
            $stateTone = 'accent';
        }

        $nextCopy = 'Sigue con la teoria para entrar mejor a la practica.';
        if ($siguienteItem && !empty($siguienteItem['titulo'])) {
            $nextCopy = $siguienteItem['mensaje'] . ': ' . $siguienteItem['titulo'];
        } elseif (($resumenProgreso->estado ?? '') === 'completada') {
            $nextCopy = 'Leccion cerrada. Puedes repasar o abrir la siguiente.';
        }

        return [
            'state_label' => $stateLabel,
            'state_tone' => $stateTone,
            'remaining_theory' => $remainingTheory,
            'remaining_activities' => $remainingActivities,
            'completed_items_copy' => (int) ($resumenProgreso->teorias_completadas ?? 0) . ' piezas de teoria y ' . (int) ($resumenProgreso->actividades_completadas ?? 0) . ' actividades.',
            'remaining_items_copy' => $remainingTheory . ' teorias y ' . $remainingActivities . ' actividades para cerrar esta leccion.',
            'next_copy' => $nextCopy,
            'practice_ready' => $remainingTheory === 0 && !empty($actividades),
        ];
    }

    private function _buildStudentNextActionLabel($siguienteItem, $respuestaExistente, $leccion_id) {
        if (!$respuestaExistente) {
            return 'Volver a la leccion';
        }

        if (!$siguienteItem || empty($siguienteItem['tipo'])) {
            return 'Volver a la leccion';
        }

        if ($siguienteItem['tipo'] === 'teoria') {
            return 'Ir a teoria';
        }

        if ($siguienteItem['tipo'] === 'actividad') {
            return 'Ir a la siguiente actividad';
        }

        if ($siguienteItem['tipo'] === 'leccion') {
            return 'Abrir siguiente leccion';
        }

        if ($siguienteItem['tipo'] === 'curso_completado') {
            return 'Volver al curso';
        }

        return 'Volver a la leccion';
    }

    private function _buildActivityGuidance($actividad, $respuestaExistente, $siguienteItem, $resumenLeccion) {
        $guidance = [
            [
                'title' => 'Antes de responder',
                'copy' => !empty($actividad->instrucciones)
                    ? $actividad->instrucciones
                    : 'Lee con calma la consigna y usa el recurso de apoyo solo si te destraba una duda puntual.',
            ],
            [
                'title' => 'Como saber si vas bien',
                'copy' => !empty($respuestaExistente)
                    ? 'Tu respuesta ya esta registrada. Usa el feedback de abajo para detectar que conviene repetir o reforzar.'
                    : 'Busca una respuesta clara, no solo rapida. Si la actividad mezcla varias preguntas, intenta cerrarlas en orden.',
            ],
            [
                'title' => 'Despues de enviar',
                'copy' => !empty($resumenLeccion->completada)
                    ? 'Si completas esta practica, la leccion puede quedar cerrada y podras pasar al siguiente bloque.'
                    : (($siguienteItem && ($siguienteItem['tipo'] ?? '') === 'actividad')
                        ? 'Cuando termines, lo mas probable es que pases directo a la siguiente actividad de la leccion.'
                        : 'Cuando termines, la pantalla te dira con claridad si te conviene volver a teoria, seguir o repasar.'),
            ],
        ];

        return $guidance;
    }

    private function _buildResponseSuccessMessage($actividad, $puntuacion, $resumenLeccion, $siguienteItem) {
        $message = 'Respuesta guardada.';

        if ($puntuacion !== null) {
            $message .= ' Resultado: ' . rtrim(rtrim(number_format((float) $puntuacion, 2, '.', ''), '0'), '.') . ' puntos.';
        } elseif (in_array($actividad->tipo_actividad, ['escritura', 'escucha'], true)) {
            $message .= ' Quedo pendiente de revision.';
        }

        if (!empty($resumenLeccion->completada)) {
            return $message . ' Leccion completada.';
        }

        if ($siguienteItem && !empty($siguienteItem['titulo'])) {
            return $message . ' Siguiente paso: ' . $siguienteItem['titulo'] . '.';
        }

        return $message . ' Vuelve a la leccion para seguir con el recorrido.';
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
