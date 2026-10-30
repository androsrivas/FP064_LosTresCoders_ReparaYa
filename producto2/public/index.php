<?php
declare(strict_types=1);

use App\Core\Env;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/core/Autoload.php';
require APP_PATH . '/core/Helpers.php';

Env::load(BASE_PATH . '/../.env');

Session::start();

use App\Core\Router;

$router = new Router();
require APP_PATH . '/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);