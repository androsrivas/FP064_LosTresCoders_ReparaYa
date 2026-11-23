<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Perfil del usuario autenticado (cualquier rol).
 * Siempre se trabaja sobre $request->user(): no hay {id} en la URL, así un usuario nunca puede ver ni editar el perfil de otro.
 */
final class ProfileController extends Controller
{
    public function show(Request $request): View 
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Perfil actualizado.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        // El cast 'password' => 'hashed' del modelo User aplica el hash
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('profile.show')->with('success', 'Contraseña actualizada.');
    }
}
