<?php

require_once __DIR__ . '/../core/Database.php';

class Curso {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerCursosPorProfesor($profesor_id) {
        $this->db->query("SELECT * FROM cursos WHERE creado_por = :profesor_id ORDER BY fecha_creacion DESC");
        $this->db->bind(':profesor_id', $profesor_id);
        return $this->db->resultSet();
    }

    public function obtenerResumenCursosPorProfesor($profesor_id) {
        $this->db->query("
            SELECT
                c.*,
                COUNT(DISTINCT l.id) AS total_lecciones,
                COUNT(DISTINCT a.id) AS total_actividades,
                COUNT(DISTINCT i.estudiante_id) AS total_estudiantes
            FROM cursos c
            LEFT JOIN lecciones l ON l.curso_id = c.id
            LEFT JOIN actividades a ON a.leccion_id = l.id
            LEFT JOIN inscripciones i ON i.curso_id = c.id
            WHERE c.creado_por = :profesor_id
            GROUP BY c.id
            ORDER BY c.fecha_creacion DESC
        ");
        $this->db->bind(':profesor_id', $profesor_id);
        return $this->db->resultSet();
    }

    public function obtenerCursoPorId($id) {
        $this->db->query("SELECT * FROM cursos WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function crearCurso($datos) {
        $codigoAcceso = !empty($datos['codigo_acceso']) ? $datos['codigo_acceso'] : null;
        $tipoCodigo = !empty($datos['tipo_codigo']) ? $datos['tipo_codigo'] : null;
        $fechaInicio = !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : null;
        $fechaFin = !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null;

        $this->db->query("INSERT INTO cursos (instancia_id, creado_por, titulo, descripcion, idioma, nivel_cefr, modalidad, es_publico, requiere_codigo, codigo_acceso, tipo_codigo, max_estudiantes, fecha_inicio, fecha_fin) VALUES (:instancia_id, :creado_por, :titulo, :descripcion, :idioma, :nivel_cefr, :modalidad, :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :max_estudiantes, :fecha_inicio, :fecha_fin)");
        
        $this->db->bind(':instancia_id', $datos['instancia_id']);
        $this->db->bind(':creado_por', $datos['creado_por']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':idioma', $datos['idioma']);
        $this->db->bind(':nivel_cefr', $datos['nivel_cefr']);
        $this->db->bind(':modalidad', $datos['modalidad']);
        $this->db->bind(':es_publico', $datos['es_publico']);
        $this->db->bind(':requiere_codigo', $datos['requiere_codigo']);
        $this->db->bind(':codigo_acceso', $codigoAcceso);
        $this->db->bind(':tipo_codigo', $tipoCodigo);
        $this->db->bind(':max_estudiantes', $datos['max_estudiantes']);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);

        return $this->db->execute();
    }

    public function actualizarCurso($id, $datos) {
        $codigoAcceso = !empty($datos['codigo_acceso']) ? $datos['codigo_acceso'] : null;
        $tipoCodigo = !empty($datos['tipo_codigo']) ? $datos['tipo_codigo'] : null;

        $this->db->query("UPDATE cursos SET titulo = :titulo, descripcion = :descripcion, idioma = :idioma, nivel_cefr = :nivel_cefr, modalidad = :modalidad, es_publico = :es_publico, requiere_codigo = :requiere_codigo, codigo_acceso = :codigo_acceso, tipo_codigo = :tipo_codigo, max_estudiantes = :max_estudiantes WHERE id = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':idioma', $datos['idioma']);
        $this->db->bind(':nivel_cefr', $datos['nivel_cefr']);
        $this->db->bind(':modalidad', $datos['modalidad']);
        $this->db->bind(':es_publico', $datos['es_publico']);
        $this->db->bind(':requiere_codigo', $datos['requiere_codigo']);
        $this->db->bind(':codigo_acceso', $codigoAcceso);
        $this->db->bind(':tipo_codigo', $tipoCodigo);
        $this->db->bind(':max_estudiantes', $datos['max_estudiantes']);

        return $this->db->execute();
    }

    public function eliminarCurso($id) {
        $this->db->query("DELETE FROM cursos WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function generarCodigoAcceso() {
        return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
    }

    public function obtenerCursosPublicos() {
        $this->db->query("SELECT * FROM cursos WHERE es_publico = 1 ORDER BY fecha_creacion DESC");
        return $this->db->resultSet();
    }

    public function obtenerResumenCursosPorEstudiante($estudiante_id) {
        $this->db->query("
            SELECT
                c.*,
                COUNT(DISTINCT l.id) AS total_lecciones,
                COUNT(DISTINCT t.id) AS total_teorias,
                COUNT(DISTINCT a.id) AS total_actividades,
                COUNT(DISTINCT CASE WHEN pt.leido = 1 THEN t.id END) AS teorias_leidas,
                COUNT(DISTINCT CASE WHEN r.id IS NOT NULL THEN a.id END) AS actividades_respondidas
            FROM inscripciones i
            JOIN cursos c ON c.id = i.curso_id
            LEFT JOIN lecciones l ON l.curso_id = c.id
            LEFT JOIN teoria t ON t.leccion_id = l.id
            LEFT JOIN progreso_teoria pt ON pt.teoria_id = t.id AND pt.estudiante_id = i.estudiante_id
            LEFT JOIN actividades a ON a.leccion_id = l.id
            LEFT JOIN respuestas r ON r.actividad_id = a.id AND r.estudiante_id = i.estudiante_id
            WHERE i.estudiante_id = :estudiante_id
            GROUP BY c.id
            ORDER BY c.fecha_creacion DESC
        ");
        $this->db->bind(':estudiante_id', $estudiante_id);
        $cursos = $this->db->resultSet();

        foreach ($cursos as $curso) {
            $curso->total_items = (int) $curso->total_teorias + (int) $curso->total_actividades;
            $curso->completados = (int) $curso->teorias_leidas + (int) $curso->actividades_respondidas;
            $curso->porcentaje = $curso->total_items > 0
                ? (int) round(($curso->completados / $curso->total_items) * 100)
                : 0;
            if ($curso->total_items === 0) {
                $curso->estado_progreso = 'pendiente';
            } elseif ($curso->porcentaje >= 100) {
                $curso->estado_progreso = 'completado';
            } elseif ($curso->porcentaje > 0) {
                $curso->estado_progreso = 'en_progreso';
            } else {
                $curso->estado_progreso = 'pendiente';
            }
        }

        return $cursos;
    }
}
