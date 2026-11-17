<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\s()-]{6,20}$/'],

            // Solo se valida si el usuario rellena "password" o "password_confirmation"
            // (ignora autocompletados accidentales del navegador en "current_password")
            'current_password' => [
                'exclude_without:password,password_confirmation',
                'required',
                'current_password',
            ],
            'password' => [
                'nullable',
                'required_with:password_confirmation',
                'string',
                'min:8',
                'max:72',
                'confirmed',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre completo es obligatorio.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 100 caracteres.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'current_password.required' => 'Debes indicar tu contraseña actual para cambiarla.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required_with' => 'Debes indicar la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La nueva contraseña no puede superar los 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}