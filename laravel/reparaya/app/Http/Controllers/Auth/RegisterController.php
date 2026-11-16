<?php
declare(strict_types=1);
namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->validated()); // la contraseña la cifra el cast 'hashed'
        $user->role = UserRole::Customer; // explícito: 'role' no es fillable

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            // Dos peticiones simultáneas con el mismo correo: la validación 'unique' no lo puede evitar
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Ya existe una cuenta con ese correo.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route($user->role->homeRoute())
            ->with('success', 'Cuenta creada correctamente.');

    }
}
