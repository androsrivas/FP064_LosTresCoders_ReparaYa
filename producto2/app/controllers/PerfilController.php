<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;
use PDOException;

final class PerfilController extends Controller
{
    public function __construct()
    {
        Auth::requireLogin();
    }

    /** GET /perfil */
    public function edit(): void
    {
        $this->view('perfil/edit', [
            'usuario' => Auth::user(),
        ]);
    }

    /** POST /perfil: nombre y correo */
    public function update(): void
    {
        $this->requireCsrf('/perfil');

        $nombre = trim($this->input('nombre'));
        $email = trim($this->input('email'));

       $data = [
        'nombre' => trim($this->input('nombre')),
        'email' => trim($this->input('email')),
       ];

       $v = (new Validator($data))
        ->required('nombre', 'nombre')
        ->max('nombre', 100, 'nombre')
        ->required('email', 'correo')
        ->email('email')
        ->max('email', 100, 'correo');

        // Unicidad ignorando al propio usuario (se hace en el punto 3)
        if (!$v->fails() && Usuario::emailExists($data['email'], Auth::id())) {
            $v->add('email', 'Ya existe una cuenta con ese correo.');
        }

        if (!$v->fails()) {
            try {
                Usuario::update(Auth::id(), $data['nombre'], $data['email']);

                // TODO punto 4: Auth::refresh(Usuario::findById(Auth::id())) + flash('success', ...) + redirect('/perfil')
            } catch(PDOException $e) {
                // 1062 = otra cuenta se quedó ese correo justo antes
                if (($e->errorInfo[1] ?? 0) !== 1062) {
                    throw $e;
                }

                $v->add('email', 'Ya existe una cuenta con ese correo.');
            }
        }

        $this->redirect('/perfil');
    }

    /** POST /perfil/password */
    public function updatePassword(): void
    {
        $this->requireCsrf('/perfil');

        $actual = $this->input('password_actual');
        $nueva = $this->input('password_nueva');

        $v = (new Validator([
            'password_actual' => $actual,
            'password_nueva' => $nueva,
            'password_confirm' => $this->input('password_confirm'),
        ]))
            ->required('password_actual', 'contraseña actual')
            ->required('password_nueva', 'nueva contraseña')
            ->min('password_nueva', 8, 'nueva contraseña')
            ->max('password_nueva', 72, 'nueva contraseña')
            ->matches('password_confirm', 'password_nueva', 'Las contraseñas no coinciden.');

        if ($actual !== '' && Usuario::verifyPassword(Auth::id(), $actual)) {
            $v->add('password_actual', 'La contraseña acutal no es correcta.');
        }
        
        if (!$v->fails()) {
            Usuario::updatePassword(Auth::id(), $nueva);
             
            // TODO punto 4: Session::regenerate() + flash('success', ...) + redirect('/pefil')
        }

        // Nunca se devuelven contraseñas en old
        $this->renderEdit($v->errors(), [], 422);
    }

    private function renderEdit(array $errors = [], array $old = [], int $status = 200): void
    {
        http_response_code($status);
        $this->view('perfil/edit', [
            'titulo' => 'Mi perfil',
            'usuario' => Auth::user(),
            'errors' => $errors,
            'old' => $old
        ]);
    }
}