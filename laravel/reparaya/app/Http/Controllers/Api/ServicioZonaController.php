<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServicioZonaResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

final class ServicioZonaController extends Controller
{
  
    public function index(): AnonymousResourceCollection
    {
        $rows = DB::table('incidents')
            ->leftJoin('communities', 'incidents.community_id', '=', 'communities.id')
            ->leftJoin('zones', 'communities.zone_id', '=', 'zones.id')
            ->where('incidents.status', IncidentStatus::Finished->value)
            ->selectRaw("COALESCE(zones.name, 'Sin zona') as zona")
            ->selectRaw('COUNT(incidents.id) as servicios_realizados')
            ->groupByRaw("COALESCE(zones.name, 'Sin zona')")
            ->orderByDesc('servicios_realizados')
            ->orderBy('zona')
            ->get();

        $total = (int) $rows->sum('servicios_realizados');

        $rowsWithPercentage = $rows->map(function (object $row) use ($total): object {
            $row->porcentaje_total = $total > 0
                ? round(((int) $row->servicios_realizados / $total) * 100, 2)
                : 0.0;

            return $row;
        });

        return ServicioZonaResource::collection($rowsWithPercentage)->additional([
            'total_servicios_realizados' => $total,
        ]);
    }
}