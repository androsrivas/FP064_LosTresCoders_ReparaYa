<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Urgency;
use App\Enums\UserRole;
use App\Services\IncidentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    /**
     * Determina si el usuario actual está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Reglas de validación aplicables a la creación de una incidencia.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isGestora = $user?->hasRole(UserRole::PropertyManager) ?? false;
        $managementCompanyId = $user?->managementCompany?->id;

        $isUrgent = $this->input('urgency') === Urgency::Urgent->value;

        $minDateRule = $isUrgent
            ? 'after:now'
            : 'after_or_equal:' . now()->addHours(IncidentService::MIN_HOURS_ADVANCE)->toDateTimeString();

        return [
            'speciality_id' => ['required', 'integer', 'exists:specialities,id'],
            'community_id'  => $isGestora
                ? [
                    'required',
                    'integer',
                    Rule::exists('communities', 'id')->where(
                        fn ($query) => $query->where('management_company_id', $managementCompanyId)
                    ),
                ]
                : ['prohibited'],
            'description'   => ['required', 'string', 'min:10', 'max:1000'],
            'urgency'       => ['required', Rule::enum(Urgency::class)],
            'service_date'  => ['required', 'date', $minDateRule],
            'address'       => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $hours = IncidentService::MIN_HOURS_ADVANCE;

        return [
            'speciality_id.required'      => 'Debes seleccionar una especialidad técnica.',
            'speciality_id.exists'        => 'La especialidad seleccionada no es válida.',
            'community_id.exists'         => 'La comunidad seleccionada no es válida.',
            'community_id.required'       => 'Debes seleccionar una comunidad.',
            'community_id.prohibited'     => 'Un cliente particular no puede asociar una comunidad a la incidencia.',
            'description.required'        => 'La descripción de la incidencia es obligatoria.',
            'description.min'             => 'Describe el problema con al menos 10 caracteres.',
            'description.max'             => 'La descripción no puede superar los 1000 caracteres.',
            'urgency.required'            => 'Debes indicar el nivel de urgencia.',
            'urgency.enum'                => 'El nivel de urgencia seleccionado no es válido.',
            'service_date.required'       => 'La fecha y hora preferida del servicio son obligatorias.',
            'service_date.date'           => 'El formato de la fecha del servicio no es válido.',
            'service_date.after'          => 'Para incidencias urgentes, la fecha y hora del servicio deben ser posteriores al momento actual.',
            'service_date.after_or_equal' => "Las incidencias estándar deben solicitarse con al menos {$hours} horas de antelación.",
            'address.required'            => 'La dirección de intervención es obligatoria.',
            'address.min'                 => 'La dirección debe tener al menos 5 caracteres.',
            'address.max'                 => 'La dirección no puede superar los 255 caracteres.',
        ];
    }
}