<?php
declare(strict_types=1);

use App\Core\Env;
<<<<<<< HEAD
<<<<<<< HEAD
=======
use App\Core\Session;
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
use App\Core\Session;
>>>>>>> origin/main

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

<<<<<<< HEAD
<<<<<<< HEAD
require APP_PATH . '/core/autoload.php';
require APP_PATH . '/core/helpers.php';

Env::load(BASE_PATH . '/../.env');

session_start();
=======
=======
>>>>>>> origin/main
require APP_PATH . '/core/Autoload.php';
require APP_PATH . '/core/Helpers.php';

Env::load(BASE_PATH . '/../.env');

Session::start();
<<<<<<< HEAD
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
>>>>>>> origin/main

use App\Core\Router;

$router = new Router();
require APP_PATH . '/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);