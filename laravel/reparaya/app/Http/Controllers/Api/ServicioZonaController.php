<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServicioZonaResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ServicioZonaController extends Controller
{
    /**
     * Devuelve el resumen agregado de incidencias finalizadas por zona y servicio.
     * Endpoint público para WordPress: GET /api/servicios/zonas
     */
    public function index(): AnonymousResourceCollection
    {
        $filas = DB::table('incidents')
            ->join('specialities', 'incidents.speciality_id', '=', 'specialities.id')
            ->leftJoin('communities', 'incidents.community_id', '=', 'communities.id')
            ->leftJoin('zones', 'communities.zone_id', '=', 'zones.id')
            ->where('incidents.status', IncidentStatus::Finished->value)
            ->selectRaw("COALESCE(zones.name, 'Sin zona') as zona")
            ->addSelect('specialities.name as servicio')
            ->selectRaw('COUNT(incidents.id) as total_incidencias')
            ->groupByRaw("COALESCE(zones.name, 'Sin zona'), specialities.name")
            ->orderBy('zona')
            ->orderByDesc('total_incidencias')
            ->get();

        $totalGlobal = (int) $filas->sum('total_incidencias');

        $filasConPorcentaje = $filas->map(function (object $fila) use ($totalGlobal): object {
            $fila->porcentaje = $totalGlobal > 0
                ? round(((int) $fila->total_incidencias / $totalGlobal) * 100, 2)
                : 0.0;

            return $fila;
        });

        return ServicioZonaResource::collection($filasConPorcentaje)->additional([
            'meta' => [
                'total_incidencias' => $totalGlobal,
            ],
        ]);
    }
}