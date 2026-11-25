@extends('layouts.app')

@section('title', 'Gestión de técnicos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Gestión de técnicos</h1>
            <p class="text-muted mb-0">Administra la plantilla de técnicos, sus especialidades y disponibilidad.</p>
        </div>
        <a href="{{ route('admin.technicians.create') }}" class="btn btn-yellow">
            Nuevo técnico
        </a>
    </div>

    @if ($technicians->isEmpty())
        <div class="alert alert-secondary" role="alert">
            Todavía no hay técnicos registrados. Da de alta el primero para poder asignarle incidencias.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Especialidad</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Incidencias</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($technicians as $technician)
                        <tr>
                            <td class="fw-semibold">{{ $technician->full_name }}</td>
                            <td class="small text-muted">{{ $technician->user?->email ?? '—' }}</td>
                            <td class="small">{{ $technician->user?->phone ?? '—' }}</td>
                            <td>{{ $technician->speciality?->name ?? 'Sin especialidad' }}</td>
                            <td class="text-center">
                                @if ($technician->is_available)
                                    <span class="badge bg-success">Disponible</span>
                                @else
                                    <span class="badge bg-secondary">No disponible</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $technician->incidents_count }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.technicians.edit', $technician) }}"
                                        class="btn btn-sm btn-outline-light">
                                        Editar
                                    </a>

                                <form method="POST" action="{{ route('admin.technicians.destroy', $technician) }}"
                                        data-confirm-message="{{ '¿Seguro que deseas eliminar al técnico «' . $technician->full_name . '»?' }}"
                                        onsubmit="return confirm(this.dataset.confirmMessage);">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            @disabled($technician->incidents_count > 0)>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection