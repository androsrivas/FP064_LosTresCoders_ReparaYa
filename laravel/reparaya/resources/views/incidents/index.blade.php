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

    <div class="card overflow-hidden">
        <div class="card-body p-0">
            @include('incidents.partials.table')
        </div>
    </div>

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
