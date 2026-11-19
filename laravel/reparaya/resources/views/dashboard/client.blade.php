@extends('layouts.app')

@section('title', 'Mi panel')

@section('content')
    <h1 class="h3 mb-4">Hola, {{ auth()->user()->name }}</h1>

    <div class="row g-4">
        {{-- Alta de incidencia --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Nueva solicitud</div>
                <div class="card-body">
                    @include('incidents.partials.form')
                </div>
            </div>
        </div>

        {{-- Resumen de las últimas incidencias --}}
        <div class="col-lg-7">
            <div class="card overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Últimas incidencias</span>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-yellow btn-sm">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentIncidents->isEmpty())
                        <p class="p-3 mb-0 text-white-50">Todavía no has registrado ninguna incidencia.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Referencia</th>
                                        <th>Especialidad</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentIncidents as $incident)
                                    <tr>
                                        <td>{{ $incident->reference }}</td>
                                        <td>{{ $incident->speciality->name ?? '-' }}</td>
                                        <td>{{ $incident->service_date->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge badge-status badge-status-{{ Str::slug($incident->status->value) }}">{{ $incident->status->value }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection