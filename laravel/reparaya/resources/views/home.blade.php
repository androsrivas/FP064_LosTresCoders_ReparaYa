@extends('layouts.app')

@section('title', 'Inicio - ReparaYa')

@section('content')
    {{-- Sección Hero / Banner Principal --}}
    <div class="row align-items-center py-5 my-2">
        {{-- Columna Izquierda: Mensaje principal y botón dinámico --}}
        <div class="col-lg-7 mb-4 mb-lg-0">
            <span class="badge badge-yellow mb-3 px-3 py-2 fs-6">
                🛠️ Servicio de Reparaciones del Hogar
            </span>
            <h1 class="display-4 fw-bold text-white mb-3">
                Soluciones rápidas y profesionales en <span class="text-yellow">ReparaYa</span>
            </h1>
            <p class="lead text-muted mb-4">
                Gestión eficiente de incidencias para fontanería, electricidad, cerrajería y más.
                Conecta con técnicos especializados y realiza el seguimiento de tus servicios en tiempo real.
            </p>

            {{-- Botón dinámico según estado de sesión --}}
            <div class="d-flex flex-wrap gap-3">
                @guest
                    <button type="button" class="btn btn-yellow btn-lg px-4" data-bs-toggle="modal" data-bs-target="#registerModal">
                        Crear cuenta
                    </button>
                @else
                    @if (Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-yellow btn-lg px-4">
                            Ir a Mi Perfil
                        </a>
                    @elseif (Route::has('profile.show'))
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-yellow btn-lg px-4">
                            Ir a Mi Perfil
                        </a>
                    @endif
                @endguest
            </div>
        </div>

        {{-- Columna Derecha: Tarjeta de Solicitud de Servicio --}}
        <div class="col-lg-5 text-center">
            <div class="card p-4 border-0 shadow-lg">
                <div class="card-body py-4">
                    <div class="display-1 text-yellow mb-2">⚡</div>
                    <h3 class="text-white fw-bold mb-2">¿Tienes una urgencia?</h3>
                    <p class="text-muted small mb-4">
                        Respuesta garantizada y asignación prioritaria de técnicos profesionales.
                    </p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-dark border border-secondary text-light">48h Estándar</span>
                        <span class="badge bg-warning text-dark fw-bold">Urgencia Inmediata</span>
                    </div>

                    @auth
                        <button type="button" class="btn btn-yellow w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
                            Solicitar Servicio
                        </button>
                    @else
                        <button type="button" class="btn btn-yellow w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Iniciar Sesión para Solicitar
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5 border-secondary opacity-25">

    {{-- Sección de Ventajas --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <div class="text-yellow fs-2 mb-3">📋</div>
                    <h5 class="card-title text-white fw-bold">Seguimiento Transparente</h5>
                    <p class="card-text text-muted small">
                        Consulta el estado de cada solicitud con códigos de color dinámicos.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <div class="text-yellow fs-2 mb-3">💬</div>
                    <h5 class="card-title text-white fw-bold">Comunicación Directa</h5>
                    <p class="card-text text-muted small">
                        Foro integrado en el detalle de cada servicio para hablar directamente con el técnico.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 p-3">
                <div class="card-body">
                    <div class="text-yellow fs-2 mb-3">📅</div>
                    <h5 class="card-title text-white fw-bold">Agenda & Organización</h5>
                    <p class="card-text text-muted small">
                        Planificación visual diaria, semanal y mensual para la gestión de trabajos.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: NUEVA PETICIÓN DE SERVICIO (reutilizando incidents.partials.form) --}}
    @auth
        <div class="modal fade" id="nuevaIncidenciaModal" tabindex="-1" aria-labelledby="nuevaIncidenciaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="nuevaIncidenciaModalLabel">
                            Solicitar <span class="text-yellow">Nuevo Servicio</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        @include('incidents.partials.form')
                    </div>
                </div>
            </div>
        </div>

        {{-- Reapertura automática del modal si falla la validación al crear la incidencia --}}
        @if ($errors->hasAny(['speciality_id', 'community_id', 'urgency', 'service_date', 'address', 'description']))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modalEl = document.getElementById('nuevaIncidenciaModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                });
            </script>
        @endif
    @endauth
@endsection