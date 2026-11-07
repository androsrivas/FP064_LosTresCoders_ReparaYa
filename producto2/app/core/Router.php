<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<int, array{regex:string, handler:mixed}>>  */
    private array $routes = [];

    public function get(string $path, mixed $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, mixed $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', rtrim($path, '/') ?: '/') . '$#';
        $this->routes[$method][] = ['regex' => $regex, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->normalize($uri);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($route['handler'], $params);
                return;
            }
        }

        // Si no existe la ruta con otro método -> 405
        foreach ($this->routes as $m => $routes) {
            foreach ($routes as $route) {
                if ($m !== $method && preg_match($route['regex'], $path)) {
                    http_response_code(405);
                    echo '405 - Método no permitido';
                    return;
                }
            }
        }

        http_response_code(404);
        echo '404 - Página no encontrada';
    }

    private function normalize(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = base_path_url();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return '/' . trim($path, '/');
    }

    private function call(mixed $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler(...$params);
            return;
        }

        // 'UsuarioController@login'
        [$class, $action] = explode('@', (string) $handler) + [1 => 'index'];
        $fqcn = 'App\\Controllers\\' . $class;

        if (!class_exists($fqcn) || !method_exists($fqcn, $action)) {
            http_response_code(500);
            echo "Controlador o acción no encontrado: {$class}@{$action}";
            return;
        }
        (new $fqcn())->$action(...$params);
    }
}
