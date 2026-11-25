<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Speciality;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    private const RECENT_INCIDENTS = 5;

    /**
     * Panel de inicio de particular y gestora: resumen de sus últimas incidencias y formulario para dar de alta una nueva.
     */
    public function __invoke(): View
    {
        $user = Auth::user();

        $recentIncidents = $user->incidents()
            ->with('speciality')
            ->latest()
            ->limit(self::RECENT_INCIDENTS)
            ->get();

        $specialities = Speciality::orderBy('name')->get();

        // Comunidades de la gestora (colección vacía para particulares)
        $communities = $user->managementCompany?->communities()->orderBy('name')->get()
            ?? collect();

        return view('dashboard.client', compact('recentIncidents', 'specialities', 'communities'));
    }
}
