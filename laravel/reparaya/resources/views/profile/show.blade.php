@extends('layouts.app')

@section('title', 'Mis Gestiones')

@php
    /** @var \App\Models\User $user */
    $isManager = $user->hasRole(\App\Enums\UserRole::PropertyManager);
    $hasProfileErrors = $errors->hasAny(['name', 'email', 'phone']) || $errors->updatePassword->any();
    $hasIncidentErrors = $errors->hasAny([
        'speciality_id',
        'community_id',
        'urgency',
        'service_date',
        'address',
        'description',
    ]);
@endphp

@section('content')
    <div class="container py-4">
        {{-- Encabezado --}}
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary">
            <h1 class="h3 fw-bold text-light m-0">
                Mis <span class="text-yellow">Gestiones</span>
            </h1>
            <span class="badge badge-status badge-status-pendiente px-3 py-2 fw-bold text-uppercase">
                {{ $user->role->label() }}
            </span>
        </div>

        {{-- Errores de dominio al cancelar --}}
        @error('general')
            <div class="alert alert-danger alert-flash mb-4">{{ $message }}</div>
        @enderror

        <div class="row g-4 align-items-start">

            {{-- COLUMNA IZQUIERDA: Perfil compacto (col-lg-3) --}}
            <div class="col-lg-3">
                <div class="perfil-panel perfil-panel-accent p-3">
                    <div class="perfil-panel-header mb-3">
                        <h2 class="h6 fw-bold mb-0"><span class="text-yellow">Mi Perfil</span></h2>
                    </div>

                    <ul class="list-group mb-3">
                        <li class="list-group-item text-light px-3 py-2">
                            <span class="text-white-50 d-block small">Nombre completo:</span>
                            <span class="fw-semibold">{{ $user->name }}</span>
                        </li>
                        <li class="list-group-item text-light px-3 py-2">
                            <span class="text-white-50 d-block small">Correo electrónico:</span>
                            <span class="fw-semibold text-break small">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item text-light px-3 py-2">
                            <span class="text-white-50 d-block small">Teléfono:</span>
                            <span class="fw-semibold">{{ $user->phone ?? 'No indicado' }}</span>
                        </li>
                        @if ($isManager && $user->managementCompany)
                            <li class="list-group-item text-light px-3 py-2">
                                <span class="text-white-50 d-block small">Gestoría:</span>
                                <span class="fw-semibold text-yellow">{{ $user->managementCompany->name }}</span>
                            </li>
                        @endif
                    </ul>

                    <div class="pt-2 border-top border-secondary text-end">
                        <button type="button" class="btn btn-yellow btn-sm fw-bold w-100"
                            data-bs-toggle="modal" data-bs-target="#editPerfilModal">
                            Editar perfil
                        </button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Dinámica según rol (col-lg-9) --}}
            <div class="col-lg-9">
                @if ($isManager)
                    {{-- Barra superior de secciones + búsqueda (solo Gestora) --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="btn-group" role="group" aria-label="Secciones de gestoría">
                            <button type="button" class="btn btn-yellow btn-sm fw-bold active" data-gestora-tab="incidencias">
                                Incidencias
                            </button>
                            <button type="button" class="btn btn-outline-yellow btn-sm fw-bold" data-gestora-tab="comunidades">
                                Comunidades
                            </button>
                            <button type="button" class="btn btn-outline-yellow btn-sm fw-bold" data-gestora-tab="liquidaciones">
                                Liquidaciones
                            </button>
                        </div>

                        <div class="flex-grow-1" style="max-width: 280px;">
                            <input type="search" id="gestoraSearchInput" class="form-control form-control-sm"
                                placeholder="Buscar en la sección..." autocomplete="off">
                        </div>
                    </div>

                    {{-- Panel dinámico de Gestora --}}
                    <div class="perfil-panel perfil-panel-accent p-4">
                        <div class="perfil-panel-header d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h5 fw-bold mb-0"><span class="text-yellow" id="gestoraSectionTitle">Incidencias de mis comunidades</span></h2>
                            <button type="button" class="btn btn-yellow btn-sm fw-bold"
                                id="gestoraNewBtn" data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
                                + Nueva Incidencia
                            </button>
                        </div>

                        {{-- Pestaña 1: Incidencias --}}
                        <div id="tab-incidencias" data-gestora-panel>
                            @include('incidents.partials.table')
                        </div>

                        {{-- Pestaña 2: Comunidades --}}
                        <div id="tab-comunidades" data-gestora-panel class="d-none">
                            @if ($communities->isEmpty())
                                <p class="text-white-50 text-center py-4 mb-0">No tienes comunidades registradas todavía.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Comunidad</th>
                                                <th>Dirección</th>
                                                <th>CIF</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($communities as $community)
                                                <tr>
                                                    <td class="fw-semibold">{{ $community->name }}</td>
                                                    <td>{{ $community->address ?? '-' }}</td>
                                                    <td>{{ $community->cif ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- Pestaña 3: Liquidaciones --}}
                        <div id="tab-liquidaciones" data-gestora-panel class="d-none">
                            <p class="text-white-50 text-center py-4 mb-0">
                                Sección de liquidaciones lista para conectar con su controlador.
                            </p>
                        </div>
                    </div>
                @else
                    {{-- Vista para Particular (y Técnico/Admin si entran a su perfil): Incidencias asociadas --}}
                    <div class="perfil-panel perfil-panel-accent p-4">
                        <div class="perfil-panel-header d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h5 fw-bold mb-0"><span class="text-yellow">Mis incidencias asociadas</span></h2>
                            <button type="button" class="btn btn-yellow btn-sm fw-bold"
                                data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
                                + Nueva Solicitud
                            </button>
                        </div>

                        @include('incidents.partials.table')
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL 1: EDITAR PERFIL Y CONTRASEÑA --}}
    <div class="modal fade" id="editPerfilModal" tabindex="-1" aria-labelledby="editPerfilModalLabel" aria-hidden="true"
        @if ($hasProfileErrors) data-auto-open="true" @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editPerfilModalLabel">
                        Editar <span class="text-yellow">Perfil</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        {{-- Formulario Datos Personales --}}
                        <div class="col-md-6">
                            <h6 class="text-yellow fw-bold mb-3">Datos personales</h6>
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label small">Nombre</label>
                                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                                        @class(['form-control', 'is-invalid' => $errors->has('name')])>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label small">Correo electrónico</label>
                                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                        @class(['form-control', 'is-invalid' => $errors->has('email')])>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label small">Teléfono <span class="text-white-50">(opcional)</span></label>
                                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}"
                                        @class(['form-control', 'is-invalid' => $errors->has('phone')])>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-yellow btn-sm fw-bold w-100">Guardar datos</button>
                            </form>
                        </div>

                        {{-- Formulario Cambio de Contraseña --}}
                        <div class="col-md-6 border-start border-secondary">
                            <h6 class="text-yellow fw-bold mb-3">Cambiar contraseña</h6>
                            <form method="POST" action="{{ route('profile.password') }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label small">Contraseña actual</label>
                                    <input id="current_password" name="current_password" type="password" required
                                        @class(['form-control', 'is-invalid' => $errors->updatePassword->has('current_password')])>
                                    @error('current_password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label small">Nueva contraseña</label>
                                    <input id="password" name="password" type="password" required
                                        @class(['form-control', 'is-invalid' => $errors->updatePassword->has('password')])>
                                    @error('password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label small">Repite la nueva contraseña</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" required
                                        class="form-control">
                                </div>

                                <button type="submit" class="btn btn-outline-yellow btn-sm fw-bold w-100">Actualizar contraseña</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 2: NUEVA INCIDENCIA --}}
    <div class="modal fade" id="nuevaIncidenciaModal" tabindex="-1" aria-labelledby="nuevaIncidenciaModalLabel" aria-hidden="true"
        @if ($hasIncidentErrors) data-auto-open="true" @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="nuevaIncidenciaModalLabel">
                        Solicitar <span class="text-yellow">Nuevo Servicio</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    @include('incidents.partials.form', ['redirectTo' => 'profile.show'])
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 3: DETALLE DE INCIDENCIA --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="detailModalLabel">Detalle de la incidencia</h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="detailContent">Cargando...</div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/incidents.js') }}"></script>
        <script src="{{ asset('js/profile.js') }}"></script>
    @endpush
@endsection