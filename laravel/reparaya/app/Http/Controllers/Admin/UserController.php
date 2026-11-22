<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * CRUD de usuarios del admin.
 * Alta y edición solo de administradores; baja y restauración de admins y particulares.
 * Técnicos y gestoras se gestionan en su propio CRUD.
 */
final class UserController extends Controller
{
    /**
     * Listado de usuarios con filtro opcional por rol (?rol=) y por usuarios dados de baja (?bajas=1).
     */
    public function index(Request $request): View
    {
        $selectedRole = UserRole::tryFrom((string) $request->query('rol'));
        $showTrashed = $request->boolean('bajas');

        $users = User::query()
            ->when($selectedRole, fn ($query) => $query->where('role', $selectedRole))
            ->when($showTrashed, fn ($query) => $query->onlyTrashed())
            ->orderBy('name')
            ->get();

        $roles = UserRole::cases();

        return view('admin.users.index', compact('users', 'roles', 'selectedRole', 'showTrashed'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Da de alta un admin. El rol no viene del formulario: este CRUD solo crea admins.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->validated());
        $user->role = UserRole::Admin;
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Administrador creado correctamente.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Modifica un admin. Si la contraseña llega vacía, se mantiene la actual.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Administrador actualizado correctamente.');
    }

    /**
     * Baja lógica (soft delete): se conserva el historial de incidencias y comentarios.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return back()->with('success', "Usuario {$user->name} dado de baja.");
    }

    public function restore(User $user): RedirectResponse
    {
        Gate::authorize('restore', $user);

        $user->restore();

        return back()->with('success', "Usuario {$user->name} restaurado.");
    }
}
