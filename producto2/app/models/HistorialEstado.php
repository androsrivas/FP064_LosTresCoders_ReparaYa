<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class HistorialEstado extends Model
{
    /**
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
        ]);
    }

    /**
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
}