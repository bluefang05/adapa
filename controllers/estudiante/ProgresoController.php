<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/Curso.php';

class ProgresoController extends Controller {
    private $cursoModel;

    public function __construct() {
        $this->requireRole(['estudiante', 'admin']);
        $this->cursoModel = new Curso();
    }

    public function index() {
        $resumenCursos = $this->cursoModel->obtenerResumenCursosPorEstudiante(Auth::getUserId());

        $this->view('estudiante/progreso/index', [
            'resumenCursos' => $resumenCursos,
        ]);
    }
}
