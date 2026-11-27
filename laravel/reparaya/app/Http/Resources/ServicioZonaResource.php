<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ServicioZonaResource extends JsonResource
{
    /**
     * Transforma cada fila agregada de servicios realizados por zona en un array JSON limpio.
     * No incluye datos personales de clientes, técnicos ni direcciones.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'zona' => (string) $this->zona,
            'servicios_realizados' => (int) $this->servicios_realizados,
            'porcentaje_total' => round((float) $this->porcentaje_total, 2),
        ];
    }
}