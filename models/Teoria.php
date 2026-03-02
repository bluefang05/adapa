<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

class Teoria {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function crearTeoria($datos) {
        $this->db->query("INSERT INTO teoria (leccion_id, titulo, contenido, tipo_contenido, orden, duracion_minutos) VALUES (:leccion_id, :titulo, :contenido, :tipo_contenido, :orden, :duracion_minutos)");
        
        $this->db->bind(':leccion_id', $datos['leccion_id']);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':tipo_contenido', $datos['tipo_contenido']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':duracion_minutos', $datos['duracion_minutos']);

        return $this->db->execute();
    }

    public function obtenerTeoriaPorId($id) {
        $this->db->query("SELECT * FROM teoria WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function obtenerTeoriasPorLeccion($leccion_id) {
        $this->db->query("SELECT * FROM teoria WHERE leccion_id = :leccion_id ORDER BY orden ASC");
        $this->db->bind(':leccion_id', $leccion_id);
        return $this->db->resultSet();
    }

    public function actualizarTeoria($id, $datos) {
        $this->db->query("UPDATE teoria SET titulo = :titulo, contenido = :contenido, tipo_contenido = :tipo_contenido, orden = :orden, duracion_minutos = :duracion_minutos WHERE id = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':titulo', $datos['titulo']);
        $this->db->bind(':contenido', $datos['contenido']);
        $this->db->bind(':tipo_contenido', $datos['tipo_contenido']);
        $this->db->bind(':orden', $datos['orden']);
        $this->db->bind(':duracion_minutos', $datos['duracion_minutos']);

        return $this->db->execute();
    }

    public function eliminarTeoria($id) {
        $this->db->query("DELETE FROM teoria WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function obtenerTotalTeoriasPorLeccion($leccion_id) {
        $this->db->query("SELECT COUNT(*) as total FROM teoria WHERE leccion_id = :leccion_id");
        $this->db->bind(':leccion_id', $leccion_id);
        $resultado = $this->db->single();
        return $resultado ? $resultado->total : 0;
    }

    public function marcarComoLeida($estudiante_id, $teoria_id) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to ensure it's marked as read
        $this->db->query("INSERT INTO progreso_teoria (estudiante_id, teoria_id, leido) VALUES (:estudiante_id, :teoria_id, 1) ON DUPLICATE KEY UPDATE leido = 1, fecha_leido = CURRENT_TIMESTAMP");
        $this->db->bind(':estudiante_id', $estudiante_id);
        $this->db->bind(':teoria_id', $teoria_id);
        return $this->db->execute();
    }

    public function obtenerTeoriasConProgreso($leccion_id, $estudiante_id) {
        $this->db->query("
            SELECT t.*, pt.leido, pt.fecha_leido 
            FROM teoria t
            LEFT JOIN progreso_teoria pt ON t.id = pt.teoria_id AND pt.estudiante_id = :estudiante_id
            WHERE t.leccion_id = :leccion_id 
            ORDER BY t.orden ASC
        ");
        $this->db->bind(':leccion_id', $leccion_id);
        $this->db->bind(':estudiante_id', $estudiante_id);
        return $this->db->resultSet();
    }
}