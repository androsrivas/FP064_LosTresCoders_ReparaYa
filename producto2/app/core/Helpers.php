<?php
declare(strict_types=1);

/** Escapa salida HTML (anti-XSS) */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ruta base de la app (funciona en '/' y '/producto2') */
function base_path_url(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $dir === '.' ? '' : $dir;
}

function url(string $path = ''): string
{
    return base_path_url() . '/' . ltrim($path, '/');
}

/** Lee config/<fichero>.php con notación punto: config('database.host') */
function config(string $key, mixed $default = null): mixed
{
    static $cache = [];
    [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($cache[$file])) {
        $path = BASE_PATH . "/config/{$file}.php";
        $cache[$file] = is_file($path) ? require $path : [];
    }
    $value = $cache[$file];
    if ($rest === null) {
        return $value;
    }
    foreach (explode('.', $rest) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function field_errors(array $errors, string $field): string 
{
    $html = '';
    foreach ($errors[$field] ?? [] as $message) {
        $html .= '<p class="field-error" role="alert">' . e($message) . '</p>';
    }

    return $html;
}

function csrf_field(): string
{
    return \App\Core\Csrf::field();
}
