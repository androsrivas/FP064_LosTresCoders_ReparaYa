<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $c = config('database');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbanme=%s;charset=utf8mb4',
            $c['host'],
            $c['port'],
            $c['dbname']
        );

        try {
            self::$pdo = new PDO($dsn, $c['user'], $c['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB: ' . $e->getMessage());
            http_response_code(500);
            exit(Env::get('APP_DEBUG') === 'true'
                ? 'DB: ' . htmlspecialchars($e->getMessage())
                : 'Error interno. Inténtelo más tarde.');
        }

        return self::$pdo;
    }
}
