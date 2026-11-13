<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ComentarioIncidencia extends Model
{
    /**
     * Obtiene todos los mensajes del foro de una incidencia ordenados cronológicamente,
     * haciendo JOIN con `usuarios` para recuperar el nombre y rol del emisor.
     */
    public static function obtenerPorIncidencia(int $incidenciaId): array
    {
        $sql = "SELECT c.id,
                       c.incidencia_id,
                       c.usuario_id,
                       c.comentario,
                       c.created_at,
                       u.nombre AS emisor_nombre,
                       u.rol    AS emisor_rol
                FROM comentarios_incidencias c
                INNER JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.incidencia_id = :incidencia_id
                ORDER BY c.created_at ASC, c.id ASC";

        return self::fetchAll($sql, [
            ':incidencia_id' => $incidenciaId
        ]);
    }

    /**
     * Inserta un nuevo comentario en el foro de la incidencia.
     */
    public static function crear(int $incidenciaId, int $usuarioId, string $comentario): int
    {
        $sql = "INSERT INTO comentarios_incidencias (incidencia_id, usuario_id, comentario)
                VALUES (:incidencia_id, :usuario_id, :comentario)";

        return self::insert($sql, [
            ':incidencia_id' => $incidenciaId,
            ':usuario_id'    => $usuarioId,
            ':comentario'    => $comentario
        ]);
    }
}