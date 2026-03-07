<?php

require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/ProfesorPlan.php';
require_once __DIR__ . '/../../models/Teoria.php';
require_once __DIR__ . '/../../models/Actividad.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class LeccionController extends Controller {
    private $leccionModel;
    private $cursoModel;
    private $planModel;
    private $teoriaModel;
    private $actividadModel;

    public function __construct() {
        $this->requireRole('profesor');
        $this->leccionModel = new Leccion();
        $this->cursoModel = new Curso();
        $this->planModel = new ProfesorPlan();
        $this->teoriaModel = new Teoria();
        $this->actividadModel = new Actividad();
    }

    public function index($curso_id) {
        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $lecciones = $this->leccionModel->obtenerLeccionesConContenido($curso_id);
        [$puedeCrearLeccion, $mensajeLimiteLeccion] = $this->planModel->puedeCrearLeccion(Auth::getUserId(), $curso_id);
        $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());

        require_once __DIR__ . '/../../views/profesor/lecciones/index.php';
    }

    public function create($curso_id) {
        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarLeccion($curso_id);
            return;
        }

        [$puedeCrear, $mensajeLimite] = $this->planModel->puedeCrearLeccion(Auth::getUserId(), $curso_id);
        if (!$puedeCrear) {
            $this->flash('error', $mensajeLimite);
            $this->redirect('/profesor/cursos/' . $curso_id . '/lecciones');
        }

        $siguiente_orden = $this->leccionModel->obtenerSiguienteOrden($curso_id);
        $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());
        require_once __DIR__ . '/../../views/profesor/lecciones/create.php';
    }

    private function guardarLeccion($curso_id) {
        require_csrf();

        [$puedeCrear, $mensajeLimite] = $this->planModel->puedeCrearLeccion(Auth::getUserId(), $curso_id);
        if (!$puedeCrear) {
            $this->flash('error', $mensajeLimite);
            $this->redirect('/profesor/cursos/' . $curso_id . '/lecciones');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        $datos = [
            'curso_id' => $curso_id,
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'orden' => $_POST['orden'],
            'duracion_minutos' => $_POST['duracion_minutos'],
            'es_obligatoria' => isset($_POST['es_obligatoria']) ? 1 : 0,
            'estado' => $_POST['estado']
        ];

        if ($this->leccionModel->crearLeccion($datos)) {
            $this->redirect('/profesor/cursos/' . $curso_id . '/lecciones');
        }

        $error = 'Error al crear la leccion';
        $siguiente_orden = $this->leccionModel->obtenerSiguienteOrden($curso_id);
        $planUso = $this->planModel->obtenerResumenUsoProfesor(Auth::getUserId());
        require_once __DIR__ . '/../../views/profesor/lecciones/create.php';
    }

    public function edit($id) {
        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $datos = [
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'orden' => $_POST['orden'],
                'duracion_minutos' => $_POST['duracion_minutos'],
                'es_obligatoria' => isset($_POST['es_obligatoria']) ? 1 : 0,
                'estado' => $_POST['estado']
            ];

            if ($this->leccionModel->actualizarLeccion($id, $datos)) {
                $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
            }

            $error = 'Error al actualizar la leccion';
            [$lessonPublishChecklist, $lessonPublishSummary, $lessonPublishHint] = $this->buildLessonPublishData($leccion);
            require_once __DIR__ . '/../../views/profesor/lecciones/edit.php';
            return;
        }

        [$lessonPublishChecklist, $lessonPublishSummary, $lessonPublishHint] = $this->buildLessonPublishData($leccion);
        require_once __DIR__ . '/../../views/profesor/lecciones/edit.php';
    }

    public function preview($id) {
        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $teorias = $this->teoriaModel->obtenerTeoriasPorLeccion($id);
        $actividades = $this->actividadModel->obtenerActividadesPorLeccion($id);

        $previewChecklist = [
            [
                'label' => 'Tiene teoria base',
                'ok' => !empty($teorias),
                'hint' => !empty($teorias) ? 'La leccion ya ofrece contexto antes de practicar.' : 'Conviene crear al menos una pieza teorica.',
            ],
            [
                'label' => 'Tiene practica',
                'ok' => !empty($actividades),
                'hint' => !empty($actividades) ? 'El alumno ya tiene donde comprobar comprension.' : 'Falta convertir la teoria en practica.',
            ],
            [
                'label' => 'Descripcion visible',
                'ok' => trim((string) ($leccion->descripcion ?? '')) !== '',
                'hint' => trim((string) ($leccion->descripcion ?? '')) !== '' ? 'La leccion explica que se espera del alumno.' : 'Falta una descripcion clara y util.',
            ],
            [
                'label' => 'Estado publicable',
                'ok' => ($leccion->estado ?? '') === 'publicada',
                'hint' => ($leccion->estado ?? '') === 'publicada' ? 'La leccion ya esta marcada como visible.' : 'Sigue en borrador o archivada.',
            ],
        ];

        require_once __DIR__ . '/../../views/profesor/lecciones/preview.php';
    }

    public function delete($id) {
        $this->requirePost();
        require_csrf();

        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $this->leccionModel->eliminarLeccion($id);
        $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
    }

    public function duplicate($id) {
        $this->requirePost();
        require_csrf();

        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        [$puedeCrearLeccion, $mensajeLimiteLeccion] = $this->planModel->puedeCrearLeccion(Auth::getUserId(), $leccion->curso_id);
        if (!$puedeCrearLeccion) {
            $this->flash('error', $mensajeLimiteLeccion);
            $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
        }

        $nuevaLeccionId = $this->leccionModel->duplicarLeccion($id);
        if (!$nuevaLeccionId) {
            $this->flash('error', 'No se pudo duplicar la leccion.');
            $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
        }

        foreach ($this->teoriaModel->obtenerTeoriasPorLeccion($id) as $teoria) {
            $this->teoriaModel->duplicarTeoria($teoria->id, $nuevaLeccionId, false);
        }

        foreach ($this->actividadModel->obtenerActividadesPorLeccion($id) as $actividad) {
            $this->actividadModel->duplicarActividad($actividad->id, $nuevaLeccionId, false);
        }

        $this->flash('success', 'Leccion duplicada con teoria y actividades.');
        $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
    }

    public function moveUp($id) {
        $this->move($id, 'up');
    }

    public function moveDown($id) {
        $this->move($id, 'down');
    }

    private function move($id, $direction) {
        $this->requirePost();
        require_csrf();

        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $this->leccionModel->moverLeccion($id, $direction);
        $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
    }

    private function buildLessonPublishData($leccion) {
        $teorias = $this->teoriaModel->obtenerTeoriasPorLeccion($leccion->id);
        $actividades = $this->actividadModel->obtenerActividadesPorLeccion($leccion->id);

        $checklist = [
            [
                'label' => 'Objetivo claro',
                'ok' => trim((string) ($leccion->titulo ?? '')) !== '' && trim((string) ($leccion->descripcion ?? '')) !== '',
                'hint' => 'Titulo y descripcion deben explicar que resuelve esta leccion.',
            ],
            [
                'label' => 'Teoria base',
                'ok' => count($teorias) > 0,
                'hint' => count($teorias) > 0 ? 'La leccion ya tiene contexto previo a la practica.' : 'Falta al menos una pieza de teoria.',
            ],
            [
                'label' => 'Practica lista',
                'ok' => count($actividades) > 0,
                'hint' => count($actividades) > 0 ? 'El alumno ya puede practicar despues de leer.' : 'Falta al menos una actividad util.',
            ],
            [
                'label' => 'Duracion definida',
                'ok' => (int) ($leccion->duracion_minutos ?? 0) > 0,
                'hint' => (int) ($leccion->duracion_minutos ?? 0) > 0 ? 'Ayuda a que el alumno entienda la carga real.' : 'Pon una duracion estimada para mejorar expectativas.',
            ],
            [
                'label' => 'Estado visible',
                'ok' => ($leccion->estado ?? '') === 'publicada',
                'hint' => ($leccion->estado ?? '') === 'publicada' ? 'La leccion ya esta marcada como publicada.' : 'Sigue en borrador o archivada.',
            ],
        ];

        $okCount = count(array_filter($checklist, static fn ($item) => !empty($item['ok'])));
        $summary = [
            'ok' => $okCount,
            'total' => count($checklist),
            'percentage' => (int) round(($okCount / max(1, count($checklist))) * 100),
            'theories' => count($teorias),
            'activities' => count($actividades),
        ];

        if ($summary['percentage'] >= 100) {
            $hint = 'Leccion lista para alumnos: ya tiene contexto, practica y estado correcto.';
        } elseif (count($teorias) === 0) {
            $hint = 'Empieza por una teoria corta y clara antes de publicar.';
        } elseif (count($actividades) === 0) {
            $hint = 'Ya hay teoria. Ahora falta cerrar la leccion con practica.';
        } else {
            $hint = 'La base existe. Ajusta los detalles que faltan para que la leccion se sienta terminada.';
        }

        return [$checklist, $summary, $hint];
    }
}
