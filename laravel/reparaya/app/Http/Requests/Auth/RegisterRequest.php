<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            // 72: límite bcrypt (como en el P2)
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico no es válido.',
            'email.lowercase' => 'El correo electrónico debe estar en minúsculas',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'phone.max' => 'El teléfono no puede superar los 20 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede superar los 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
