<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

class OpcionMultiple {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtener todas las opciones de una actividad
     */
    public function obtenerOpcionesPorActividad($actividad_id) {
        try {
            $this->db->query("
                SELECT id, actividad_id, texto, texto AS opcion_texto, es_correcta, orden
                FROM opciones_multiples
                WHERE actividad_id = :actividad_id 
                ORDER BY orden ASC, id ASC
            ");
            
            $this->db->bind(':actividad_id', $actividad_id);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error al obtener opciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solo las opciones correctas de una actividad
     */
    public function obtenerOpcionesCorrectas($actividad_id) {
        try {
            $this->db->query("
                SELECT id, actividad_id, texto, texto AS opcion_texto, es_correcta, orden
                FROM opciones_multiples
                WHERE actividad_id = :actividad_id AND es_correcta = 1
                ORDER BY orden ASC, id ASC
            ");
            
            $this->db->bind(':actividad_id', $actividad_id);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error al obtener opciones correctas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una opción específica por ID
     */
    public function obtenerOpcionPorId($opcion_id) {
        try {
            $this->db->query("
                SELECT id, actividad_id, texto, texto AS opcion_texto, es_correcta, orden
                FROM opciones_multiples
                WHERE id = :id
            ");
            
            $this->db->bind(':id', $opcion_id);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log("Error al obtener opción: " . $e->getMessage());
            return null;
        }
    }
}
