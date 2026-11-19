@extends('layouts.app')

@section('title', 'Mis incidencias')

@php
    $isManager = auth()->user()->hasRole(\App\Enums\UserRole::PropertyManager);
    $cancellable = [\App\Enums\IncidentStatus::Assigned, App\Enums\IncidentStatus::Pending];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Mis incidencias</h1>
        <a href="{{ route(auth()->user()->role->homeRoute()) }}" class="btn btn-yellow btn-sm">Nueva solicitud</a>
    </div>

    {{-- Errores de dominio al cancelar (DomainException en IncidentController::cancel) --}}
    @error('general')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    @if ($incidents->isEmpty())
        <p class="text-white-50">Todavía no has registrado ninguna incidencia.</p>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Especialidad</th>
                        @if ($isManager)
                            <th>Comunidad</th>
                        @endif
                        <th>Fecha</th>
                        <th>Urgencia</th>
                        <th>Estado</th>
                        <th>Técnico</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidents as $incident)
                        <tr>
                            <td>
                                <strong>{{ $incident->reference }}</strong><br>
                                <small class="text-white-50">{{ Str::limit($incident->description, 40) }}</small>
                            </td>
                            <td>{{ $incident->speciality->name ?? '-' }}</td>
                            @if ($isManager)
                                <td>{{ $incident->community->name ?? '-' }}</td>
                            @endif
                            <td>{{ $incident->service_date->format('d/m/Y H:i') }}</td>
                            <td>
                                <span
                                    class="badge badge-status badge-urgencia-{{ Str::slug($incident->urgency->value) }}">{{ $incident->urgency->value }}</span>
                            </td>
                            <td>
                                <span class="badge badge-status badge-status-{{ Str::slug($incident->status->value) }}">
                                    {{ $incident->status->value }}
                                </span>
                            </td>
                            <td>{{ $incident->technician?->user?->name ?? 'Sin asignar' }}</td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-outline-yellow btn-sm"
                                    data-detail-url="{{ route('incidents.show', $incident) }}">
                                    Ver
                                </button>

                                @if (in_array($incident->status, $cancellable, true))
                                    <form action="{{ route('incidents.cancel', $incident) }}" method="POST"
                                        class="d-inline"
                                        data-confirm="¿Seguro que quieres cancelar la incidencia {{ $incident->reference }}?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal de detalle: el contenido se carga por AJAX desde incidents.show --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="detailModalLabel">Detalle de la incidencia</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="detailContent">Cargando...</div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/incidents.js') }}"></script>
    @endpush
@endsection
