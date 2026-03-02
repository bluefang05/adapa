<?php

require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class CursoController extends Controller {
    private $cursoModel;

    public function __construct() {
        $this->requireRole('profesor');
        $this->cursoModel = new Curso();
    }

    public function index() {
        $profesor_id = Auth::getUserId();
        $cursos = $this->cursoModel->obtenerResumenCursosPorProfesor($profesor_id);
        
        require_once __DIR__ . '/../../views/profesor/cursos/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarCurso();
        } else {
            require_once __DIR__ . '/../../views/profesor/cursos/create.php';
        }
    }

    private function guardarCurso() {
        require_csrf();
        $codigoAcceso = trim($_POST['codigo_acceso'] ?? '');
        $requiereCodigo = isset($_POST['requiere_codigo']) ? 1 : 0;

        $datos = [
            'instancia_id' => Auth::getInstanciaId(),
            'creado_por' => Auth::getUserId(),
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'idioma' => $_POST['idioma'],
            'nivel_cefr' => $_POST['nivel_cefr'],
            'modalidad' => $_POST['modalidad'],
            'es_publico' => isset($_POST['es_publico']) ? 1 : 0,
            'requiere_codigo' => $requiereCodigo,
            'codigo_acceso' => $requiereCodigo ? ($codigoAcceso !== '' ? $codigoAcceso : $this->cursoModel->generarCodigoAcceso()) : null,
            'tipo_codigo' => $requiereCodigo ? ($_POST['tipo_codigo'] ?? 'unico_curso') : null,
            'max_estudiantes' => (int) ($_POST['max_estudiantes'] ?? 0),
            'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
            'fecha_fin' => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null
        ];

        if ($this->cursoModel->crearCurso($datos)) {
            $this->redirect('/profesor/cursos');
        } else {
            $error = "Error al crear el curso";
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
            $datos = [
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'],
                'idioma' => $_POST['idioma'],
                'nivel_cefr' => $_POST['nivel_cefr'],
                'modalidad' => $_POST['modalidad'],
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0,
                'requiere_codigo' => $requiereCodigo,
                'codigo_acceso' => $requiereCodigo ? $codigoAcceso : null,
                'tipo_codigo' => $requiereCodigo ? ($_POST['tipo_codigo'] ?? 'unico_curso') : null,
                'max_estudiantes' => (int) ($_POST['max_estudiantes'] ?? 0)
            ];

            if ($this->cursoModel->actualizarCurso($id, $datos)) {
                $this->redirect('/profesor/cursos');
            } else {
                $error = "Error al actualizar el curso";
                require_once __DIR__ . '/../../views/profesor/cursos/edit.php';
            }
        } else {
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
}
