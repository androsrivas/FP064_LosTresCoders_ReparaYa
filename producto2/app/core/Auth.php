<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    /** @param array{id: int|string, nombre: string, email: string, rol: string} $usuario */
    public static function login(array $usuario): void
    {
        Session::regenerate();
        Session::set('user', [
            'id' => (int) $usuario['id'],
            'nombre' => (string) $usuario['nombre'],
            'email' => (string) $usuario['email'],
            'rol' => (string) $usuario['rol'],
        ]);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::has('user');
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['rol'] ?? null;
    }

    public static function hasRole(string ...$roles): bool
    {
        return self::check() && in_array(self::role(), $roles, true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::deny(401, '/login', 'Debes iniciar sesión para continuar.');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        if (!self::hasRole(...$roles)) {
            self::deny(403, self::homeFor(self::role()), 'No tienes permiso para acceder a esta sección.');
        }
    }

    public static function homeFor(?string $rol): string
    {
        return match ($rol) {
            Roles::ADMIN => '/admin',
            Roles::TECNICO => '/tecnico/agenda',
            Roles::PARTICULAR => '/particular/avisos',
            default => '/login',
        };
    }

    public static function refresh(array $usuario): void
    {
        $actual = self::user() ?? [];
        Session::set('user', [
            'id' => $actual['id'] ?? (int) $usuario['id'],
            'nombre' => (string) $usuario['nombre'],
            'email' => (string) $usuario['email'],
            'rol' => $actual['rol'] ?? (string) $usuario['rol'],
        ]);
    }

    private static function deny(int $status, string $redirectTo, string $message): never
    {
        $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        if ($wantsJson) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $message]);
            exit;
        }

        Session::addFlash('error', $message);
        header('Location: ' . url($redirectTo));
        exit;
    }
}
