@extends('layouts.app')

@section('title', 'Especialidades y tarifas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Especialidades y tarifas</h1>
            <p class="text-muted mb-0">Gestiona los tipos de servicio, salarios base y precios de referencia.</p>
        </div>
        <a href="{{ route('admin.specialities.create') }}" class="btn btn-yellow">
            Nueva especialidad
        </a>
    </div>

    @if ($specialities->isEmpty())
        <div class="alert alert-secondary" role="alert">
            Todavía no hay especialidades registradas. Crea la primera para poder asignarla a los técnicos.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-end">Salario base</th>
                        <th class="text-end">Precio base</th>
                        <th class="text-center">Técnicos</th>
                        <th class="text-center">Incidencias</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($specialities as $speciality)
                        @php
                            $cannotDelete = $speciality->technicians_count > 0 || $speciality->incidents_count > 0;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $speciality->name }}</td>
                            <td class="small text-muted">
                                {{ \Illuminate\Support\Str::limit($speciality->description ?? 'Sin descripción', 60) }}
                            </td>
                            <td class="text-end">{{ number_format((float) $speciality->base_salary, 2, ',', '.') }} €</td>
                            <td class="text-end">{{ number_format((float) $speciality->base_price, 2, ',', '.') }} €</td>
                            <td class="text-center">{{ $speciality->technicians_count }}</td>
                            <td class="text-center">{{ $speciality->incidents_count }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.specialities.edit', $speciality) }}"
                                        class="btn btn-sm btn-outline-light">
                                        Editar
                                    </a>

                                    <form method="POST" action="{{ route('admin.specialities.destroy', $speciality) }}"
                                        data-confirm-message="{{ '¿Seguro que deseas eliminar la especialidad «' . $speciality->name . '»?' }}"
                                        onsubmit="return confirm(this.dataset.confirmMessage);">
                                        @csrf
                                        @method('DELETE')
                                        <span @if ($cannotDelete) title="No se puede eliminar mientras tenga técnicos o incidencias asociadas" @endif>
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                @disabled($cannotDelete)>
                                                Eliminar
                                            </button>
                                        </span>
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