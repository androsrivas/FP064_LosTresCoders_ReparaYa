<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Roles;
use InvalidArgumentException;
use PDOException;

class Usuario extends Model
{
    public static function findByEmail(string $email): ?array
    {
        return self::fetchOne(
            'SELECT * FROM usuarios WHERE email = :email LIMIT 1',
            ['email' => $email]
        );
    }

    public function findById(int $id): ?array
    {
        return self::fetchOne(
            'SELECT id, nombre, email, rol FROM usuarios WHERE id = :id',
            ['id' => $id]
        );
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool 
    {
        $row = self::fetchOne(
            'SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1',
            ['email' => self::normalizeEmail($email), 'id' => $exceptId ?? 0]
        );

        return $row !== null;
    } 

    public static function create(
        string $nombre,
        string $email,
        string $password,
        string $rol = Roles::PARTICULAR
    ): int {
        if (!Roles::isValid($rol)) {
            throw new InvalidArgumentException('Rol no válido.');
        }

        return self::insert(
            'INSERT INTO usuarios (nombre, email, password, rol)
            VALUES (:nombre, :email, :password, :rol)',
            [
                'nombre' => $nombre,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'rol' => $rol,
            ]
        );
    }

    public static function update(
        int $id, 
        string $nombre, 
        string $email, 
        ?string $password = null
    ): void {
        $params = [
            'nombre' => $nombre,
            'email' => self::normalizeEmail($email),
            'id' => $id,
        ];

        $sql = 'UPDATE usuarios SET nombre = :nombre, email = :email';

        if ($password !== null && $password !== '') {
            $sql .= ', password = :password';
            $params['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        self::execute($sql . ' WHERE id = :id', $params);
    }

    public static function verifyCredentials(string $email, string $password): ?array 
    {
        $u = self::findByEmail($email);
        return ($u !== null && password_verify($password, (string) $u['password'])) ? $u : null;
    }

    private static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }
}