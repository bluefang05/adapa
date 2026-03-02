<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Database.php';

class EstudiantesController extends Controller {
    public function __construct() {
        $this->requireRole(['profesor', 'admin']);
    }

    public function index() {
        $db = new Database();
        $where = 'c.creado_por = :owner_id';
        $ownerValue = Auth::getUserId();
        $pageTitle = 'Mis estudiantes';

        if (Auth::hasRole('admin')) {
            $where = 'c.instancia_id = :owner_id';
            $ownerValue = Auth::getInstanciaId();
            $pageTitle = 'Estudiantes inscritos';
        }

        $db->query("
            SELECT
                c.id AS curso_id,
                c.titulo AS curso_titulo,
                u.id AS estudiante_id,
                u.nombre,
                u.apellido,
                u.email,
                i.fecha_inscripcion,
                COUNT(DISTINCT t.id) AS total_teorias,
                COUNT(DISTINCT CASE WHEN pt.leido = 1 THEN t.id END) AS teorias_leidas,
                COUNT(DISTINCT a.id) AS total_actividades,
                COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN a.id END) AS actividades_respondidas
            FROM cursos c
            JOIN inscripciones i ON i.curso_id = c.id
            JOIN usuarios u ON u.id = i.estudiante_id
            LEFT JOIN lecciones l ON l.curso_id = c.id
            LEFT JOIN teoria t ON t.leccion_id = l.id
            LEFT JOIN progreso_teoria pt ON pt.teoria_id = t.id AND pt.estudiante_id = u.id
            LEFT JOIN actividades a ON a.leccion_id = l.id
            LEFT JOIN respuestas r ON r.actividad_id = a.id AND r.estudiante_id = u.id
            WHERE {$where}
            GROUP BY c.id, c.titulo, u.id, u.nombre, u.apellido, u.email, i.fecha_inscripcion
            ORDER BY c.titulo ASC, u.nombre ASC, u.apellido ASC
        ");
        $db->bind(':owner_id', $ownerValue);
        $estudiantes = $db->resultSet();

        foreach ($estudiantes as $estudiante) {
            $estudiante->total_items = (int) $estudiante->total_teorias + (int) $estudiante->total_actividades;
            $estudiante->completados = (int) $estudiante->teorias_leidas + (int) $estudiante->actividades_respondidas;
            $estudiante->porcentaje = $estudiante->total_items > 0
                ? (int) round(($estudiante->completados / $estudiante->total_items) * 100)
                : 0;
        }

        $this->view('profesor/estudiantes/index', [
            'pageTitle' => $pageTitle,
            'estudiantes' => $estudiantes,
        ]);
    }
}
