<?php

require_once __DIR__ . '/../core/Database.php';

class Inscripcion {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function inscribirEstudiante($curso_id, $estudiante_id) {
        if ($this->verificarInscripcion($curso_id, $estudiante_id)) {
            return true;
        }

        $this->db->query("INSERT INTO inscripciones (curso_id, estudiante_id) VALUES (:curso_id, :estudiante_id)");
        $this->db->bind(':curso_id', $curso_id);
        $this->db->bind(':estudiante_id', $estudiante_id);
        
        return $this->db->execute();
    }

    public function obtenerCursosPorEstudiante($estudiante_id) {
        $this->db->query("
            SELECT c.* FROM cursos c
            JOIN inscripciones i ON c.id = i.curso_id
            WHERE i.estudiante_id = :estudiante_id
        ");
        $this->db->bind(':estudiante_id', $estudiante_id);
        return $this->db->resultSet();
    }

    public function verificarInscripcion($curso_id, $estudiante_id) {
        $this->db->query("SELECT * FROM inscripciones WHERE curso_id = :curso_id AND estudiante_id = :estudiante_id");
        $this->db->bind(':curso_id', $curso_id);
        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }
}
