<?php
declare(strict_types=1);

use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/core/autoload.php';
require APP_PATH . '/core/helpers.php';

Env::load(BASE_PATH . '/../.env');

session_start();

use App\Core\Router;

$router = new Router();
require APP_PATH . '/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);