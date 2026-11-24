<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioZonaResource extends JsonResource
{
    /**
     * Transforma cada fila agregada de servicio por zona en un array JSON limpio.
     * No incluye datos personales de clientes, técnicos ni direcciones.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'zona' => (string) $this->zona,
            'servicio' => (string) $this->servicio,
            'total_incidencias' => (int) $this->total_incidencias,
            'porcentaje' => round((float) $this->porcentaje, 2),
        ];
    }
}