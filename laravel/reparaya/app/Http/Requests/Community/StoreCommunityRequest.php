<?php

declare(strict_types=1);

namespace App\Http\Requests\Community;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityRequest extends FormRequest
{
    protected $errorBag = 'createCommunity';

    /**
     * Determina si el usuario actual está autorizado a crear una comunidad.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasRole(UserRole::PropertyManager)
            && $user->managementCompany !== null;
    }

    /**
     * Reglas de validación aplicables a la creación de una comunidad.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:2', 'max:255'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'    => 'El nombre de la comunidad es obligatorio.',
            'name.min'         => 'El nombre de la comunidad debe tener al menos 2 caracteres.',
            'name.max'         => 'El nombre de la comunidad no puede superar los 255 caracteres.',
            'address.required' => 'La dirección de la comunidad es obligatoria.',
            'address.min'      => 'La dirección debe tener al menos 5 caracteres.',
            'address.max'      => 'La dirección no puede superar los 255 caracteres.',
            'zone_id.required' => 'Debes seleccionar una zona.',
            'zone_id.exists'   => 'La zona seleccionada no es válida.',
        ];
    }
}