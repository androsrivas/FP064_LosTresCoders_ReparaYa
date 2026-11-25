<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserRequest extends FormRequest
{
    /**
     * Solo el administrador puede dar de alta usuarios desde el panel.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Reglas de alta de un admin. Técnicos y gestoras se dan de alta en su propio CRUD y los particulares se registran ellos mismos.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            // 72: límite de bcrypt (como en el registro).
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.lowercase' => 'El correo electrónico debe estar en minúsculas.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'email.unique' => 'Ya existe un usuario con ese correo (puede estar dado de baja).',
            'phone.max' => 'El teléfono no puede superar los 20 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede superar los 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
