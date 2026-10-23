<?php

require_once __DIR__ . '/../../config/config.php';

class Incidencia {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Crear una nueva incidencia con su localizador único y estado dinámico
     */
    public function crear($datos) {
        $sql = "INSERT INTO incidencias 
                    (localizador, cliente_id, especialidad_id, descripcion, direccion, fecha_servicio, tipo_urgencia, estado) 
                VALUES 
                    (:localizador, :cliente_id, :especialidad_id, :descripcion, :direccion, :fecha_servicio, :tipo_urgencia, :estado)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':localizador'     => $datos['localizador'],
            ':cliente_id'      => $datos['cliente_id'],
            ':especialidad_id' => $datos['especialidad_id'],
            ':descripcion'     => $datos['descripcion'],
            ':direccion'       => $datos['direccion'],
            ':fecha_servicio'  => $datos['fecha_servicio'],
            ':tipo_urgencia'   => $datos['tipo_urgencia'],
            ':estado'          => $datos['estado'] ?? 'Pendiente'
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Buscar Incidencia por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente, t.nombre_completo AS nombre_tecnico
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                LEFT JOIN tecnicos t ON i.tecnico_id = t.id
                WHERE i.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Buscar Incidencia por Cliente
     */
    public function obtenerPorCliente($clienteId) {
        $sql = "SELECT i.*, e.nombre_especialidad 
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                WHERE i.cliente_id = :cliente_id
                ORDER BY i.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar todas las Incidencias asignadas a un tecnico especifico
     */
    public function obtenerPorTecnico($tecnicoId) {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                WHERE i.tecnico_id = :tecnico_id
                ORDER BY i.fecha_servicio ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tecnico_id' => $tecnicoId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar Incidencias sin tecnico asignado (tecnico_id IS NULL)
     */
    public function obtenerSinAsignar() {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                WHERE i.tecnico_id IS NULL
                ORDER BY i.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Actualizar estado de Incidencia
     */
    public function actualizarEstado($id, $nuevoEstado) {
        $sql = "UPDATE incidencias SET estado = :estado WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id'     => $id
        ]);
    }

    /**
     * Asignar un tecnico a una Incidencia
     */
    public function asignarTecnico($id, $tecnicoId) {
        $sql = "UPDATE incidencias SET tecnico_id = :tecnico_id, estado = 'Asignada' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':tecnico_id' => $tecnicoId,
            ':id'         => $id
        ]);
    }
}