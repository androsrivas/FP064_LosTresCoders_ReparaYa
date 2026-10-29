<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if (strlen($value) >= 2 && $value[0] === $value[-1] && ($value[0] === '"' || $value[0] === "'")) {
                $value = substr($value, 1, -1);
            }

            // El entorno real (AWS) tiene priorida dsobre el .env
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string 
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}
