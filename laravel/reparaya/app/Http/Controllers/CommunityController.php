<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Community\StoreCommunityRequest;
use App\Http\Requests\Community\UpdateCommunityRequest;
use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function store(StoreCommunityRequest $request): RedirectResponse
    {
        $request->user()->managementCompany->communities()->create($request->validated());

        return redirect()
            ->route('profile.show')
            ->with('active_tab', 'comunidades')
            ->with('success', 'Comunidad registrada correctamente.');
    }

    public function update(UpdateCommunityRequest $request, Community $community): RedirectResponse
    {
        $community->update($request->validated());

        return redirect()
            ->route('profile.show')
            ->with('active_tab', 'comunidades')
            ->with('success', 'Comunidad actualizada correctamente.');
    }

    public function destroy(Request $request, Community $community): RedirectResponse
    {
        $company = $request->user()->managementCompany;
        abort_unless($company !== null && $community->management_company_id === $company->id, 403);

        if ($community->incidents()->exists()) {
            return redirect()
                ->route('profile.show')
                ->with('active_tab', 'comunidades')
                ->withErrors(['general' => 'No se puede eliminar la comunidad porque tiene incidencias asociadas.']);
        }

        $community->delete();

        return redirect()
            ->route('profile.show')
            ->with('active_tab', 'comunidades')
            ->with('success', 'Comunidad eliminada correctamente.');
    }
}