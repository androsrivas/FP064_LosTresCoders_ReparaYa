<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    /**
     * Muestra la pantalla de Mi Perfil con los datos del usuario y sus incidencias.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $incidents = $user->incidents()
            ->with(['speciality', 'technician.user', 'community'])
            ->orderByDesc('created_at')
            ->get();

        return view('profile.edit', compact('user', 'incidents'));
    }

    /**
     * Actualiza los datos personales y, opcionalmente, la contraseña del usuario.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $data = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'phone' => isset($validated['phone']) && trim($validated['phone']) !== ''
                ? trim($validated['phone'])
                : null,
        ];

        $passwordChanged = !empty($validated['password']);

        if ($passwordChanged) {
            // El cast 'password' => 'hashed' del modelo User lo hashea automáticamente
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        if ($passwordChanged) {
            $request->session()->regenerate();
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Tus datos se han actualizado correctamente.');
    }
}