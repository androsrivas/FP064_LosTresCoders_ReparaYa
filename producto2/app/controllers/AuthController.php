<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Usuario;
use PDOException;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->redirectIfLogged();
        $this->view('auth/login', ['titulo' => 'Iniciar sesión', 'errors' => [], 'old' => []]);
    }

    public function login(): void
    {
        $this->redirectIfLogged();
        $this->requireCsrf('/login');

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

        http_response_code($status);
        $this->view('auth/login', [
            'titulo' => 'Iniciar sesión',
            'errors' => $v->errors(),
            'old' => ['email' => $email],
        ]);
    }

    public function showRegister(): void 
    {
        $this->redirectIfLogged();
        $this->view('auth/registro', ['titulo' => 'Crear cuenta', 'errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $this->redirectIfLogged();
        $this->requireCsrf('/registro');

        $data = [
            'nombre' => trim($this->input('nombre')),
            'email' => trim($this->input('email')),
<<<<<<< HEAD
<<<<<<< HEAD
=======
            'telefono' => trim($this->input('telefono')),
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
            'telefono' => trim($this->input('telefono')),
>>>>>>> origin/main
            'password' => $this->input('password'),
            'password_confirm' => $this->input('password_confirm'),
        ];

        $v = (new Validator($data))
            ->required('nombre', 'nombre')
            ->max('nombre', 100, 'nombre')
            ->required('email', 'correo')
            ->email('email')
            ->max('email', 100, 'correo')
<<<<<<< HEAD
<<<<<<< HEAD
=======
            ->telefono('telefono')
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
            ->telefono('telefono')
>>>>>>> origin/main
            ->required('password', 'contraseña')
            ->min('password', 8, 'contraseña')
            ->max('password', 72, 'contraseña')
            ->matches('password_confirm', 'password', 'Las contraseñas no coinciden.');

        if (!$v->fails() && Usuario::emailExists($data['email'])) {
            $v->add('email', 'Ya existe una cuenta con ese correo.');
        }

        if (!$v->fails()) {
            try {
<<<<<<< HEAD
<<<<<<< HEAD
                $id = Usuario::create($data['nombre'], $data['email'], $data['password']);
=======
                $id = Usuario::create($data['nombre'], $data['email'], $data['password'], telefono: $data['telefono']);
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
                $id = Usuario::create($data['nombre'], $data['email'], $data['password'], telefono: $data['telefono']);
>>>>>>> origin/main
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

        http_response_code(422);
        $this->view('auth/registro', [
            'titulo' => 'Crear cuenta',
            'errors' => $v->errors(),
<<<<<<< HEAD
<<<<<<< HEAD
            'old' => ['nombre' => $data['nombre'], 'email' => $data['email']],
=======
            'old' => ['nombre' => $data['nombre'], 'email' => $data['email'], 'telefono' => $data['telefono']],
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
            'old' => ['nombre' => $data['nombre'], 'email' => $data['email'], 'telefono' => $data['telefono']],
>>>>>>> origin/main
        ]);
    }

    public function logout(): void 
    {
        $this->requireCsrf('/');

        Auth::logout();
        $this->flash('success', 'Has cerrado sesión.');
        $this->redirect('/login');
    }

    private function redirectIfLogged(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::homeFor(Auth::role()));
        }
    }
}