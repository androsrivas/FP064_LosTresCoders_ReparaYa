<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    
    $parts = explode('\\', substr($class, strlen($prefix)));
    $className = array_pop($parts);
    $dir = strtolower(implode('/', $parts));
    $file = APP_PATH . '/' . ($dir !== '' ? $dir . '/' : '') . $className . '.php';

    if (is_file($file)) {
        require $file;
    }
});
