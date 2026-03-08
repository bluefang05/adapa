<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/Actividad.php';
require_once __DIR__ . '/../../models/Inscripcion.php';
require_once __DIR__ . '/../../models/Leccion.php';

class EstudianteActividadController extends Controller
{
    private $actividadModel;
    private $inscripcionModel;
    private $leccionModel;

    public function __construct()
    {
        $this->requireRole('estudiante');
        $this->actividadModel = new Actividad();
        $this->inscripcionModel = new Inscripcion();
        $this->leccionModel = new Leccion();
    }

    public function index($actividad_id)
    {
        $actividad = $this->actividadModel->obtenerActividadPorId($actividad_id);
        if (!$actividad) {
            $this->flash('error', 'Actividad no encontrada');
            $this->redirect('/estudiante');
        }

        $leccion = $this->leccionModel->obtenerLeccionPorId($actividad->leccion_id);
        if (!$leccion) {
            $this->flash('error', 'La leccion asociada ya no existe');
            $this->redirect('/estudiante');
        }

        $estudiante_id = Auth::getUserId();
        if (!$this->inscripcionModel->verificarInscripcion($leccion->curso_id, $estudiante_id)) {
            $this->flash('error', 'No estas inscrito en este curso');
            $this->redirect('/estudiante');
        }

        // La experiencia canonica del estudiante vive en /estudiante/actividades/{id}.
        $this->redirect('/estudiante/actividades/' . $actividad_id);
    }

    public function guardarRespuesta()
    {
        $this->requirePost();
        require_csrf();

        $data = json_decode(file_get_contents('php://input'), true);
        $actividad_id = $data['actividad_id'] ?? null;
        $respuesta = $data['respuesta'] ?? '';
        $es_correcta = $data['es_correcta'] ?? false;
        $tiempo_respuesta = $data['tiempo_respuesta'] ?? 0;

        if (!$actividad_id) {
            $this->json(['success' => false, 'error' => 'ID de actividad requerido'], 400);
        }

        $actividad = $this->actividadModel->obtenerActividadPorId($actividad_id);
        if (!$actividad) {
            $this->json(['success' => false, 'error' => 'Actividad no encontrada'], 404);
        }

        $leccion = $this->leccionModel->obtenerLeccionPorId($actividad->leccion_id);
        if (!$leccion) {
            $this->json(['success' => false, 'error' => 'Leccion no encontrada'], 404);
        }

        $estudiante_id = Auth::getUserId();
        if (!$this->inscripcionModel->verificarInscripcion($leccion->curso_id, $estudiante_id)) {
            $this->json(['success' => false, 'error' => 'No estas inscrito en este curso'], 403);
        }

        $resultado = $this->actividadModel->guardarRespuestaEstudiante([
            'estudiante_id' => $estudiante_id,
            'actividad_id' => $actividad_id,
            'respuesta' => $respuesta,
            'es_correcta' => $es_correcta,
            'tiempo_respuesta' => $tiempo_respuesta
        ]);

        if ($resultado) {
            $this->leccionModel->sincronizarProgresoEstudiante($actividad->leccion_id, $estudiante_id);
            $resumenLeccion = $this->leccionModel->obtenerResumenProgreso($actividad->leccion_id, $estudiante_id);
            $message = $es_correcta
                ? 'Respuesta guardada. Vas bien en esta practica.'
                : 'Respuesta guardada. Revisa el feedback y vuelve a intentarlo si hace falta.';

            $this->json([
                'success' => true,
                'es_correcta' => $es_correcta,
                'message' => $message,
                'lesson_progress' => (int) ($resumenLeccion->porcentaje ?? 0),
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Error al guardar respuesta'], 500);
        }
    }
}
