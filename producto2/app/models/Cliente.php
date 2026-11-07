<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Roles;

class Cliente extends Model
{
    public static function all(): array
    {
        return self::fetchAll(
            'SELECT id, nombre, email, telefono, created_at
            FROM usuarios WHERE rol = :rol ORDER BY nombre',
            ['rol' => Roles::PARTICULAR]
        );
    }

    public static function findById(int $id): ?array
    {
        return self::fetchOne(
            'SELECT id, nombre, email, telefono, created_at
            FROM usuarios WHERE id = :id AND rol = :rol',
            ['id' => $id, 'rol' => Roles::PARTICULAR]
        );
    }

    public static function create(
        string $nombre,
        string $email,
        string $password,
        ?string $telefono = null
    ): int {
        return Usuario::create($nombre, $email, $password, Roles::PARTICULAR, $telefono);
    }

    public static function update(int $id, string $nombre, string $email, ?string $telefono): void
    {
        self::execute(
            'UPDATE usuarios 
            SET nombre = :nombre, email = :email, telefono = :telefono 
            WHERE id = :id 
            AND rol = :rol',
            [
                'nombre' => $nombre,
                'email' => Usuario::normalizeEmail($email),
                'telefono' => Usuario::normalizeTelefono($telefono),
                'id' => $id,
                'rol' => Roles::PARTICULAR
            ]);
    }

    public static function delete(int $id): void
    {
        self::execute(
            'DELETE FROM usuarios WHERE id = :id AND rol = :rol',
            ['id' => $id, 'rol' => Roles::PARTICULAR]
        );
    }
}