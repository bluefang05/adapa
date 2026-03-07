<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Teoria.php';
require_once __DIR__ . '/../../models/Leccion.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/MediaRecurso.php';
require_once __DIR__ . '/../../core/Auth.php';

class TeoriaController extends Controller
{
    private $teoriaModel;
    private $leccionModel;
    private $cursoModel;
    private $mediaModel;

    public function __construct()
    {
        $this->requireRole('profesor');
        $this->teoriaModel = new Teoria();
        $this->leccionModel = new Leccion();
        $this->cursoModel = new Curso();
        $this->mediaModel = new MediaRecurso();
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

    private function recogerBloquesDesdeRequest()
    {
        $tipos = $_POST['bloque_tipo'] ?? [];
        $titulos = $_POST['bloque_titulo'] ?? [];
        $contenidos = $_POST['bloque_contenido'] ?? [];
        $idiomas = $_POST['bloque_idioma'] ?? [];
        $tts = $_POST['bloque_tts'] ?? [];

        $total = max(
            count(is_array($tipos) ? $tipos : []),
            count(is_array($titulos) ? $titulos : []),
            count(is_array($contenidos) ? $contenidos : [])
        );

        $bloques = [];
        for ($i = 0; $i < $total; $i++) {
            $bloques[] = [
                'tipo_bloque' => $tipos[$i] ?? 'explicacion',
                'titulo' => $titulos[$i] ?? '',
                'contenido' => $contenidos[$i] ?? '',
                'idioma_bloque' => $idiomas[$i] ?? '',
                'tts_habilitado' => isset($tts[$i]) ? 1 : 0,
                'media_id' => $_POST['bloque_media_id'][$i] ?? '',
            ];
        }

        return $bloques;
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
                'duracion_minutos' => $_POST['duracion_minutos'],
                'bloques' => $this->recogerBloquesDesdeRequest(),
            ];

            if ($this->teoriaModel->crearTeoria($datos)) {
                $this->flash('success', 'Teoria creada exitosamente');
                $this->redirect('/profesor/lecciones/' . $leccion_id . '/teoria');
            }

            $error = 'Error al crear la teoria';
        }

        $this->view('profesor/teoria/create', [
            'leccion' => $leccion,
            'recursos' => $this->mediaModel->obtenerRecursosPorProfesor(Auth::getUserId(), Auth::getInstanciaId()),
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
                'duracion_minutos' => $_POST['duracion_minutos'],
                'bloques' => $this->recogerBloquesDesdeRequest(),
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
            'recursos' => $this->mediaModel->obtenerRecursosPorProfesor(Auth::getUserId(), Auth::getInstanciaId()),
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

    public function duplicate($id)
    {
        $this->requirePost();
        require_csrf();

        [$teoria] = $this->obtenerTeoriaAutorizada($id);

        $nuevoId = $this->teoriaModel->duplicarTeoria($id);
        if ($nuevoId) {
            $this->flash('success', 'Teoria duplicada en borrador operativo.');
        } else {
            $this->flash('error', 'No se pudo duplicar la teoria.');
        }

        $this->redirect('/profesor/lecciones/' . $teoria->leccion_id . '/teoria');
    }

    public function moveUp($id)
    {
        $this->move($id, 'up');
    }

    public function moveDown($id)
    {
        $this->move($id, 'down');
    }

    private function move($id, $direction)
    {
        $this->requirePost();
        require_csrf();

        [$teoria] = $this->obtenerTeoriaAutorizada($id);
        $this->teoriaModel->moverTeoria($id, $direction);
        $this->redirect('/profesor/lecciones/' . $teoria->leccion_id . '/teoria');
    }
}
