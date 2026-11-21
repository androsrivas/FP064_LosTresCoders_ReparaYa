<?php
declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SpecialityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $specialityId = $this->route('speciality')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('specialities', 'name')->ignore($specialityId),
            ],
            'description' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'string'],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la especialidad es obligatorio.',
            'name.max' => 'El nombre no puede superar los 50 caracteres.',
            'name.unique' => 'Ya existe una especialidad con ese nombre.',
            'base_salary.required' => 'El salario base es obligatorio.',
            'base_salary.numeric' => 'El salario base debe ser un número válido.',
            'base_salary.min' => 'El salario base no puede ser negativo.',
            'base_price.required' => 'El precio base es obligatorio.',
            'base_price.numeric' => 'El precio base debe ser un número válido.',
            'base_price.min' => 'El precio base no puede ser negativo.',
        ];
    }
}