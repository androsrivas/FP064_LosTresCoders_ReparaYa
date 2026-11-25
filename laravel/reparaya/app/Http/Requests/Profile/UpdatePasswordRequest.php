<?php
declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePasswordRequest extends FormRequest
{
    /**
     * Bolsa de errores propia: el formulario de contraseñas comparte la página con el de datos
     */
    protected $errorBag = 'updatePassword';
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
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            // 'current_password' comprueba el hash contra el usuario autenticado
            'current_password' => ['required', 'string', 'current_password'],
            // Mismos límites que en RegisterRequest (72: límite bcrypt)
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Introduce tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede superar los 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.different' => 'La nueva contraseña debe ser distinta a la actual.',
        ];
    }
}
