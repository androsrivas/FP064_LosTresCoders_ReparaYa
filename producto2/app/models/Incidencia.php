<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Incidencia extends Model
{
    /**
     * Crear una nueva incidencia con su localizador único y estado dinámico
     */
    public static function crear(array $datos): int
    {
        $sql = "INSERT INTO incidencias 
                    (localizador, cliente_id, especialidad_id, descripcion, direccion, fecha_servicio, tipo_urgencia, estado) 
                VALUES 
                    (:localizador, :cliente_id, :especialidad_id, :descripcion, :direccion, :fecha_servicio, :tipo_urgencia, :estado)";

        return self::insert($sql, [
            'localizador'     => $datos['localizador'],
            'cliente_id'      => $datos['cliente_id'],
            'especialidad_id' => $datos['especialidad_id'],
            'descripcion'     => $datos['descripcion'],
            'direccion'       => $datos['direccion'],
            'fecha_servicio'  => $datos['fecha_servicio'],
            'tipo_urgencia'   => $datos['tipo_urgencia'],
            'estado'          => $datos['estado'] ?? 'Pendiente'
        ]);
    }

    /**
     * Buscar Incidencia por ID
     */
    public static function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente, t.nombre_completo AS nombre_tecnico
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                LEFT JOIN tecnicos t ON i.tecnico_id = t.id
                WHERE i.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Buscar Incidencia por Cliente
     */
    public static function obtenerPorCliente(int $clienteId): array
    {
        $sql = "SELECT i.*, e.nombre_especialidad 
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                WHERE i.cliente_id = :cliente_id
                ORDER BY i.created_at DESC";

        return self::fetchAll($sql, ['cliente_id' => $clienteId]);
    }

    /**
     * Buscar todas las Incidencias asignadas a un técnico específico
     */
    public static function obtenerPorTecnico(int $tecnicoId): array
    {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                WHERE i.tecnico_id = :tecnico_id
                ORDER BY i.fecha_servicio ASC";

        return self::fetchAll($sql, ['tecnico_id' => $tecnicoId]);
    }

    /**
     * Buscar Incidencias sin técnico asignado (tecnico_id IS NULL)
     */
    public static function obtenerSinAsignar(): array
    {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                WHERE i.tecnico_id IS NULL
                ORDER BY i.created_at DESC";

        return self::fetchAll($sql);
    }

    /**
     * Actualizar estado de Incidencia
     */
    public static function actualizarEstado(int $id, string $nuevoEstado): int
    {
        $sql = "UPDATE incidencias SET estado = :estado WHERE id = :id";
        
        return self::execute($sql, [
            'estado' => $nuevoEstado,
            'id'     => $id
        ]);
    }

    /**
     * Asignar un técnico a una Incidencia
     */
    public static function asignarTecnico(int $id, int $tecnicoId): int
    {
        $sql = "UPDATE incidencias SET tecnico_id = :tecnico_id, estado = 'Asignada' WHERE id = :id";

        return self::execute($sql, [
            'tecnico_id' => $tecnicoId,
            'id'         => $id
        ]);
    }

    /**
     * Modificación de una incidencia
     */
    public static function actualizarIncidencia(int $id, array $datos): int
    {
        $sql = "UPDATE incidencias SET 
                    especialidad_id = :especialidad_id,
                    descripcion     = :descripcion,
                    direccion       = :direccion,
                    fecha_servicio  = :fecha_servicio,
                    tipo_urgencia   = :tipo_urgencia,
                    estado          = :estado,
                    tecnico_id      = :tecnico_id
                WHERE id = :id";

        return self::execute($sql, [
            'especialidad_id' => $datos['especialidad_id'],
            'descripcion'     => $datos['descripcion'],
            'direccion'       => $datos['direccion'],
            'fecha_servicio'  => $datos['fecha_servicio'],
            'tipo_urgencia'   => $datos['tipo_urgencia'],
            'estado'          => $datos['estado'],
            'tecnico_id'      => $datos['tecnico_id'] ?? null,
            'id'              => $id
        ]);
    }
}