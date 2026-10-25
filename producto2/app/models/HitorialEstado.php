<?php
// producto2/app/models/HistorialEstado.php

require_once __DIR__ . '/../../config/config.php';

class HistorialEstado {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Registrar una entrada en la tabla historial_incidencias (permite no insertar comentario)
     */
    public function registrar($incidenciaId, $estadoAnterior, $estadoNuevo, $usuarioId, $comentario = null) {
        $sql = "INSERT INTO historial_incidencias 
                    (incidencia_id, usuario_id, estado_anterior, estado_nuevo, comentario) 
                VALUES 
                    (:incidencia_id, :usuario_id, :estado_anterior, :estado_nuevo, :comentario)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':incidencia_id'  => $incidenciaId,
            ':usuario_id'     => $usuarioId,
            ':estado_anterior'=> $estadoAnterior,
            ':estado_nuevo'   => $estadoNuevo,
            ':comentario'     => $comentario
        ]);
    }

    /**
     * Obtener el historial completo de transiciones de una incidencia
     * Incluye el nombre y rol del usuario que realizó la acción
     */
    public function obtenerPorIncidencia($incidenciaId) {
        $sql = "SELECT h.*, u.nombre AS nombre_usuario, u.rol 
                FROM historial_incidencias h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.incidencia_id = :incidencia_id
                ORDER BY h.created_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':incidencia_id' => $incidenciaId]);
        return $stmt->fetchAll();
    }
}