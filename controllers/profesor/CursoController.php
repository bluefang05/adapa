<?php

require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/MediaRecurso.php';
require_once __DIR__ . '/../../models/ProfesorPlan.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Teoria.php';
require_once __DIR__ . '/../../models/Actividad.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class CursoController extends Controller {
    private $cursoModel;
    private $mediaModel;
    private $planModel;
    private $leccionModel;
    private $teoriaModel;
    private $actividadModel;

    public function __construct() {
        $this->requireRole('profesor');
        $this->cursoModel = new Curso();
        $this->mediaModel = new MediaRecurso();
        $this->planModel = new ProfesorPlan();
        $this->leccionModel = new Leccion();
        $this->teoriaModel = new Teoria();
        $this->actividadModel = new Actividad();
    }

    public function index() {
        $profesor_id = Auth::getUserId();
        $cursos = $this->cursoModel->obtenerResumenCursosPorProfesor($profesor_id);
        $planUso = $this->planModel->obtenerResumenUsoProfesor($profesor_id);
        
        require_once __DIR__ . '/../../views/profesor/cursos/index.php';
    }

    public function create() {
        [$puedeCrear, $mensajeLimite] = $this->planModel->puedeCrearCurso(Auth::getUserId());
        if (!$puedeCrear) {
            $this->flash('error', $mensajeLimite);
            $this->redirect('/profesor/cursos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarCurso();
        } else {
            $recursosImagen = $this->mediaModel->obtenerImagenesPorProfesor(Auth::getUserId(), Auth::getInstanciaId());
            $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());
            require_once __DIR__ . '/../../views/profesor/cursos/create.php';
        }
    }

    private function guardarCurso() {
        require_csrf();
        $codigoAcceso = trim($_POST['codigo_acceso'] ?? '');
        $requiereCodigo = isset($_POST['requiere_codigo']) ? 1 : 0;
        [$nivelPrincipal, $nivelDesde, $nivelHasta] = $this->normalizarRangoCefr(
            $_POST['nivel_cefr'] ?? '',
            $_POST['nivel_cefr_desde'] ?? '',
            $_POST['nivel_cefr_hasta'] ?? ''
        );

        $datos = [
            'instancia_id' => Auth::getInstanciaId(),
            'creado_por' => Auth::getUserId(),
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'idioma' => $_POST['idioma_objetivo'],
            'idioma_objetivo' => $_POST['idioma_objetivo'],
            'idioma_base' => $_POST['idioma_base'],
            'idioma_ensenanza' => $_POST['idioma_base'],
            'portada_media_id' => $this->resolverPortadaCurso($_POST['portada_media_id'] ?? null),
            'nivel_cefr' => $nivelPrincipal,
            'nivel_cefr_desde' => $nivelDesde,
            'nivel_cefr_hasta' => $nivelHasta,
            'modalidad' => $_POST['modalidad'],
            'es_publico' => isset($_POST['es_publico']) ? 1 : 0,
            'requiere_codigo' => $requiereCodigo,
            'codigo_acceso' => $requiereCodigo ? ($codigoAcceso !== '' ? $codigoAcceso : $this->cursoModel->generarCodigoAcceso()) : null,
            'tipo_codigo' => $requiereCodigo ? ($_POST['tipo_codigo'] ?? 'unico_curso') : null,
            'max_estudiantes' => (int) ($_POST['max_estudiantes'] ?? 0),
            'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
            'fecha_fin' => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null
        ];

        [$datos, $ajustesPlan] = $this->planModel->normalizarDatosCursoParaPlan(Auth::getUserId(), $datos);
        if (!empty($datos['requiere_codigo']) && empty($datos['codigo_acceso'])) {
            $datos['codigo_acceso'] = $this->cursoModel->generarCodigoAcceso();
        }

        if ($this->cursoModel->crearCurso($datos)) {
            if ($ajustesPlan) {
                $this->flash('success', 'Curso creado. En plan gratuito se aplicaron estos ajustes: ' . implode(', ', $ajustesPlan) . '.');
            }
            $this->redirect('/profesor/cursos');
        } else {
            $error = "Error al crear el curso";
            $recursosImagen = $this->mediaModel->obtenerImagenesPorProfesor(Auth::getUserId(), Auth::getInstanciaId());
            $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());
            require_once __DIR__ . '/../../views/profesor/cursos/create.php';
        }
    }

    public function edit($id) {
        $curso = $this->cursoModel->obtenerCursoPorId($id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $codigoAcceso = trim($_POST['codigo_acceso'] ?? '');
            $requiereCodigo = isset($_POST['requiere_codigo']) ? 1 : 0;
            [$nivelPrincipal, $nivelDesde, $nivelHasta] = $this->normalizarRangoCefr(
                $_POST['nivel_cefr'] ?? '',
                $_POST['nivel_cefr_desde'] ?? '',
                $_POST['nivel_cefr_hasta'] ?? ''
            );
            $datos = [
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'idioma' => $_POST['idioma_objetivo'],
                'idioma_objetivo' => $_POST['idioma_objetivo'],
                'idioma_base' => $_POST['idioma_base'],
                'idioma_ensenanza' => $_POST['idioma_base'],
                'portada_media_id' => $this->resolverPortadaCurso($_POST['portada_media_id'] ?? null),
                'nivel_cefr' => $nivelPrincipal,
                'nivel_cefr_desde' => $nivelDesde,
                'nivel_cefr_hasta' => $nivelHasta,
                'modalidad' => $_POST['modalidad'],
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0,
                'requiere_codigo' => $requiereCodigo,
                'codigo_acceso' => $requiereCodigo ? $codigoAcceso : null,
                'tipo_codigo' => $requiereCodigo ? ($_POST['tipo_codigo'] ?? 'unico_curso') : null,
                'max_estudiantes' => (int) ($_POST['max_estudiantes'] ?? 0)
            ];

            [$datos, $ajustesPlan] = $this->planModel->normalizarDatosCursoParaPlan(Auth::getUserId(), $datos);
            if (!empty($datos['requiere_codigo']) && empty($datos['codigo_acceso'])) {
                $datos['codigo_acceso'] = $this->cursoModel->generarCodigoAcceso();
            }

            if ($this->cursoModel->actualizarCurso($id, $datos)) {
                if ($ajustesPlan) {
                    $this->flash('success', 'Curso actualizado. En plan gratuito se mantuvieron estos limites: ' . implode(', ', $ajustesPlan) . '.');
                }
                $this->redirect('/profesor/cursos');
            } else {
                $error = "Error al actualizar el curso";
                $recursosImagen = $this->mediaModel->obtenerImagenesPorProfesor(Auth::getUserId(), Auth::getInstanciaId());
                $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());
                [$coursePublishChecklist, $coursePublishSummary, $coursePublishHint] = $this->buildCoursePublishData($curso);
                require_once __DIR__ . '/../../views/profesor/cursos/edit.php';
            }
        } else {
            $recursosImagen = $this->mediaModel->obtenerImagenesPorProfesor(Auth::getUserId(), Auth::getInstanciaId());
            $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());
            [$coursePublishChecklist, $coursePublishSummary, $coursePublishHint] = $this->buildCoursePublishData($curso);
            require_once __DIR__ . '/../../views/profesor/cursos/edit.php';
        }
    }

    public function delete($id) {
        $this->requirePost();
        require_csrf();

        $curso = $this->cursoModel->obtenerCursoPorId($id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $this->cursoModel->eliminarCurso($id);
        $this->redirect('/profesor/cursos');
    }

    public function duplicate($id) {
        $this->requirePost();
        require_csrf();

        $curso = $this->cursoModel->obtenerCursoPorId($id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        [$puedeCrear, $mensajeLimite] = $this->planModel->puedeCrearCurso(Auth::getUserId());
        if (!$puedeCrear) {
            $this->flash('error', $mensajeLimite);
            $this->redirect('/profesor/cursos');
        }

        $nuevoCursoId = $this->cursoModel->duplicarCurso($id, Auth::getInstanciaId(), Auth::getUserId());
        if (!$nuevoCursoId) {
            $this->flash('error', 'No se pudo duplicar el curso.');
            $this->redirect('/profesor/cursos');
        }

        foreach ($this->leccionModel->obtenerLeccionesPorCurso($id) as $leccion) {
            $nuevaLeccionId = $this->leccionModel->duplicarLeccion($leccion->id);
            if (!$nuevaLeccionId) {
                continue;
            }

            // Reasignar la leccion duplicada al nuevo curso.
            $this->leccionModel->actualizarLeccion($nuevaLeccionId, [
                'titulo' => str_replace(' (copia)', '', $leccion->titulo),
                'descripcion' => $leccion->descripcion,
                'orden' => $leccion->orden,
                'duracion_minutos' => $leccion->duracion_minutos,
                'es_obligatoria' => $leccion->es_obligatoria,
                'estado' => 'borrador',
            ]);
            $this->leccionModel->reasignarCurso($nuevaLeccionId, $nuevoCursoId);

            foreach ($this->teoriaModel->obtenerTeoriasPorLeccion($leccion->id) as $teoria) {
                $this->teoriaModel->duplicarTeoria($teoria->id, $nuevaLeccionId, false);
            }

            foreach ($this->actividadModel->obtenerActividadesPorLeccion($leccion->id) as $actividad) {
                $this->actividadModel->duplicarActividad($actividad->id, $nuevaLeccionId, false);
            }
        }

        $this->flash('success', 'Curso duplicado con su estructura completa.');
        $this->redirect('/profesor/cursos');
    }

    private function resolverPortadaCurso($mediaId) {
        $mediaId = (int) $mediaId;
        if ($mediaId <= 0) {
            return null;
        }

        $recurso = $this->mediaModel->obtenerRecursoPorId($mediaId, Auth::getUserId(), Auth::getInstanciaId());
        if (!$recurso || $recurso->tipo_media !== 'imagen') {
            return null;
        }

        return $mediaId;
    }

    private function normalizarRangoCefr($nivelPrincipal, $nivelDesde, $nivelHasta) {
        $orden = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

        $nivelPrincipal = in_array($nivelPrincipal, $orden, true) ? $nivelPrincipal : 'A1';
        $nivelDesde = in_array($nivelDesde, $orden, true) ? $nivelDesde : $nivelPrincipal;
        $nivelHasta = in_array($nivelHasta, $orden, true) ? $nivelHasta : $nivelDesde;

        if (array_search($nivelDesde, $orden, true) > array_search($nivelHasta, $orden, true)) {
            $nivelHasta = $nivelDesde;
        }

        return [$nivelPrincipal, $nivelDesde, $nivelHasta];
    }

    private function buildCoursePublishData($curso) {
        $lecciones = $this->leccionModel->obtenerLeccionesPorCurso($curso->id);
        $lessonCount = count($lecciones);
        $theoryCount = 0;
        $activityCount = 0;
        $publishedLessonCount = 0;

        foreach ($lecciones as $leccion) {
            $lessonTheories = $this->teoriaModel->obtenerTeoriasPorLeccion($leccion->id);
            $lessonActivities = $this->actividadModel->obtenerActividadesPorLeccion($leccion->id);
            $theoryCount += count($lessonTheories);
            $activityCount += count($lessonActivities);
            if (($leccion->estado ?? '') === 'publicada') {
                $publishedLessonCount++;
            }
        }

        $checklist = [
            [
                'label' => 'Identidad clara',
                'ok' => trim((string) ($curso->titulo ?? '')) !== '' && trim((string) ($curso->descripcion ?? '')) !== '',
                'hint' => 'Titulo y descripcion deben explicar idioma, alcance y valor real.',
            ],
            [
                'label' => 'Portada lista',
                'ok' => !empty($curso->portada_media_id),
                'hint' => 'Una buena portada mejora mucho catalogo, panel y percepcion premium.',
            ],
            [
                'label' => 'Ruta creada',
                'ok' => $lessonCount > 0,
                'hint' => $lessonCount > 0 ? "Ya tienes {$lessonCount} leccion(es) montadas." : 'Crea al menos una leccion antes de abrir el curso.',
            ],
            [
                'label' => 'Teoria suficiente',
                'ok' => $theoryCount > 0,
                'hint' => $theoryCount > 0 ? "El curso ya suma {$theoryCount} pieza(s) de teoria." : 'Aun no hay teoria visible para el alumno.',
            ],
            [
                'label' => 'Practica suficiente',
                'ok' => $activityCount > 0,
                'hint' => $activityCount > 0 ? "El curso ya suma {$activityCount} actividad(es)." : 'Falta convertir el contenido en practica.',
            ],
            [
                'label' => 'Lecciones visibles',
                'ok' => $publishedLessonCount > 0,
                'hint' => $publishedLessonCount > 0 ? "{$publishedLessonCount} leccion(es) ya estan publicadas." : 'Ninguna leccion esta marcada como publicada todavia.',
            ],
        ];

        $okCount = count(array_filter($checklist, static fn ($item) => !empty($item['ok'])));
        $summary = [
            'ok' => $okCount,
            'total' => count($checklist),
            'percentage' => (int) round(($okCount / max(1, count($checklist))) * 100),
            'lessons' => $lessonCount,
            'theories' => $theoryCount,
            'activities' => $activityCount,
            'published_lessons' => $publishedLessonCount,
        ];

        if ($summary['percentage'] >= 100) {
            $hint = 'Listo para abrirlo con confianza: ya tiene estructura, teoria, practica y visibilidad basica.';
        } elseif ($lessonCount === 0) {
            $hint = 'Todavia esta en fase de configuracion: crea la primera leccion antes de pensar en hacerlo visible.';
        } elseif ($activityCount === 0) {
            $hint = 'La estructura existe, pero aun falta practica para que el curso no se sienta incompleto.';
        } else {
            $hint = 'Va bien encaminado. Pulsa las piezas que faltan antes de abrirlo a mas estudiantes.';
        }

        return [$checklist, $summary, $hint];
    }
}
