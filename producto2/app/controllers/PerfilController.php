<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;
use PDOException;
use App\Core\Session;
use App\Services\IncidenciaService;

final class PerfilController extends Controller
{
    public function __construct()
    {
        Auth::requireLogin();
    }

    /** GET /perfil */
    public function edit(): void
    {
        $this->renderEdit();
    }

    /** POST /perfil */
    public function update(): void
    {
        $this->requireCsrf('/perfil');

       $data = [
        'nombre' => trim($this->input('nombre')),
        'email' => trim($this->input('email')),
        'telefono' => trim($this->input('telefono')),
       ];

       $actual = $this->input('password_actual');
       $password = $this->input('password');
       $confirm = $this->input('password_confirm');

       // Si toca cualquiera de los tres campos, se entiende que quiere cambiarla
       $cambiaPassword = $actual !== '' || $password !== '' || $confirm !== '';

       $v = (new Validator($data + [
        'password_actual' => $actual,
        'password' => $password,
        'password_confirm' => $confirm,
       ]))
        ->required('nombre', 'nombre')
        ->max('nombre', 100, 'nombre')
        ->required('email', 'correo')
        ->email('email')
        ->max('email', 100, 'correo')
        ->telefono('telefono');

        if ($cambiaPassword) {
            $v->required('password_actual', 'contraseña actual')
                ->required('password', 'nueva contraseña')
                ->min('password', 8, 'nueva contraseña')
                ->max('password', 72, 'nueva contraseña')
                ->matches('password_confirm', 'password', 'Las contraseñas no coinciden.');

            if ($actual !== '' && !Usuario::verifyPassword((int) Auth::id(), $actual)) {
                $v->add('password_actual', 'La contraseña actual no es correcta.');
            }
        }

        // Unicidad ignorando al propio usuario (se hace en el punto 3)
        if (!isset($v->errors()['email']) && Usuario::emailExists($data['email'], Auth::id())) {
            $v->add('email', 'Ya existe una cuenta con ese correo.');
        }

        if (!$v->fails()) {
            try {
                Usuario::update(
                    (int) Auth::id(), 
                    $data['nombre'], 
                    $data['email'],
                    $cambiaPassword ? $password : null,
                    $data['telefono']
                );

                // Sesión coherente con lo realmente guardado (correo ya normalizado)
                Auth::refresh(Usuario::findById((int) Auth::id()));

                if ($cambiaPassword) {
                    Session::regenerate();
                }

                $this->flash('success', 'Tus datos se han actualizado correctamente.');
                $this->redirect('/perfil');
            } catch(PDOException $e) {
                if (($e->errorInfo[1] ?? 0) !== 1062) {
                    throw $e;
                }

                $v->add('email', 'Ya existe una cuenta con ese correo.');
            }
        }

        $this->renderEdit($v->errors(), $data, 422);
    }  

    private function renderEdit(array $errors = [], array $old = [], int $status = 200): void
    {
        $userId = (int) Auth::id();
        $incidenciaService = new IncidenciaService();
        $incidencias = $incidenciaService->obtenerPorCliente($userId);

        http_response_code($status);
        $this->view('perfil/perfil', [
            'titulo' => 'Mi perfil',
            'usuario' => Usuario::findById($userId),
            'incidencias' => $incidencias,
            'errors' => $errors,
            'old' => $old
        ]);
    }
}