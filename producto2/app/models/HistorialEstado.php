<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class HistorialEstado extends Model
{
    /**
<<<<<<< HEAD
     * Registra un evento en la tabla historial_incidencias
     */
    public static function crear(int $incidenciaId, ?string $estadoAnterior, string $estadoNuevo, int $usuarioId, ?string $observacion = null): int
    {
        $sql = "INSERT INTO historial_incidencias 
                    (incidencia_id, estado_anterior, estado_nuevo, usuario_id, observacion) 
                VALUES 
                    (:incidencia_id, :estado_anterior, :estado_nuevo, :usuario_id, :observacion)";

        return self::insert($sql, [
            'incidencia_id'   => $incidenciaId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $estadoNuevo,
            'usuario_id'      => $usuarioId,
            'observacion'     => $observacion
=======
     * Registra un cambio de estado o evento en el historial de la incidencia.
     */
    public static function registrar(
        int $incidenciaId,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?int $usuarioId = null,
        ?string $comentario = null
    ): int {
        $sql = "INSERT INTO historial_incidencias 
                    (incidencia_id, usuario_id, estado_anterior, estado_nuevo, comentario) 
                VALUES 
                    (:incidencia_id, :usuario_id, :estado_anterior, :estado_nuevo, :comentario)";

        return self::insert($sql, [
            'incidencia_id'   => $incidenciaId,
            'usuario_id'      => $usuarioId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $estadoNuevo,
            'comentario'      => $comentario
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
        ]);
    }

    /**
<<<<<<< HEAD
     * Obtiene el historial completo de una incidencia ordenado cronológicamente
     */
    public static function obtenerPorIncidencia(int $incidenciaId): array
    {
        $sql = "SELECT h.*, u.nombre AS nombre_usuario, u.rol
                FROM historial_incidencias h
                INNER JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.incidencia_id = :incidencia_id
                ORDER BY h.created_at ASC";

        return self::fetchAll($sql, ['incidencia_id' => $incidenciaId]);
    }

    /**
    * Obtiene la trazabilidad histórica de una incidencia en orden cronológico
    */
    public static function obtenerPorIncidencia(int $incidenciaId): array
    {
        $sql = "SELECT h.*, u.nombre AS nombre_usuario, u.rol AS rol_usuario
            FROM historial_incidencias h
            INNER JOIN usuarios u ON h.usuario_id = u.id
            WHERE h.incidencia_id = :incidencia_id
            ORDER BY h.fecha_cambio ASC";

        return self::fetchAll($sql, ['incidencia_id' => $incidenciaId]);
    }

=======
     * Obtiene el historial completo de cambios de una incidencia junto con los datos del usuario.
     */
    public static function obtenerPorIncidencia(int $incidenciaId): array
    {
        $sql = "SELECT h.*, u.nombre AS usuario_nombre, u.rol AS usuario_rol 
                FROM historial_incidencias h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.incidencia_id = :incidencia_id
                ORDER BY h.created_at DESC, h.id DESC";

        return self::fetchAll($sql, ['incidencia_id' => $incidenciaId]);
    }
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
}