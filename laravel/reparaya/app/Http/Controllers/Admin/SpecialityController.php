<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecialityRequest;
use App\Models\Speciality;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class SpecialityController extends Controller
{
    public function index(): View
    {
        $specialities = Speciality::withCount(['technicians', 'incidents'])
            ->orderBy('name')
            ->get();

        return view('admin.specialities.index', compact('specialities'));
    }

    public function create(): View
    {
        return view('admin.specialities.create');
    }

    public function store(SpecialityRequest $request): RedirectResponse
    {
        Speciality::create($request->validated());

        return redirect()
            ->route('admin.specialities.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function edit(Speciality $speciality): View
    {
        return view('admin.specialities.edit', compact('speciality'));
    }

    public function update(SpecialityRequest $request, Speciality $speciality): RedirectResponse
    {
        $speciality->update($request->validated());

        return redirect()
            ->route('admin.specialities.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Speciality $speciality): RedirectResponse
    {
        if ($speciality->technicians()->exists() || $speciality->incidents()->exists()) {
            return redirect()
                ->route('admin.specialities.index')
                ->with('error', 'No se puede eliminar la especialidad porque tiene técnicos o incidencias asociadas.');
        }

        $speciality->delete();

        return redirect()
            ->route('admin.specialities.index')
            ->with('success', 'Especialidad eliminada correctamente.');
    }
}