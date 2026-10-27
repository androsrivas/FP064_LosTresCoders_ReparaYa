<?php
// producto2/app/models/HistorialEstado.php

class HistorialEstado
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Registra un cambio de estado o evento en el historial de la incidencia.
     *
     */
    public function registrar(
        int $incidenciaId,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?int $usuarioId = null,
        ?string $comentario = null
    ): bool {
        $sql = "INSERT INTO historial_incidencias 
                    (incidencia_id, usuario_id, estado_anterior, estado_nuevo, comentario) 
                VALUES 
                    (:incidencia_id, :usuario_id, :estado_anterior, :estado_nuevo, :comentario)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':incidencia_id'  => $incidenciaId,
            ':usuario_id'     => $usuarioId,
            ':estado_anterior' => $estadoAnterior,
            ':estado_nuevo'    => $estadoNuevo,
            ':comentario'      => $comentario
        ]);
    }

    /**
     * Obtiene el historial completo de cambios de una incidencia junto con los datos del usuario.
     */
    public function obtenerPorIncidencia(int $incidenciaId): array
    {
        $sql = "SELECT h.*, u.nombre AS usuario_nombre, u.rol AS usuario_rol 
                FROM historial_incidencias h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.incidencia_id = :incidencia_id
                ORDER BY h.created_at DESC, h.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':incidencia_id' => $incidenciaId]);

        return $stmt->fetchAll();
    }
}