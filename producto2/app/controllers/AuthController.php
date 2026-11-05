<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Usuario;
use PDOException;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->redirectIfLogged();
        $this->redirect('/');
    }

    public function login(): void
    {
        // Mantengo la variable $status para posibles usos a futuro, pero debido al cambio a redireccion 
        // de cabecera, algunos navegadores necesitan recibir por narices '302 Found' o puede dar complicaciones
        $this->redirectIfLogged();
        $this->requireCsrf('/');

        $email = trim($this->input('email'));
        $password = $this->input('password');

        $v = (new Validator(['email' => $email, 'password' => $password]))
            ->required('email', 'correo')
            ->email('email')
            ->required('password', 'contraseña');

        $status = 422;

        if (!$v->fails()) {
            $usuario = Usuario::verifyCredentials($email, $password);

            if ($usuario !== null) {
                Auth::login($usuario);
                $this->flash('success', '¡Hola, ' . $usuario['nombre'] . '!');
                $this->redirect(Auth::homeFor($usuario['rol']));
            }

            // Mensaje genérico: no revela si el correo existe.
            $v->add('general', 'Correo o contraseña incorrectos.');
            $status = 401;
        }

        // Guardar errores y 'old' en Session para enviarlos al modal
        \App\Core\Session::set('errors', $v->errors());
        \App\Core\Session::set('old', ['email' => $email]);
        $this->redirect('/');
    }

    public function showRegister(): void 
    {
        $this->redirectIfLogged();
        $this->view('auth/registro', ['titulo' => 'Crear cuenta', 'errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $this->redirectIfLogged();
        $this->requireCsrf('/');

        $data = [
            'nombre' => trim($this->input('nombre')),
            'email' => trim($this->input('email')),
            'telefono' => trim($this->input('telefono')),
            'password' => $this->input('password'),
            'password_confirm' => $this->input('password_confirm'),
        ];

        $v = (new Validator($data))
            ->required('nombre', 'nombre')
            ->max('nombre', 100, 'nombre')
            ->required('email', 'correo')
            ->email('email')
            ->max('email', 100, 'correo')
            ->telefono('telefono')
            ->required('password', 'contraseña')
            ->min('password', 8, 'contraseña')
            ->max('password', 72, 'contraseña')
            ->matches('password_confirm', 'password', 'Las contraseñas no coinciden.');

        if (!$v->fails() && Usuario::emailExists($data['email'])) {
            $v->add('email', 'Ya existe una cuenta con ese correo.');
        }

        if (!$v->fails()) {
            try {
                $id = Usuario::create($data['nombre'], $data['email'], $data['password'], telefono: $data['telefono']);
                $usuario = Usuario::findById($id);

                Auth::login($usuario);
                $this->flash('success', 'Cuenta creada correctamente. ¡Hola!');
                $this->redirect(Auth::homeFor($usuario['rol']));
            } catch (PDOException $e) {
                // 1062 = clave duplicada (otra petición registró ese correo justo antes)
                if (($e->errorInfo[1] ?? 0) !== 1062) {
                    throw $e;
                }
                $v->add('email', 'Ya existe una cuenta con ese correo.');
            }
        }

        \App\Core\Session::set('errors', $v->errors());
        \App\Core\Session::set('old', [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'telefono' => $data['telefono']
        ]);
        $this->redirect('/');
    }

    public function logout(): void 
    {
        $this->requireCsrf('/');

        Auth::logout();
        $this->flash('success', 'Has cerrado sesión.');
        $this->redirect('/');
    }

    private function redirectIfLogged(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::homeFor(Auth::role()));
        }
    }
}