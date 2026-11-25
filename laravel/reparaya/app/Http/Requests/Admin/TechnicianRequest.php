<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Technician;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Technician|null $technician */
        $technician = $this->route('technician');
        $userId = $technician?->user_id;

        return [
            'full_name'     => ['required', 'string', 'max:100'],
            'email'         => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'         => ['nullable', 'string', 'max:20'],
            'password'      => [
                $technician ? 'nullable' : 'required',
                'string',
                'min:8',
                'max:72',
                'confirmed',
            ],
            'speciality_id' => ['required', 'integer', 'exists:specialities,id'],
            'is_available'  => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required'     => 'El nombre completo del técnico es obligatorio.',
            'email.required'         => 'El correo electrónico es obligatorio.',
            'email.email'            => 'Introduce un correo electrónico válido.',
            'email.unique'           => 'Ya existe un usuario registrado con ese correo electrónico.',
            'password.required'      => 'La contraseña inicial es obligatoria al dar de alta un técnico.',
            'password.min'           => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'     => 'La confirmación de contraseña no coincide.',
            'speciality_id.required' => 'Debes seleccionar una especialidad.',
            'speciality_id.exists'   => 'La especialidad seleccionada no es válida.',
        ];
    }
}