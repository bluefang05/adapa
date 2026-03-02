<?php

require_once __DIR__ . '/../core/Database.php';

class ProfesorPlan
{
    public const PLAN_FREE = 'free';
    public const PLAN_PAID = 'paid';
    public const PLAN_LIFETIME = 'lifetime';

    public const FREE_MAX_COURSES = 1;
    public const FREE_MAX_LESSONS_PER_COURSE = 3;
    public const FREE_MAX_ACTIVITIES_PER_LESSON = 3;
    public const FREE_MAX_STUDENTS_PER_COURSE = 3;

    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public static function obtenerPlanesDisponibles()
    {
        return [
            self::PLAN_FREE => 'Plan gratuito',
            self::PLAN_PAID => 'Plan activo',
            self::PLAN_LIFETIME => 'Cuenta vitalicia',
        ];
    }

    public static function normalizarPlan($plan)
    {
        $plan = strtolower((string) $plan);
        return array_key_exists($plan, self::obtenerPlanesDisponibles()) ? $plan : self::PLAN_FREE;
    }

    public static function obtenerEtiquetaPlan($plan)
    {
        $planes = self::obtenerPlanesDisponibles();
        $plan = self::normalizarPlan($plan);
        return $planes[$plan];
    }

    public static function limitesFree()
    {
        return [
            'max_courses' => self::FREE_MAX_COURSES,
            'max_lessons_per_course' => self::FREE_MAX_LESSONS_PER_COURSE,
            'max_activities_per_lesson' => self::FREE_MAX_ACTIVITIES_PER_LESSON,
            'max_students_per_course' => self::FREE_MAX_STUDENTS_PER_COURSE,
        ];
    }

    public static function tieneAccesoCompleto($user)
    {
        if (!$user) {
            return false;
        }

        if (!empty($user->is_official)) {
            return true;
        }

        return in_array(self::normalizarPlan($user->billing_plan ?? null), [self::PLAN_PAID, self::PLAN_LIFETIME], true);
    }

    public static function obtenerResumenCuenta($user)
    {
        $plan = self::normalizarPlan($user->billing_plan ?? null);
        $isOfficial = !empty($user->is_official);

        return [
            'plan' => $plan,
            'plan_label' => self::obtenerEtiquetaPlan($plan),
            'is_official' => $isOfficial,
            'official_label' => $isOfficial ? 'Cuenta oficial' : null,
            'has_full_access' => self::tieneAccesoCompleto($user),
            'is_free' => !$isOfficial && $plan === self::PLAN_FREE,
        ];
    }

    public function obtenerUsuario($userId)
    {
        $this->db->query('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $userId);
        return $this->db->single();
    }

    public function obtenerResumenUsoProfesor($profesorId)
    {
        $usuario = $this->obtenerUsuario($profesorId);
        $resumenCuenta = self::obtenerResumenCuenta($usuario);

        $this->db->query('SELECT COUNT(*) AS total FROM cursos WHERE creado_por = :profesor_id');
        $this->db->bind(':profesor_id', $profesorId);
        $totalCursos = (int) ($this->db->single()->total ?? 0);

        $this->db->query('
            SELECT COUNT(*) AS total
            FROM lecciones l
            INNER JOIN cursos c ON c.id = l.curso_id
            WHERE c.creado_por = :profesor_id
        ');
        $this->db->bind(':profesor_id', $profesorId);
        $totalLecciones = (int) ($this->db->single()->total ?? 0);

        $this->db->query('
            SELECT COUNT(*) AS total
            FROM actividades a
            INNER JOIN lecciones l ON l.id = a.leccion_id
            INNER JOIN cursos c ON c.id = l.curso_id
            WHERE c.creado_por = :profesor_id
        ');
        $this->db->bind(':profesor_id', $profesorId);
        $totalActividades = (int) ($this->db->single()->total ?? 0);

        $this->db->query('
            SELECT COUNT(*) AS total
            FROM inscripciones i
            INNER JOIN cursos c ON c.id = i.curso_id
            WHERE c.creado_por = :profesor_id
        ');
        $this->db->bind(':profesor_id', $profesorId);
        $totalEstudiantes = (int) ($this->db->single()->total ?? 0);

        return array_merge($resumenCuenta, [
            'total_courses' => $totalCursos,
            'total_lessons' => $totalLecciones,
            'total_activities' => $totalActividades,
            'total_students' => $totalEstudiantes,
            'limits' => self::limitesFree(),
        ]);
    }

    public function puedeCrearCurso($profesorId)
    {
        $usuario = $this->obtenerUsuario($profesorId);
        if (self::tieneAccesoCompleto($usuario)) {
            return [true, null];
        }

        $this->db->query('SELECT COUNT(*) AS total FROM cursos WHERE creado_por = :profesor_id');
        $this->db->bind(':profesor_id', $profesorId);
        $total = (int) ($this->db->single()->total ?? 0);

        if ($total >= self::FREE_MAX_COURSES) {
            return [false, 'Tu plan gratuito ya usa su curso piloto. Activa tu plan para crear mas cursos.'];
        }

        return [true, null];
    }

    public function puedeCrearLeccion($profesorId, $cursoId)
    {
        $usuario = $this->obtenerUsuario($profesorId);
        if (self::tieneAccesoCompleto($usuario)) {
            return [true, null];
        }

        $this->db->query('SELECT COUNT(*) AS total FROM lecciones WHERE curso_id = :curso_id');
        $this->db->bind(':curso_id', $cursoId);
        $total = (int) ($this->db->single()->total ?? 0);

        if ($total >= self::FREE_MAX_LESSONS_PER_COURSE) {
            return [false, 'Tu plan gratuito permite hasta 3 lecciones por curso. Activa tu plan para ampliar el recorrido.'];
        }

        return [true, null];
    }

    public function puedeCrearActividad($profesorId, $leccionId)
    {
        $usuario = $this->obtenerUsuario($profesorId);
        if (self::tieneAccesoCompleto($usuario)) {
            return [true, null];
        }

        $this->db->query('SELECT COUNT(*) AS total FROM actividades WHERE leccion_id = :leccion_id');
        $this->db->bind(':leccion_id', $leccionId);
        $total = (int) ($this->db->single()->total ?? 0);

        if ($total >= self::FREE_MAX_ACTIVITIES_PER_LESSON) {
            return [false, 'Tu plan gratuito permite hasta 3 actividades por leccion. Activa tu plan para sumar mas practica.'];
        }

        return [true, null];
    }

    public function puedeAgregarEstudiante($cursoId)
    {
        $this->db->query('
            SELECT c.id, c.creado_por, u.billing_plan, u.is_official, COUNT(i.id) AS total_inscritos
            FROM cursos c
            INNER JOIN usuarios u ON u.id = c.creado_por
            LEFT JOIN inscripciones i ON i.curso_id = c.id
            WHERE c.id = :curso_id
            GROUP BY c.id, c.creado_por, u.billing_plan, u.is_official
            LIMIT 1
        ');
        $this->db->bind(':curso_id', $cursoId);
        $resumen = $this->db->single();

        if (!$resumen) {
            return [false, 'Curso no encontrado.'];
        }

        if (self::tieneAccesoCompleto($resumen)) {
            return [true, null];
        }

        if ((int) ($resumen->total_inscritos ?? 0) >= self::FREE_MAX_STUDENTS_PER_COURSE) {
            return [false, 'Este curso piloto ya alcanzo su limite de 3 estudiantes activos.'];
        }

        return [true, null];
    }

    public function normalizarDatosCursoParaPlan($profesorId, array $datos)
    {
        $usuario = $this->obtenerUsuario($profesorId);
        if (self::tieneAccesoCompleto($usuario)) {
            return [$datos, []];
        }

        $ajustes = [];

        if (!empty($datos['es_publico'])) {
            $datos['es_publico'] = 0;
            $ajustes[] = 'el curso queda privado';
        }

        if (empty($datos['requiere_codigo'])) {
            $datos['requiere_codigo'] = 1;
            $ajustes[] = 'el acceso se gestiona por codigo';
        }

        $maxEstudiantes = (int) ($datos['max_estudiantes'] ?? 0);
        if ($maxEstudiantes <= 0 || $maxEstudiantes > self::FREE_MAX_STUDENTS_PER_COURSE) {
            $datos['max_estudiantes'] = self::FREE_MAX_STUDENTS_PER_COURSE;
            $ajustes[] = 'el cupo se ajusto a 3 estudiantes';
        }

        return [$datos, $ajustes];
    }
}
