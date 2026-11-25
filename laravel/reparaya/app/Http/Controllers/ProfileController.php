<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Models\Incident;
use App\Models\Speciality;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

/**
 * Perfil del usuario autenticado (cualquier rol).
 * Siempre se trabaja sobre $request->user(): no hay {id} en la URL, así un usuario nunca puede ver ni editar el perfil de otro.
 */
final class ProfileController extends Controller
{
    public function show(Request $request): View 
    {
        $user = $request->user();
        $isManager = $user->hasRole(UserRole::PropertyManager);

        $communities = $isManager
            ? ($user->managementCompany?->communities()->with('zone')->orderBy('name')->get() ?? collect())
            : collect();

        $zones = $isManager
            ? Zone::orderBy('name')->get()
            : collect();

        $commissions = $isManager
            ? ($user->managementCompany?->commissions()
                ->with(['incident.community', 'incident.speciality'])
                ->orderByDesc('finished_at')
                ->get() ?? collect())
            : collect();

        $monthlySettlements = $commissions
            ->filter(fn ($commission) => $commission->finished_at !== null)
            ->groupBy(fn ($commission) => $commission->finished_at->format('Y-m'))
            ->map(function ($items, string $yearMonth) {
                $date = Carbon::createFromFormat('Y-m', $yearMonth)->locale('es');

                return [
                    'period' => $yearMonth,
                    'label' => ucfirst($date->translatedFormat('F Y')),
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ];
            })
            ->values();

        $incidentsQuery = Incident::with(['speciality', 'community', 'technician.user'])
            ->latest();

        if ($isManager) {
            $incidentsQuery->whereIn('community_id', $communities->pluck('id'));
        } else {
            $incidentsQuery->where('client_id', $user->id);
        }

        $incidents = $incidentsQuery->get();
        $specialities = Speciality::orderBy('name')->get();

        return view('profile.show', compact(
            'user', 
            'incidents', 
            'communities', 
            'specialities', 
            'zones', 
            'commissions', 
            'monthlySettlements'
        ));
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Perfil actualizado.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        // El cast 'password' => 'hashed' del modelo User aplica el hash
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('profile.show')->with('success', 'Contraseña actualizada.');
    }
}
