<?php
use App\Core\Env;

return [
    'host'     => Env::get('DB_HOST', 'localhost'),
    'port'     => Env::get('DB_PORT', '3306'),
    'dbname'   => Env::get('DB_DATABASE', ''),
    'user'     => Env::get('DB_USERNAME', ''),
    'password' => Env::get('DB_PASSWORD', ''),
];