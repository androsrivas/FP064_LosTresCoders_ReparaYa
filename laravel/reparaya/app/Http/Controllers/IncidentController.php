<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Urgency;
use App\Models\Incident;
use App\Models\Speciality;
use App\Services\IncidentService;
use App\Http\Requests\StoreIncidentRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class IncidentController extends Controller
{
    // Inyección de dependencias por constructor (igual que en Spring)
    public function __construct(
        private readonly IncidentService $incidentService
    ) {
    }

    /**
     * Listado de incidencias del cliente autenticado.
     */
    public function index(): View
    {
        $incidents = Incident::with(['speciality', 'technician.user', 'community'])
            ->where('client_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('incidents.index', compact('incidents'));
    }

    /**
     * Formulario de alta de nueva incidencia.
     */
    public function create(): View
    {
        $specialities = Speciality::orderBy('name')->get();
        $urgencies = Urgency::cases();

        return view('incidents.create', compact('specialities', 'urgencies'));
    }

    /**
     * Guarda una nueva incidencia validando los datos y delegando en IncidentService.
     */
    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        try {
            $this->incidentService->createForClient(Auth::user(), $request->validated());

            return redirect()
                ->route(Auth::user()->role->homeRoute())
                ->with('success', 'Incidencia registrada correctamente.');
        } catch (DomainException $e) {
            return back()
                ->withInput()
                ->withErrors(['service_date' => $e->getMessage()]);
        }
    }

    /**
     * Detalle de una incidencia (pensado para cargarlo en el modal AJAX).
     */
    public function show(Incident $incident): View
    {
        // Evitar IDOR: solo el cliente dueño de la incidencia puede verla aquí
        abort_if($incident->client_id !== Auth::id(), 403);

        $incident->load([
            'speciality',
            'community',
            'technician.user',
            'statusLogs.user',
            'comments.user',
        ]);

        return view('incidents.partials.detail', compact('incident'));
    }

    /**
     * Cancela una incidencia si cumple el estado y las 48h de antelación.
     */
    public function cancel(Incident $incident): RedirectResponse
    {
        abort_if($incident->client_id !== Auth::id(), 403);

        try {
            $this->incidentService->cancelByClient($incident, Auth::user());

            return back()->with('success', 'Incidencia cancelada correctamente.');
        } catch (DomainException $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }
}