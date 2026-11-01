<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

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

        // TODO punto 2: validaciones
        // TODO punto 3: Usuario::updatePerfil(...)
        // TODO punto 4: Auth::refresh([...]) + flash

        $this->redirect('/perfil');
    }

    /** POST /perfil/password */
    public function updatePassword(): void
    {
        $this->requireCsrf('/perfil');

        $actual = $this->input('password_actual');
        $nueva = $this->input('password_nueva');
        $confirmar = $this->input('password_confirmacion');

        // TODO punto 2: validaciones
        // TODO punto 3: Usuario::updatePassword(...)
        // TODO punto 4: Session::regenerate() + flash

        $this->redirect('/perfil');
    }
}