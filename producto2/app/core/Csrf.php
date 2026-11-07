<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf 
{
    private const KEY = '_csrf';

    /** Token de la sesión actual (se crea la primera vez que se pide) */
    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }

        return (string) Session::get(self::KEY);
    }

    /** Campo oculto para los formularios POST */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    /** Compara el token recibido con el de la sessión, en tiempo constante */
    public static function verify(?string $token): bool
    {
        $expected = Session::get(self::KEY);

        return is_string($expected) && $token !== null && hash_equals($expected, $token);
    }
}