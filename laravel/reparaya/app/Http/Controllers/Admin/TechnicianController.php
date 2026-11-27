<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TechnicianRequest;
use App\Models\Speciality;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class TechnicianController extends Controller
{
    public function index(): View
    {
        $technicians = Technician::with(['user', 'speciality'])
            ->withCount('incidents')
            ->orderBy('full_name')
            ->get();

        return view('admin.technicians.index', compact('technicians'));
    }

    public function create(): View
    {
        $specialities = Speciality::orderBy('name')->get();

        return view('admin.technicians.create', compact('specialities'));
    }

    public function store(TechnicianRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data): void {
            $user = new User([
                'name'     => $data['full_name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => $data['password'],
            ]);
            $user->role = UserRole::Technician;
            $user->save();

            Technician::create([
                'user_id'       => $user->id,
                'full_name'     => $data['full_name'],
                'speciality_id' => $data['speciality_id'],
                'is_available'  => (bool) ($data['is_available'] ?? false),
            ]);
        });

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', 'Técnico creado correctamente.');
    }

    public function edit(Technician $technician): View
    {
        $technician->load('user');
        $specialities = Speciality::orderBy('name')->get();

        return view('admin.technicians.edit', compact('technician', 'specialities'));
    }

    public function update(TechnicianRequest $request, Technician $technician): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $technician): void {
            $userUpdates = [
                'name'  => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];

            if (!empty($data['password'])) {
                $userUpdates['password'] = $data['password'];
            }

            $technician->user->update($userUpdates);

            $technician->update([
                'full_name'     => $data['full_name'],
                'speciality_id' => $data['speciality_id'],
                'is_available'  => (bool) ($data['is_available'] ?? false),
            ]);
        });

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', 'Técnico actualizado correctamente.');
    }

    public function destroy(Technician $technician): RedirectResponse
    {
        if ($technician->incidents()->exists()) {
            return redirect()
                ->route('admin.technicians.index')
                ->with('error', 'No se puede eliminar el técnico porque tiene incidencias asignadas.');
        }

        DB::transaction(function () use ($technician): void {
            $user = $technician->user;
            $technician->delete();
            $user?->delete();
        });

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', 'Técnico eliminado correctamente.');
    }
}