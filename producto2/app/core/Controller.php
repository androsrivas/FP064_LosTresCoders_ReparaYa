<?php
declare(strict_types=1);

namespace App\Core;
abstract class Controller 
{
    /** Carga app/views/<vista>.php dentro del layout (null = sin layout) */
    protected function view (string $view, array $data = [], bool $layout = true): void 
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
        Session::addFlash($type, $message);
    }

    /** Valor de $_POST garantizado como un string (un array o nada a devolver) */
    protected function input(string $key): string
    {
        $v = $_POST[$key] ?? '';
        return is_string($v) ? $v : '';
    }

    protected function requireCsrf(string $backTo): void
    {
        if (!Csrf::verify($this->input('_csrf'))) {
            $this->flash('error', 'El formulario ha caducado o no es válido. Inténtalo de nuevo.');
            $this->redirect($backTo);
        }
    }

}