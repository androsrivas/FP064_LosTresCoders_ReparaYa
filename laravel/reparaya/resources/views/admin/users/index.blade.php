@extends('layouts.app')

@section('title', 'Usuarios')

@php
// CRUD propio de cada rol que no se gestiona aquí (si la ruta ya existe)
$externalCrud = [
    \App\Enums\UserRole::Technician->value => 'admin.technicians.index',
    \App\Enums\UserRole::PropertyManager->value => 'admin.management-companies.index',
];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Usuarios</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-yellow btn-sm">Nuevo administrador</a>
    </div>

    {{-- Filtros: se envían por GET para que la URL se pueda compartir y recargar --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="rol" class="form-label small mb-1">Rol</label>
            <select name="rol" id="rol" class="form-select form-select-sm">
                <option value="">Todos</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected($selectedRole === $role)>{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <div class="form-check mb-1">
                <input type="checkbox" name="bajas" value="1" id="bajas" class="form-check-input" @checked($showTrashed)>
                <label for="bajas" class="form-check-label small">Solo dados de baja</label>
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-yellow btn-sm">Filtrar</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="card-body p-0">
            @if ($users->isEmpty())
                <p class="p-3 mb-0 text-white-50">No hay usuarios que coincidan con el filtro.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Alta</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        {{ $user->name }}
                                        @if ($user->is(auth()->user()))
                                            <small class="text-white-50">(tú)</small>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $user->role->label() }}</span></td>
                                    <td>{{ $user->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="text-end text-nowrap">
                                        @if ($user->trashed())
                                            @can('restore', $user)
                                                <form action="{{ route('admin.users.restore', $user) }}" method="POST"
                                                    class="d-inline"
                                                    data-confirm="¿Restaurar al usuario {{ $user->name }}?">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-yellow btn-sm">Restaurar</button>
                                                </form>
                                            @endcan
                                        @else
                                            @can('update', $user)
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-yellow btn-sm">Editar</a>
                                            @endcan

                                            @php($crudRoute = $externalCrud[$user->role->value] ?? null)
                                            @if ($crudRoute && Route::has($crudRoute))
                                                <a href="{{ route($crudRoute) }}" class="btn btn-outline-light btn-sm">
                                                    Gestionar en {{ $user->role === \App\Enums\UserRole::Technician ? 'Técnicos' : 'Gestoras' }}
                                                </a>
                                            @endif

                                            @can('delete', $user)
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                    class="d-inline"
                                                    data-confirm="¿Dar de baja al usuario {{ $user->name }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Dar de baja</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection