<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller 
{
    /** Carha app/views/<vista>.php dentro del layout (null = sin layout) */
    protected function view (string $view, array $data = [], ?string $layout = 'layout/main'): void 
    {
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo "Vista no encontrada";
            return;
        }

        extract($data, EXTR_SKIP);

        if ($layout) {
            require APP_PATH . '/views/layouts/header.php';
            require APP_PATH . '/views/layouts/menu.php';
        }

        require $viewFile;

        if ($layout) {
            require APP_PATH . '/views/layouts/footer.php';
        }
    }

    protected function redirect(string $path): never 
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function json(mixed $data, int $status = 200): never 
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function flash(string $type, string $message): void 
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }
}