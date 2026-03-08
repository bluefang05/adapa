<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';

class CalificacionesController extends Controller {
    public function __construct() {
        $this->requireRole(['estudiante', 'admin']);
    }

    public function index() {
        $db = new Database();
        $db->query("
            SELECT
                c.id AS curso_id,
                c.titulo AS curso_titulo,
                l.titulo AS leccion_titulo,
                a.titulo AS actividad_titulo,
                a.tipo_actividad,
                a.puntos_maximos,
                r.puntuacion,
                r.fecha_respuesta
            FROM respuestas r
            JOIN actividades a ON a.id = r.actividad_id
            JOIN lecciones l ON l.id = a.leccion_id
            JOIN cursos c ON c.id = l.curso_id
            WHERE r.estudiante_id = :estudiante_id
              AND COALESCE(NULLIF(c.estado_editorial, ''), 'borrador') = 'publicado'
              AND COALESCE(NULLIF(l.estado_editorial, ''), 'borrador') = 'publicado'
            ORDER BY r.fecha_respuesta DESC
        ");
        $db->bind(':estudiante_id', Auth::getUserId());
        $calificaciones = $db->resultSet();

        $this->view('estudiante/calificaciones/index', [
            'calificaciones' => $calificaciones,
            'calificacionesScopeHint' => 'Solo se muestran respuestas de cursos y lecciones que siguen publicadas para estudiantes.',
        ]);
    }
}
