<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
<<<<<<< HEAD

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
=======
use InvalidArgumentException;

class Incidencia extends Model
{
    /** Devuelve el id de la incidencia creada. */
    public static function crear(array $datos): int
    {
        $sql = "INSERT INTO incidencias
                    (localizador, cliente_id, especialidad_id, descripcion, direccion, fecha_servicio, tipo_urgencia, estado)
                VALUES
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
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

<<<<<<< HEAD
    /**
     * Buscar Incidencia por ID
     */
=======
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
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

<<<<<<< HEAD
    /**
     * Buscar Incidencia por Cliente
     */
    public static function obtenerPorCliente(int $clienteId): array
    {
        $sql = "SELECT i.*, e.nombre_especialidad 
=======
    public static function obtenerPorCliente(int $clienteId): array
    {
        $sql = "SELECT i.*, e.nombre_especialidad
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                WHERE i.cliente_id = :cliente_id
                ORDER BY i.created_at DESC";

        return self::fetchAll($sql, ['cliente_id' => $clienteId]);
    }

<<<<<<< HEAD
    /**
     * Buscar todas las Incidencias asignadas a un técnico específico
     */
=======
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
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

<<<<<<< HEAD
    /**
     * Buscar Incidencias sin técnico asignado (tecnico_id IS NULL)
     */
=======
    /** Incidencias pendientes de asignar (las canceladas también tienen tecnico_id NULL). */
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
    public static function obtenerSinAsignar(): array
    {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                WHERE i.tecnico_id IS NULL
<<<<<<< HEAD
=======
                  AND i.estado = 'Pendiente'
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
                ORDER BY i.created_at DESC";

        return self::fetchAll($sql);
    }

<<<<<<< HEAD
    /**
     * Obtiene el listado de incidencias filtrado por estado
     */
    public static function obtenerPorEstado(string $estado = 'Todas'): array
    {
        if ($estado === 'Todas') {
            $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente, t.nombre_completo AS nombre_tecnico
                    FROM incidencias i
                    INNER JOIN especialidades e ON i.especialidad_id = e.id
                    INNER JOIN usuarios u ON i.cliente_id = u.id
                    LEFT JOIN tecnicos t ON i.tecnico_id = t.id
                    ORDER BY i.created_at DESC";

            return self::fetchAll($sql);
        }

        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre AS nombre_cliente, t.nombre_completo AS nombre_tecnico
                FROM incidencias i
                INNER JOIN especialidades e ON i.especialidad_id = e.id
                INNER JOIN usuarios u ON i.cliente_id = u.id
                LEFT JOIN tecnicos t ON i.tecnico_id = t.id
                WHERE i.estado = :estado
                ORDER BY i.created_at DESC";

        return self::fetchAll($sql, ['estado' => $estado]);
    }

    /**
     * Actualizar estado de Incidencia
     */
    public static function actualizarEstado(int $id, string $nuevoEstado): int
    {
        //Lista  con el ENUM de estados
        $estadosPermitidos = ['Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada'];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            return 0;
        }

        $sql = "UPDATE incidencias SET estado = :estado WHERE id = :id";
        
=======
    public static function actualizarEstado(int $id, string $nuevoEstado): int
    {
        $estadosPermitidos = ['Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada'];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            throw new InvalidArgumentException("Estado de incidencia no válido: '{$nuevoEstado}'.");
        }

        $sql = "UPDATE incidencias SET estado = :estado WHERE id = :id";

>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
        return self::execute($sql, [
            'estado' => $nuevoEstado,
            'id'     => $id
        ]);
    }

<<<<<<< HEAD
    /**
     * Asignar un técnico a una Incidencia
     */
    public static function asignarTecnico(int $id, int $tecnicoId): int
    {
        //Control de excepcion PDO
        try {
            $sql = "UPDATE incidencias SET tecnico_id = :tecnico_id, estado = 'Asignada' WHERE id = :id";

            return self::execute($sql, [
                'tecnico_id' => $tecnicoId,
                'id'         => $id
            ]);
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Modificación de una incidencia
     */
    public static function actualizarIncidencia(int $id, array $datos): int
    {
        $sql = "UPDATE incidencias SET 
=======
    /** Asigna el técnico y pasa la incidencia a estado Asignada. */
    public static function asignarTecnico(int $id, int $tecnicoId): int
    {
        $sql = "UPDATE incidencias SET tecnico_id = :tecnico_id, estado = 'Asignada' WHERE id = :id";

        return self::execute($sql, [
            'tecnico_id' => $tecnicoId,
            'id'         => $id
        ]);
    }

    /** Devuelve 0 si ningún valor cambia (MySQL cuenta filas modificadas, no encontradas). */
    public static function actualizarIncidencia(int $id, array $datos): int
    {
        $sql = "UPDATE incidencias SET
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
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
            'tecnico_id'      => !empty($datos['tecnico_id']) ? (int)$datos['tecnico_id'] : null,
            'id'              => $id
        ]);
    }
<<<<<<< HEAD

    /**
     * Genera un localizador único de incidencia con formato REP-YYYY-XXXX 
     */
    public static function generarLocalizador(): string
    {
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 4));
        
        return "REP-{$year}-{$random}";
    }
}
=======
}
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
