<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Roles;
use InvalidArgumentException;

class Usuario extends Model
{
    public static function findByEmail(string $email): ?array
    {
        return self::fetchOne(
            'SELECT * FROM usuarios WHERE email = :email LIMIT 1',
            ['email' => self::normalizeEmail($email)]
        );
    }

    public static function findById(int $id): ?array
    {
        return self::fetchOne(
            'SELECT id, nombre, email, rol, telefono FROM usuarios WHERE id = :id',
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
        string $rol = Roles::PARTICULAR,
        ?string $telefono = null
    ): int {
        if (!Roles::isValid($rol)) {
            throw new InvalidArgumentException('Rol no válido.');
        }

        return self::insert(
            'INSERT INTO usuarios (nombre, email, password, rol, telefono)
            VALUES (:nombre, :email, :password, :rol, :telefono)',
            [
                'nombre' => $nombre,
                'email' => self::normalizeEmail($email),
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'rol' => $rol,
                'telefono' => self::normalizeTelefono($telefono),
            ]
        );
    }

    public static function update(
        int $id, 
        string $nombre, 
        string $email, 
        ?string $password = null,
        ?string $telefono = null
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

        if ($telefono !== null) {
            $sql .= ', telefono = :telefono';
            $params['telefono'] = self::normalizeTelefono($telefono);
        }

        self::execute($sql . ' WHERE id = :id', $params);
    }

    public static function verifyCredentials(string $email, string $password): ?array 
    {
        $u = self::findByEmail($email);
        return ($u !== null && password_verify($password, (string) $u['password'])) ? $u : null;
    }

    public static function verifyPassword(int $id, string $password):bool 
    {
        $row = self::fetchOne('SELECT password FROM usuarios WHERE id = :id', ['id' => $id]);
        return $row !== null && password_verify($password, (string) $row['password']);
    }

    public static function updatePassword(int $id, string $password): void
    {
        self::execute(
            'UPDATE usuarios SET password = :password WHERE id = :id',
            [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $id,
            ]
        );
    }

    private static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private static function normalizeTelefono(?string $telefono): ?string
    {
        $t = preg_replace('/[\s.\-]/', '', trim((string) $telefono)) ?? '';
        $t = preg_replace('/^(?:\+|00)34/', '', $t) ?? '';

        return $t === '' ? null : $t;
    }
}