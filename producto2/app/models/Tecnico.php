<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Roles;

class Tecnico extends Model
{
    private const SELECT = 'SELECT t.id, t.usuario_id, t.nombre_completo, t.disponible, t.especialidad_id, e.nombre_especialidad, u.email, u.telefono
    FROM tecnicos t
    LEFT JOIN usuarios u ON u.id = t.usuario_id
    LEFT JOIN especialidades e ON e.id = t.especialidad_id';

    public static function all(): array 
    {
        return self::fetchAll(self::SELECT . ' ORDER BY t.nombre_completo');
    }

    public static function findById(int $id): ?array 
    {
        return self::fetchOne(self::SELECT . ' WHERE t.id = :id', ['id' => $id]);
    }

    public static function findByUsuarioId(int $usuarioId): ?array 
    {
        return self::fetchOne(self::SELECT . ' WHERE t.usuario_id = :uid', ['uid' => $usuarioId]);
    }

    public static function create(
        string $nombre,
        string $email,
        string $password,
        ?int $especialidadId = null,
        ?string $telefono = null,
        bool $disponible = true
    ): int {
        return self::transaction(function () use ($nombre, $email, $password, $especialidadId, $telefono, $disponible): int {
            $usuarioId = Usuario::create($nombre, $email, $password, Roles::TECNICO, $telefono);

            return self::insert(
                'INSERT INTO tecnicos (usuario_id, nombre_completo, especialidad_id, disponible)
                VALUES (:usuario_id, :nombre_completo, :especialidad_id, :disponible)',
                [
                    'usuario_id' => $usuarioId,
                    'nombre_completo' => $nombre,
                    'especialidad_id' => $especialidadId,
                    'disponible' => (int) $disponible,
                ]
            );
        });
    }

    public static function update(
        int $id,
        string $nombre,
        string $email,
        ?int $especialidadId,
        ?string $telefono,
        bool $disponible
    ): void {
        // Un solo UPDATE multitabla: es atómico, no necesita transacción.
        // No se declara ':nombre' dos veces porque con prepares nativos no se puede repetir.
        self::execute(
            'UPDATE tecnicos tecnicos
            JOIN usuarios u ON u.id = t.usuario_id
            SET u.nombre = :nombre, u.email = :email, u.telefono = :telefono, t.nombre_completo = :nombre_completo, t.especialidad_id = :especialidad_id, t.disponible = :disponible
            WHERE t.id = :id AND u.rol = :rol',
            [
                'nombre' => $nombre,
                'email' => Usuario::normalizeEmail($email),
                'telefono' => Usuario::normalizeTelefono($telefono),
                'nombre_completo' => $nombre,
                'especialidad_id' => $especialidadId,
                'disponible' => (int) $disponible,
                'id' => $id,
                'rol' => Roles::TECNICO,
            ]
        );
    }

    public static function delete(int $id): bool
    {
        return self::execute(
            'DELETE FROM usuarios WHERE id = :id AND rol = :rol',
            ['id' => $id, 'rol' => Roles::PARTICULAR]
        ) > 0;
    }
}