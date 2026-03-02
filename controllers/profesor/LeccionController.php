<?php

require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class LeccionController extends Controller {
    private $leccionModel;
    private $cursoModel;

    public function __construct() {
        $this->requireRole('profesor');
        $this->leccionModel = new Leccion();
        $this->cursoModel = new Curso();
    }

    public function index($curso_id) {
        // Verificar que el curso pertenezca al profesor
        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $lecciones = $this->leccionModel->obtenerLeccionesConContenido($curso_id);
        
        require_once __DIR__ . '/../../views/profesor/lecciones/index.php';
    }

    public function create($curso_id) {
        // Verificar que el curso pertenezca al profesor
        $curso = $this->cursoModel->obtenerCursoPorId($curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarLeccion($curso_id);
        } else {
            $siguiente_orden = $this->leccionModel->obtenerSiguienteOrden($curso_id);
            require_once __DIR__ . '/../../views/profesor/lecciones/create.php';
        }
    }

    private function guardarLeccion($curso_id) {
        require_csrf();
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
        } else {
            $error = "Error al crear la lección";
            require_once __DIR__ . '/../../views/profesor/lecciones/create.php';
        }
    }

    public function edit($id) {
        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        // Verificar que el curso pertenezca al profesor
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
            } else {
                $error = "Error al actualizar la lección";
                require_once __DIR__ . '/../../views/profesor/lecciones/edit.php';
            }
        } else {
            require_once __DIR__ . '/../../views/profesor/lecciones/edit.php';
        }
    }

    public function delete($id) {
        $this->requirePost();
        require_csrf();

        $leccion = $this->leccionModel->obtenerLeccionPorId($id);
        if (!$leccion) {
            $this->redirect('/profesor/cursos');
        }

        // Verificar que el curso pertenezca al profesor
        $curso = $this->cursoModel->obtenerCursoPorId($leccion->curso_id);
        if (!$curso || $curso->creado_por != Auth::getUserId()) {
            $this->redirect('/profesor/cursos');
        }

        $this->leccionModel->eliminarLeccion($id);
        $this->redirect('/profesor/cursos/' . $leccion->curso_id . '/lecciones');
    }
}
