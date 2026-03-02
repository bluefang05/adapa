<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Teoria.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../core/Auth.php';

class TeoriaController extends Controller
{
    private $teoriaModel;
    private $leccionModel;
    private $cursoModel;

    public function __construct()
    {
        $this->requireRole('profesor');
        $this->teoriaModel = new Teoria();
        $this->leccionModel = new Leccion();
        $this->cursoModel = new Curso();
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

    private function obtenerTeoriaAutorizada($teoriaId)
    {
        $teoria = $this->teoriaModel->obtenerTeoriaPorId($teoriaId);
        if (!$teoria) {
            $this->flash('error', 'Teoria no encontrada');
            $this->redirect('/profesor/cursos');
        }

        $leccion = $this->obtenerLeccionAutorizada($teoria->leccion_id);

        return [$teoria, $leccion];
    }

    public function index($leccion_id)
    {
        $leccion = $this->obtenerLeccionAutorizada($leccion_id);
        $teorias = $this->teoriaModel->obtenerTeoriasPorLeccion($leccion_id);

        $this->view('profesor/teoria/index', [
            'leccion' => $leccion,
            'teorias' => $teorias
        ]);
    }

    public function create($leccion_id)
    {
        $leccion = $this->obtenerLeccionAutorizada($leccion_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $datos = [
                'leccion_id' => $leccion_id,
                'titulo' => $_POST['titulo'],
                'contenido' => $_POST['contenido'],
                'tipo_contenido' => $_POST['tipo_contenido'],
                'orden' => $_POST['orden'],
                'duracion_minutos' => $_POST['duracion_minutos']
            ];

            if ($this->teoriaModel->crearTeoria($datos)) {
                $this->flash('success', 'Teoria creada exitosamente');
                $this->redirect('/profesor/lecciones/' . $leccion_id . '/teoria');
            }

            $error = 'Error al crear la teoria';
        }

        $this->view('profesor/teoria/create', [
            'leccion' => $leccion,
            'error' => $error ?? null
        ]);
    }

    public function edit($id)
    {
        [$teoria, $leccion] = $this->obtenerTeoriaAutorizada($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $datos = [
                'titulo' => $_POST['titulo'],
                'contenido' => $_POST['contenido'],
                'tipo_contenido' => $_POST['tipo_contenido'],
                'orden' => $_POST['orden'],
                'duracion_minutos' => $_POST['duracion_minutos']
            ];

            if ($this->teoriaModel->actualizarTeoria($id, $datos)) {
                $this->flash('success', 'Teoria actualizada exitosamente');
                $this->redirect('/profesor/lecciones/' . $teoria->leccion_id . '/teoria');
            }

            $error = 'Error al actualizar la teoria';
        }

        $this->view('profesor/teoria/edit', [
            'teoria' => $teoria,
            'leccion' => $leccion,
            'error' => $error ?? null
        ]);
    }

    public function delete($id)
    {
        $this->requirePost();
        require_csrf();

        [$teoria] = $this->obtenerTeoriaAutorizada($id);

        if ($this->teoriaModel->eliminarTeoria($id)) {
            $this->flash('success', 'Teoria eliminada exitosamente');
        } else {
            $this->flash('error', 'Error al eliminar la teoria');
        }

        $this->redirect('/profesor/lecciones/' . $teoria->leccion_id . '/teoria');
    }
}
