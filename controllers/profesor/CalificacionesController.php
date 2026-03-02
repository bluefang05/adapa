<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Respuesta.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Actividad.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../core/Auth.php';

class CalificacionesController extends Controller {
    private $respuestaModel;
    private $cursoModel;
    private $actividadModel;
    private $leccionModel;

    public function __construct() {
        $this->requireRole('profesor');
        $this->respuestaModel = new Respuesta();
        $this->cursoModel = new Curso();
        $this->actividadModel = new Actividad();
        $this->leccionModel = new Leccion();
    }

    public function index() {
        $profesorId = Auth::getUserId();
        $cursos = $this->cursoModel->obtenerCursosPorProfesor($profesorId);

        foreach ($cursos as $curso) {
            $curso->pendientes = $this->respuestaModel->contarRespuestasPendientesPorCurso($curso->id);
        }

        $this->view('profesor/calificaciones/index', ['cursos' => $cursos]);
    }

    public function curso($curso_id) {
        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        if (!$curso) {
            $this->flash('error', 'Curso no encontrado');
            $this->redirect('/profesor/calificaciones');
        }

        if ((int) $curso->creado_por !== (int) Auth::getUserId()) {
            $this->flash('error', 'No tienes permiso para ver las calificaciones de este curso');
            $this->redirect('/profesor/calificaciones');
        }

        $respuestas = $this->respuestaModel->obtenerRespuestasPorCurso($curso_id);

        $this->view('profesor/calificaciones/curso', [
            'curso' => $curso,
            'respuestas' => $respuestas
        ]);
    }

    public function revisar($respuesta_id) {
        $respuesta = $this->respuestaModel->obtenerRespuestaPorId($respuesta_id);
        if (!$respuesta) {
            $this->flash('error', 'Respuesta no encontrada');
            $this->redirect('/profesor/calificaciones');
        }

        $actividad = $this->actividadModel->obtenerActividadPorId($respuesta->actividad_id);
        if (!$actividad) {
            $this->flash('error', 'La actividad asociada ya no existe');
            $this->redirect('/profesor/calificaciones');
        }

        $leccion = $this->leccionModel->obtenerLeccionPorId($actividad->leccion_id);
        if (!$leccion) {
            $this->flash('error', 'La leccion asociada ya no existe');
            $this->redirect('/profesor/calificaciones');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);

        if (!$curso || (int) $curso->creado_por !== (int) Auth::getUserId()) {
            $this->flash('error', 'No tienes permiso para revisar esta respuesta');
            $this->redirect('/profesor/calificaciones');
        }

        $actividad->curso_id = $leccion->curso_id;

        $this->view('profesor/calificaciones/revisar', [
            'respuesta' => $respuesta,
            'actividad' => $actividad
        ]);
    }

    public function calificar($respuesta_id) {
        $this->requirePost();
        require_csrf();

        $respuesta = $this->respuestaModel->obtenerRespuestaPorId($respuesta_id);
        if (!$respuesta) {
            $this->flash('error', 'Respuesta no encontrada');
            $this->redirect('/profesor/calificaciones');
        }

        $actividad = $this->actividadModel->obtenerActividadPorId($respuesta->actividad_id);
        if (!$actividad) {
            $this->flash('error', 'La actividad asociada ya no existe');
            $this->redirect('/profesor/calificaciones');
        }

        $leccion = $this->leccionModel->obtenerLeccionPorId($actividad->leccion_id);
        if (!$leccion) {
            $this->flash('error', 'La leccion asociada ya no existe');
            $this->redirect('/profesor/calificaciones');
        }

        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);

        if (!$curso || (int) $curso->creado_por !== (int) Auth::getUserId()) {
            $this->flash('error', 'No tienes permiso para calificar esta respuesta');
            $this->redirect('/profesor/calificaciones');
        }

        $puntuacion = $_POST['puntuacion'] ?? 0;
        $comentarios = $_POST['comentarios'] ?? '';

        if ($this->respuestaModel->actualizarCalificacion($respuesta_id, $puntuacion, $comentarios)) {
            $this->flash('mensaje', 'Calificacion actualizada correctamente.');
        } else {
            $this->flash('error', 'Error al actualizar la calificacion.');
        }

        if (isset($_POST['curso_id'])) {
            $this->redirect('/profesor/calificaciones/curso/' . $_POST['curso_id']);
        }

        $this->redirect('/profesor/calificaciones');
    }
}
