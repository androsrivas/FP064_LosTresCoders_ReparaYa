<?php

//Funcion para cargar el archivo .env global
if (!function_exists('cargarEnv')) {
    function cargarEnv($rutaEnv) {
        if (!file_exists($rutaEnv)) {
            return;
        }

        $lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (empty($linea) || strpos($linea, '#') === 0) {
                continue;
            }

            if (strpos($linea, '=') !== false) {
                list($clave, $valor) = explode('=', $linea, 2);
                $clave = trim($clave);
                $valor = trim($valor);

                if (!array_key_exists($clave, $_SERVER) && !array_key_exists($clave, $_ENV)) {
                    putenv("{$clave}={$valor}");
                    $_ENV[$clave] = $valor;
                    $_SERVER[$clave] = $valor;
                }
            }
        }
    }
}

// Cargar el .env global/
cargarEnv(__DIR__ . '/../../.env');

// Obtener valores en variables
$host     = getenv('DB_HOST');
$dbname   = getenv('DB_DATABASE');
$user     = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$port     = getenv('DB_PORT');

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}