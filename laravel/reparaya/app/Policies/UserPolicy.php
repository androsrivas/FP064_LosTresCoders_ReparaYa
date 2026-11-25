<?php
declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Qué puede hacer el admin con cada usuario desde el CRUD de usuarios.
 * Técnicos y gestoras se gestionan en su propio CRUD; los particulares se registran ellos mismos.
 */
final class UserPolicy
{
    /**
     * Solo se editan admins desde este CRUD.
     */
    public function update(User $admin, User $target): bool
    {
        return $target->hasRole(UserRole::Admin);
    }

    /**
     * Se pueden dar de baja admins y particulares, pero nunca a uno mismo.
     */
    public function delete(User $admin, User $target): bool
    {
        $roles = [UserRole::Admin, UserRole::Customer];

        if ($admin->is($target)) {
            return false;
        }

        return $target->hasRole(...$roles);
    }

    /**
     * Se puede restaurar a quien se podría dar de baja.
     */
    public function restore(User $admin, User $target): bool
    {
        return $this->delete($admin, $target);
    }
}
