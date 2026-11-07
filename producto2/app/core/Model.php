<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
<<<<<<< HEAD
=======
use Throwable;
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e

abstract class Model
{

    protected static function db(): PDO
    {
        return Database::connection();
    }

    /** Ejecuta una sentencia preparada */
    protected static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected static function fetchAll(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    protected static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    protected static function execute(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    protected static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::db()->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::db()->beginTransaction();
    }

    public static function commit(): void
    {
        self::db()->commit();
    }

    public static function rollBack(): void
    {
        if (self::db()->inTransaction()) self::db()->rollBack();
    }
<<<<<<< HEAD
=======

    public static function transaction(callable $fn): mixed
    {
        self::beginTransaction();

        try {
            $result = $fn();
            self::commit();
            return $result;
        } catch (Throwable $e) {
            self::rollBack();
            throw $e;
        }
    }
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
}
