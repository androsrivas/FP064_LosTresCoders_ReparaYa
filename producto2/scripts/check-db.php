<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/core/Autoload.php';
require APP_PATH . '/core/Helpers.php';

App\Core\Env::load(BASE_PATH . '/.env');
App\Core\Database::connection()->query('SELECT 1');
echo "OK: conexión correcta.\n";