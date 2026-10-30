<?php
declare(strict_types=1);

namespace App\Core;

final class Roles
{
    public const ADMIN = 'admin';
    public const TECNICO = 'tecnico';
    public const PARTICULAR = 'particular';
    public const TODOS = [self::ADMIN, self::TECNICO, self::PARTICULAR];

    public static function isValid(string $rol): bool
    {
        return in_array($rol, self::TODOS, true);
    }
}