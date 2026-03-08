<?php

require_once __DIR__ . '/../core/Database.php';

class Curso {
    private $db;
    private const CEFR_ORDER = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function __construct() {
        $this->db = new Database();
    }

    public static function formatearRangoNivel($curso) {
        $desde = $curso->nivel_cefr_desde ?? $curso->nivel_cefr ?? null;
        $hasta = $curso->nivel_cefr_hasta ?? $curso->nivel_cefr ?? null;

        if (!$desde && !$hasta) {
            return '';
        }

        if ($desde && $hasta && $desde !== $hasta) {
            return $desde . '-' . $hasta;
        }

        return $desde ?: $hasta ?: '';
    }

    public static function esRutaCompleta($curso) {
        $desde = $curso->nivel_cefr_desde ?? $curso->nivel_cefr ?? null;
        $hasta = $curso->nivel_cefr_hasta ?? $curso->nivel_cefr ?? null;

        return !empty($desde) && !empty($hasta) && $desde !== $hasta;
    }

    public static function obtenerEtiquetaNivel($curso) {
        return self::esRutaCompleta($curso) ? 'Ruta completa' : 'Nivel unico';
    }

    public static function obtenerIdiomaObjetivo($curso) {
        return $curso->idioma_objetivo ?? $curso->idioma ?? '';
    }

    public static function obtenerIdiomaBase($curso) {
        return $curso->idioma_base ?? $curso->idioma_ensenanza ?? 'espanol';
    }

    public static function obtenerOpcionesCefr() {
        return self::CEFR_ORDER;
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
                mr.ruta_archivo AS portada_url,
                mr.alt_text AS portada_alt,
                COUNT(DISTINCT l.id) AS total_lecciones,
                COUNT(DISTINCT a.id) AS total_actividades,
                COUNT(DISTINCT i.estudiante_id) AS total_estudiantes
            FROM cursos c
            LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
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
        $this->db->query("
            SELECT c.*, mr.ruta_archivo AS portada_url, mr.alt_text AS portada_alt
            FROM cursos c
            LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
            WHERE c.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function crearCurso($datos) {
        $codigoAcceso = !empty($datos['codigo_acceso']) ? $datos['codigo_acceso'] : null;
        $tipoCodigo = !empty($datos['tipo_codigo']) ? $datos['tipo_codigo'] : null;
        $fechaInicio = !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : null;
        $fechaFin = !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null;

        $idiomaObjetivo = $datos['idioma_objetivo'] ?? $datos['idioma'];
        $idiomaBase = $datos['idioma_base'] ?? $datos['idioma_ensenanza'] ?? 'espanol';

        $this->db->query("INSERT INTO cursos (instancia_id, creado_por, titulo, descripcion, idioma, idioma_objetivo, idioma_base, idioma_ensenanza, portada_media_id, nivel_cefr, nivel_cefr_desde, nivel_cefr_hasta, modalidad, es_publico, requiere_codigo, codigo_acceso, tipo_codigo, max_estudiantes, fecha_inicio, fecha_fin, estado, estado_editorial) VALUES (:instancia_id, :creado_por, :titulo, :descripcion, :idioma, :idioma_objetivo, :idioma_base, :idioma_ensenanza, :portada_media_id, :nivel_cefr, :nivel_cefr_desde, :nivel_cefr_hasta, :modalidad, :es_publico, :requiere_codigo, :codigo_acceso, :tipo_codigo, :max_estudiantes, :fecha_inicio, :fecha_fin, :estado, :estado_editorial)");
        
        $this->db->bind(':instancia_id', $datos['instancia_id']);
        $this->db->bind(':creado_por', $datos['creado_por']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':idioma', $idiomaObjetivo);
        $this->db->bind(':idioma_objetivo', $idiomaObjetivo);
        $this->db->bind(':idioma_base', $idiomaBase);
        $this->db->bind(':idioma_ensenanza', $idiomaBase);
        $this->db->bind(':portada_media_id', $datos['portada_media_id'] ?? null);
        $this->db->bind(':nivel_cefr', $datos['nivel_cefr']);
        $this->db->bind(':nivel_cefr_desde', $datos['nivel_cefr_desde'] ?? $datos['nivel_cefr']);
        $this->db->bind(':nivel_cefr_hasta', $datos['nivel_cefr_hasta'] ?? $datos['nivel_cefr']);
        $this->db->bind(':modalidad', $datos['modalidad']);
        $this->db->bind(':es_publico', $datos['es_publico']);
        $this->db->bind(':requiere_codigo', $datos['requiere_codigo']);
        $this->db->bind(':codigo_acceso', $codigoAcceso);
        $this->db->bind(':tipo_codigo', $tipoCodigo);
        $this->db->bind(':max_estudiantes', $datos['max_estudiantes']);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        $this->db->bind(':estado', $datos['estado'] ?? 'preparacion');
        $this->db->bind(':estado_editorial', $datos['estado_editorial'] ?? 'borrador');

        return $this->db->execute();
    }

    public function duplicarCurso($id, $instanciaId, $creadoPor) {
        $curso = $this->obtenerCursoPorId($id);
        if (!$curso) {
            return null;
        }

        $datos = [
            'instancia_id' => $instanciaId,
            'creado_por' => $creadoPor,
            'titulo' => trim((string) $curso->titulo) . ' (copia)',
            'descripcion' => $curso->descripcion,
            'idioma' => $curso->idioma_objetivo ?? $curso->idioma,
            'idioma_objetivo' => $curso->idioma_objetivo ?? $curso->idioma,
            'idioma_base' => $curso->idioma_base ?? $curso->idioma_ensenanza ?? 'espanol',
            'idioma_ensenanza' => $curso->idioma_base ?? $curso->idioma_ensenanza ?? 'espanol',
            'portada_media_id' => $curso->portada_media_id ?? null,
            'nivel_cefr' => $curso->nivel_cefr ?? 'A1',
            'nivel_cefr_desde' => $curso->nivel_cefr_desde ?? $curso->nivel_cefr ?? 'A1',
            'nivel_cefr_hasta' => $curso->nivel_cefr_hasta ?? $curso->nivel_cefr ?? 'A1',
            'modalidad' => $curso->modalidad ?? 'perpetuo',
            'es_publico' => 0,
            'requiere_codigo' => !empty($curso->requiere_codigo) ? 1 : 0,
            'codigo_acceso' => !empty($curso->requiere_codigo) ? $this->generarCodigoAcceso() : null,
            'tipo_codigo' => $curso->tipo_codigo ?? null,
            'max_estudiantes' => $curso->max_estudiantes ?? 0,
            'fecha_inicio' => $curso->fecha_inicio ?? null,
            'fecha_fin' => $curso->fecha_fin ?? null,
            'estado' => 'preparacion',
            'estado_editorial' => 'borrador',
        ];

        if (!$this->crearCurso($datos)) {
            return null;
        }

        return (int) $this->db->lastInsertId();
    }

    public function actualizarCurso($id, $datos) {
        $codigoAcceso = !empty($datos['codigo_acceso']) ? $datos['codigo_acceso'] : null;
        $tipoCodigo = !empty($datos['tipo_codigo']) ? $datos['tipo_codigo'] : null;

        $idiomaObjetivo = $datos['idioma_objetivo'] ?? $datos['idioma'];
        $idiomaBase = $datos['idioma_base'] ?? $datos['idioma_ensenanza'] ?? 'espanol';

        $this->db->query("UPDATE cursos SET titulo = :titulo, descripcion = :descripcion, idioma = :idioma, idioma_objetivo = :idioma_objetivo, idioma_base = :idioma_base, idioma_ensenanza = :idioma_ensenanza, portada_media_id = :portada_media_id, nivel_cefr = :nivel_cefr, nivel_cefr_desde = :nivel_cefr_desde, nivel_cefr_hasta = :nivel_cefr_hasta, modalidad = :modalidad, es_publico = :es_publico, requiere_codigo = :requiere_codigo, codigo_acceso = :codigo_acceso, tipo_codigo = :tipo_codigo, max_estudiantes = :max_estudiantes, estado = :estado, estado_editorial = :estado_editorial WHERE id = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':idioma', $idiomaObjetivo);
        $this->db->bind(':idioma_objetivo', $idiomaObjetivo);
        $this->db->bind(':idioma_base', $idiomaBase);
        $this->db->bind(':idioma_ensenanza', $idiomaBase);
        $this->db->bind(':portada_media_id', $datos['portada_media_id'] ?? null);
        $this->db->bind(':nivel_cefr', $datos['nivel_cefr']);
        $this->db->bind(':nivel_cefr_desde', $datos['nivel_cefr_desde'] ?? $datos['nivel_cefr']);
        $this->db->bind(':nivel_cefr_hasta', $datos['nivel_cefr_hasta'] ?? $datos['nivel_cefr']);
        $this->db->bind(':modalidad', $datos['modalidad']);
        $this->db->bind(':es_publico', $datos['es_publico']);
        $this->db->bind(':requiere_codigo', $datos['requiere_codigo']);
        $this->db->bind(':codigo_acceso', $codigoAcceso);
        $this->db->bind(':tipo_codigo', $tipoCodigo);
        $this->db->bind(':max_estudiantes', $datos['max_estudiantes']);
        $this->db->bind(':estado', $datos['estado'] ?? 'preparacion');
        $this->db->bind(':estado_editorial', $datos['estado_editorial'] ?? 'borrador');

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
        $this->db->query("
            SELECT c.*, mr.ruta_archivo AS portada_url, mr.alt_text AS portada_alt
            FROM cursos c
            LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
            WHERE c.es_publico = 1
            ORDER BY c.fecha_creacion DESC
        ");
        return $this->db->resultSet();
    }

    public function obtenerCursosDisponiblesParaExplorar($filtros = []) {
        $sql = "
            SELECT c.*, mr.ruta_archivo AS portada_url, mr.alt_text AS portada_alt
            FROM cursos c
            LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
            WHERE c.es_publico = 1
              AND c.estado = 'activo'
              AND c.inscripcion_abierta = 1
              AND (c.requiere_codigo = 0 OR c.requiere_codigo IS NULL)
        ";

        $idiomaObjetivo = trim($filtros['idioma_objetivo'] ?? '');
        if ($idiomaObjetivo !== '') {
            $sql .= " AND c.idioma_objetivo = :idioma_objetivo";
        }

        $idiomaBase = trim($filtros['idioma_base'] ?? '');
        if ($idiomaBase !== '') {
            $sql .= " AND COALESCE(NULLIF(c.idioma_base, ''), c.idioma_ensenanza, 'espanol') = :idioma_base";
        }

        $nivelObjetivo = trim($filtros['nivel_objetivo'] ?? '');
        if (in_array($nivelObjetivo, self::CEFR_ORDER, true)) {
            $sql .= "
              AND FIELD(:nivel_objetivo, 'A1', 'A2', 'B1', 'B2', 'C1', 'C2')
                  BETWEEN FIELD(COALESCE(c.nivel_cefr_desde, c.nivel_cefr), 'A1', 'A2', 'B1', 'B2', 'C1', 'C2')
                      AND FIELD(COALESCE(c.nivel_cefr_hasta, c.nivel_cefr), 'A1', 'A2', 'B1', 'B2', 'C1', 'C2')
            ";
        }

        $tipoRecorrido = trim($filtros['tipo_recorrido'] ?? '');
        if ($tipoRecorrido === 'ruta_completa') {
            $sql .= " AND COALESCE(c.nivel_cefr_desde, c.nivel_cefr) <> COALESCE(c.nivel_cefr_hasta, c.nivel_cefr)";
        } elseif ($tipoRecorrido === 'nivel_unico') {
            $sql .= " AND COALESCE(c.nivel_cefr_desde, c.nivel_cefr) = COALESCE(c.nivel_cefr_hasta, c.nivel_cefr)";
        }

        $sql .= " ORDER BY c.fecha_creacion DESC";
        $this->db->query($sql);

        if ($idiomaObjetivo !== '') {
            $this->db->bind(':idioma_objetivo', $idiomaObjetivo);
        }

        if ($idiomaBase !== '') {
            $this->db->bind(':idioma_base', $idiomaBase);
        }

        if (in_array($nivelObjetivo, self::CEFR_ORDER, true)) {
            $this->db->bind(':nivel_objetivo', $nivelObjetivo);
        }

        return $this->db->resultSet();
    }

    public function validarCodigoDeAcceso($codigo, $estudianteId, $instanciaId) {
        $codigo = trim($codigo);

        if ($codigo === '') {
            return null;
        }

        $this->db->query("
            SELECT c.*
            FROM cursos c
            WHERE c.instancia_id = :instancia_id
              AND c.estado = 'activo'
              AND c.inscripcion_abierta = 1
              AND c.requiere_codigo = 1
              AND c.codigo_acceso = :codigo
            LIMIT 1
        ");
        $this->db->bind(':instancia_id', $instanciaId);
        $this->db->bind(':codigo', $codigo);
        $curso = $this->db->single();

        if ($curso) {
            return $curso;
        }

        $this->db->query("
            SELECT c.*, ca.id AS codigo_id, ca.tipo AS codigo_tipo
            FROM codigos_acceso ca
            INNER JOIN cursos c ON c.id = ca.curso_id
            WHERE ca.instancia_id = :instancia_id
              AND ca.codigo = :codigo
              AND ca.activo = 1
              AND c.estado = 'activo'
              AND c.inscripcion_abierta = 1
              AND (
                    ca.tipo = 'unico_curso'
                    OR (ca.tipo = 'por_estudiante' AND (ca.estudiante_id IS NULL OR ca.estudiante_id = :estudiante_id))
                    OR ca.tipo = 'combo_grupo'
                  )
            LIMIT 1
        ");
        $this->db->bind(':instancia_id', $instanciaId);
        $this->db->bind(':codigo', $codigo);
        $this->db->bind(':estudiante_id', $estudianteId);

        return $this->db->single();
    }

    public function registrarUsoCodigo($codigoId, $estudianteId) {
        $this->db->query("
            UPDATE codigos_acceso
            SET usado_por = :estudiante_id,
                fecha_uso = NOW(),
                activo = CASE WHEN tipo = 'por_estudiante' THEN 0 ELSE activo END
            WHERE id = :codigo_id
        ");
        $this->db->bind(':codigo_id', $codigoId);
        $this->db->bind(':estudiante_id', $estudianteId);

        return $this->db->execute();
    }

    public function obtenerResumenCursosPorEstudiante($estudiante_id) {
        $this->db->query("
            SELECT
                c.*,
                mr.ruta_archivo AS portada_url,
                mr.alt_text AS portada_alt,
                (
                    SELECT COUNT(*)
                    FROM lecciones l
                    WHERE l.curso_id = c.id
                ) AS total_lecciones,
                (
                    SELECT COUNT(*)
                    FROM teoria t
                    INNER JOIN lecciones l ON l.id = t.leccion_id
                    WHERE l.curso_id = c.id
                ) AS total_teorias,
                (
                    SELECT COUNT(*)
                    FROM actividades a
                    INNER JOIN lecciones l ON l.id = a.leccion_id
                    WHERE l.curso_id = c.id
                ) AS total_actividades,
                (
                    SELECT COUNT(DISTINCT pt.teoria_id)
                    FROM progreso_teoria pt
                    INNER JOIN teoria t ON t.id = pt.teoria_id
                    INNER JOIN lecciones l ON l.id = t.leccion_id
                    WHERE l.curso_id = c.id
                      AND pt.estudiante_id = i.estudiante_id
                      AND pt.leido = 1
                ) AS teorias_leidas,
                (
                    SELECT COUNT(DISTINCT r.actividad_id)
                    FROM respuestas r
                    INNER JOIN actividades a ON a.id = r.actividad_id
                    INNER JOIN lecciones l ON l.id = a.leccion_id
                    WHERE l.curso_id = c.id
                      AND r.estudiante_id = i.estudiante_id
                ) AS actividades_respondidas
            FROM inscripciones i
            JOIN cursos c ON c.id = i.curso_id
            LEFT JOIN media_recursos mr ON mr.id = c.portada_media_id
            WHERE i.estudiante_id = :estudiante_id
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
            $curso->porcentaje = max(0, min(100, $curso->porcentaje));
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
