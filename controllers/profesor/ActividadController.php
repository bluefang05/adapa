<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Actividad.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/MediaRecurso.php';
require_once __DIR__ . '/../../models/ProfesorPlan.php';
require_once __DIR__ . '/../../core/Auth.php';

class ActividadController extends Controller
{
    private $actividadModel;
    private $leccionModel;
    private $cursoModel;
    private $mediaModel;
    private $planModel;

    public function __construct()
    {
        $this->requireRole('profesor');
        $this->actividadModel = new Actividad();
        $this->leccionModel = new Leccion();
        $this->cursoModel = new Curso();
        $this->mediaModel = new MediaRecurso();
        $this->planModel = new ProfesorPlan();
    }

    private function tiposActividad()
    {
        return [
            'opcion_multiple' => 'Opcion Multiple',
            'verdadero_falso' => 'Verdadero/Falso',
            'respuesta_corta' => 'Respuesta Corta',
            'respuesta_larga' => 'Respuesta Larga',
            'arrastrar_soltar' => 'Arrastrar y Soltar',
            'ordenar_palabras' => 'Ordenar Palabras',
            'completar_oracion' => 'Completar Oracion',
            'emparejamiento' => 'Emparejamiento',
            'escucha' => 'Escucha',
            'codigo' => 'Codigo',
            'proyecto' => 'Proyecto'
        ];
    }

    private function obtenerLeccionAutorizada($leccionId)
    {
        $leccion = $this->leccionModel->obtenerLeccionPorId($leccionId);
        if (!$leccion) {
            $this->flash('error', 'Leccion no encontrada');
            $this->redirect('/profesor/cursos');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || (int) $curso->creado_por !== (int) Auth::getUserId()) {
            $this->flash('error', 'No tienes permiso para acceder a este recurso');
            $this->redirect('/profesor/cursos');
        }

        return $leccion;
    }

    private function obtenerActividadAutorizada($actividadId)
    {
        $actividad = $this->actividadModel->obtenerActividadPorId($actividadId);
        if (!$actividad) {
            $this->flash('error', 'Actividad no encontrada');
            $this->redirect('/profesor/cursos');
        }

        $leccion = $this->obtenerLeccionAutorizada($actividad->leccion_id);

        return [$actividad, $leccion];
    }

    private function guardarActividadTemporal($leccionId, $tipoActividad)
    {
        $_SESSION['actividad_temp'] = [
            'leccion_id' => $leccionId,
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'tipo_actividad' => $tipoActividad,
            'instrucciones' => $_POST['descripcion'] ?? '',
            'orden' => $_POST['orden'] ?? 1,
            'tiempo_limite_minutos' => $_POST['tiempo_limite_minutos'] ?? 5,
            'puntos_maximos' => $_POST['puntos_maximos'] ?? 10
        ];
    }

    private function normalizarContenidoConfig($tipo)
    {
        switch ($tipo) {
            case 'opcion_multiple':
            case 'verdadero_falso':
            case 'respuesta_larga':
            case 'codigo':
            case 'proyecto':
            case 'emparejamiento':
            case 'escucha':
                $contenidoInput = $_POST['contenido'] ?? '{}';
                return is_string($contenidoInput) ? (json_decode($contenidoInput, true) ?? []) : $contenidoInput;

            case 'arrastrar_soltar':
                $itemsInput = $_POST['items'] ?? [];
                $targetsInput = $_POST['targets'] ?? [];
                return [
                    'items' => is_array($itemsInput) ? $itemsInput : (json_decode($itemsInput, true) ?? []),
                    'targets' => is_array($targetsInput) ? $targetsInput : (json_decode($targetsInput, true) ?? [])
                ];

            case 'ordenar_palabras':
                $itemsInput = $_POST['items'] ?? [];
                return [
                    'items' => is_array($itemsInput) ? $itemsInput : (json_decode($itemsInput, true) ?? [])
                ];

            case 'respuesta_corta':
                $contenidoInput = $_POST['contenido'] ?? '{}';
                $contenido = is_array($contenidoInput) ? $contenidoInput : (json_decode($contenidoInput, true) ?? []);

                if (empty($contenido) && isset($_POST['respuestas_correctas'])) {
                    $respuestasInput = $_POST['respuestas_correctas'] ?? [];
                    $contenido = [
                        'pregunta' => $_POST['pregunta'] ?? 'Escribe tu respuesta:',
                        'placeholder' => $_POST['placeholder'] ?? 'Escribe aqui tu respuesta...',
                        'respuestas_correctas' => is_array($respuestasInput) ? $respuestasInput : (json_decode($respuestasInput, true) ?? [])
                    ];
                }

                return $contenido;

            case 'completar_oracion':
                $contenidoInput = $_POST['contenido'] ?? '{}';
                $contenido = is_string($contenidoInput) ? (json_decode($contenidoInput, true) ?? []) : $contenidoInput;

                if (empty($contenido) || !isset($contenido['texto_completo'])) {
                    $texto = $_POST['oracion_completa'] ?? '';
                    preg_match_all('/\[([^\]]+)\]/', $texto, $matches);
                    $respuestas = $matches[1] ?? [];

                    return [
                        'texto_completo' => $texto,
                        'respuestas_correctas' => $respuestas,
                        'segmentos' => preg_split('/(\[[^\]]+\])/', $texto, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)
                    ];
                }

                return $contenido;

            default:
                return [];
        }
    }

    public function configurar($id)
    {
        [$actividad] = $this->obtenerActividadAutorizada($id);

        $_SESSION['actividad_temp'] = [
            'actividad_id' => $actividad->id,
            'leccion_id' => $actividad->leccion_id,
            'titulo' => $actividad->titulo,
            'descripcion' => $actividad->descripcion,
            'tipo_actividad' => $actividad->tipo_actividad,
            'orden' => $actividad->orden,
            'tiempo_limite_minutos' => $actividad->tiempo_limite_minutos,
            'puntos_maximos' => $actividad->puntos_maximos,
            'contenido' => $actividad->contenido
        ];

        $this->redirect('/profesor/actividades/config/' . $actividad->tipo_actividad . '/' . $actividad->leccion_id);
    }

    public function preview($id)
    {
        [$actividad] = $this->obtenerActividadAutorizada($id);
        $siguienteActividad = $this->actividadModel->obtenerSiguienteActividadEnLeccion($actividad->leccion_id, $actividad->id);

        $this->view('estudiante/actividades/index', [
            'actividad' => $actividad,
            'siguienteActividad' => $siguienteActividad
        ]);
    }

    public function index($leccion_id)
    {
        $leccion = $this->obtenerLeccionAutorizada($leccion_id);
        $actividades = $this->actividadModel->obtenerActividadesPorLeccion($leccion_id);
        [$puedeCrearActividad, $mensajeLimiteActividad] = $this->planModel->puedeCrearActividad(Auth::getUserId(), $leccion_id);
        $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());

        $this->view('profesor/actividades/index', [
            'leccion' => $leccion,
            'actividades' => $actividades,
            'puedeCrearActividad' => $puedeCrearActividad,
            'mensajeLimiteActividad' => $mensajeLimiteActividad,
            'planUso' => $planUso
        ]);
    }

    public function create($leccion_id)
    {
        $leccion = $this->obtenerLeccionAutorizada($leccion_id);
        $tiposActividad = $this->tiposActividad();
        [$puedeCrear, $mensajeLimite] = $this->planModel->puedeCrearActividad(Auth::getUserId(), $leccion_id);
        if (!$puedeCrear) {
            $this->flash('error', $mensajeLimite);
            $this->redirect('/profesor/lecciones/' . $leccion_id . '/actividades');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $tipoActividad = $_POST['tipo_actividad'] ?? '';
            if (isset($tiposActividad[$tipoActividad])) {
                $this->guardarActividadTemporal($leccion_id, $tipoActividad);
                $this->redirect('/profesor/actividades/config/' . $tipoActividad . '/' . $leccion_id);
            }

            $datos = [
                'leccion_id' => $leccion_id,
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'tipo_actividad' => $tipoActividad,
                'instrucciones' => $_POST['descripcion'],
                'contenido' => json_encode(['configuracion' => 'por_definir']),
                'orden' => $_POST['orden'],
                'tiempo_limite_minutos' => $_POST['tiempo_limite_minutos'],
                'puntos_maximos' => $_POST['puntos_maximos']
            ];

            if ($this->actividadModel->crearActividad($datos)) {
                $this->flash('success', 'Actividad creada exitosamente');
                $this->redirect('/profesor/lecciones/' . $leccion_id . '/actividades');
            }

            $error = 'Error al crear la actividad';
        }

        $this->view('profesor/actividades/create', [
            'leccion' => $leccion,
            'tipos_actividad' => $tiposActividad,
            'planUso' => $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId()),
            'error' => $error ?? null
        ]);
    }

    public function edit($id)
    {
        [$actividad, $leccion] = $this->obtenerActividadAutorizada($id);
        $tiposActividad = $this->tiposActividad();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $datos = [
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'tipo_actividad' => $_POST['tipo_actividad'],
                'instrucciones' => $_POST['descripcion'],
                'contenido' => $actividad->contenido,
                'orden' => $_POST['orden'],
                'tiempo_limite_minutos' => $_POST['tiempo_limite_minutos'],
                'puntos_maximos' => $_POST['puntos_maximos']
            ];

            if ($this->actividadModel->actualizarActividad($id, $datos)) {
                $this->flash('success', 'Actividad actualizada exitosamente');
                $this->redirect('/profesor/lecciones/' . $actividad->leccion_id . '/actividades');
            }

            $error = 'Error al actualizar la actividad';
        }

        $this->view('profesor/actividades/edit', [
            'actividad' => $actividad,
            'leccion' => $leccion,
            'tipos_actividad' => $tiposActividad,
            'error' => $error ?? null
        ]);
    }

    public function delete($id)
    {
        $this->requirePost();
        require_csrf();

        [$actividad] = $this->obtenerActividadAutorizada($id);

        if ($this->actividadModel->eliminarActividad($id)) {
            $this->flash('success', 'Actividad eliminada exitosamente');
        } else {
            $this->flash('error', 'Error al eliminar la actividad');
        }

        $this->redirect('/profesor/lecciones/' . $actividad->leccion_id . '/actividades');
    }

    public function config($tipo, $leccion_id)
    {
        if (!isset($_SESSION['actividad_temp'])) {
            $this->flash('error', 'No hay datos de actividad temporal');
            $this->redirect('/profesor/lecciones/' . $leccion_id . '/actividades/create');
        }

        $leccion = $this->obtenerLeccionAutorizada($leccion_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $contenido = $this->normalizarContenidoConfig($tipo);
            $temp = $_SESSION['actividad_temp'];

            $datosBase = [
                'titulo' => $_POST['titulo'] ?? ($temp['titulo'] ?? ''),
                'descripcion' => $_POST['descripcion'] ?? ($temp['descripcion'] ?? ''),
                'tipo_actividad' => $tipo,
                'instrucciones' => $_POST['descripcion'] ?? ($temp['descripcion'] ?? ''),
                'contenido' => json_encode($contenido),
                'orden' => $_POST['orden'] ?? ($temp['orden'] ?? 1),
                'tiempo_limite_minutos' => $_POST['tiempo_limite_minutos'] ?? ($temp['tiempo_limite_minutos'] ?? 5),
                'puntos_maximos' => $_POST['puntos_maximos'] ?? ($temp['puntos_maximos'] ?? 10)
            ];

            if (!empty($temp['actividad_id'])) {
                if ($this->actividadModel->actualizarActividad($temp['actividad_id'], $datosBase)) {
                    unset($_SESSION['actividad_temp']);
                    $this->flash('success', 'Actividad actualizada exitosamente');
                    $this->redirect('/profesor/lecciones/' . $leccion_id . '/actividades');
                }

                $error = 'Error al actualizar la actividad';
            } else {
                $datosCrear = array_merge($datosBase, ['leccion_id' => $leccion_id]);

                if ($this->actividadModel->crearActividad($datosCrear)) {
                    unset($_SESSION['actividad_temp']);
                    $this->flash('success', 'Actividad creada exitosamente');
                    $this->redirect('/profesor/lecciones/' . $leccion_id . '/actividades');
                }

                $error = 'Error al crear la actividad';
            }
        }

        $vista = 'profesor/actividades/config_' . $tipo;
        $datosVista = [
            'leccion' => $leccion,
            'error' => $error ?? null
        ];

        if ($tipo === 'opcion_multiple') {
            $datosVista['recursosImagen'] = $this->mediaModel->obtenerImagenesPorProfesor(Auth::getUserId(), Auth::getInstanciaId());
        }

        $this->view($vista, $datosVista);
    }
}
