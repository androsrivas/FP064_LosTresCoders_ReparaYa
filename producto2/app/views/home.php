<?php
// producto2/app/views/home.php

require_once __DIR__ . '/layouts/header.php';

$isLoggedIn = isset($_SESSION['usuario_id']);
$rol        = $_SESSION['usuario_rol'] ?? null;
?>

<!-- Sección Hero / Banner Principal -->
<div class="row align-items-center py-5 my-2">
    <!-- Columna Izquierda: Mensaje principal y botón dinámico -->
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

        <!-- Botón que cambia o desaparece si hay sesión -->
        <div class="d-flex flex-wrap gap-3">
            <?php if (!$isLoggedIn): ?>
                <button type="button" class="btn btn-yellow btn-lg px-4" data-bs-toggle="modal" data-bs-target="#registroModal">
                    Crear cuenta
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Columna Derecha: Tarjeta de Solicitud de Servicio -->
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

                <!-- Botón para abrir el Modal de Nueva Incidencia -->
                 <?php if ($isLoggedIn): ?>
                    <button type="button" class="btn btn-yellow w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
                    Solicitar Servicio
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<hr class="my-5 border-secondary opacity-25">

<!-- Sección de Ventajas -->
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

<!-- ==========================================
     MODAL 1: REGISTRO DE USUARIOS (CREAR CUENTA)
     ========================================== -->
<div class="modal fade" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="registroModalLabel">Crear Cuenta en <span class="text-yellow">ReparaYa</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=registro" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reg_nombre" class="form-label text-muted small">Nombre completo</label>
                        <input type="text" name="nombre" id="reg_nombre" class="form-control" required placeholder="Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label for="reg_email" class="form-label text-muted small">Correo Electrónico</label>
                        <input type="email" name="email" id="reg_email" class="form-control" required placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-3">
                        <label for="reg_telefono" class="form-label text-muted small">Teléfono</label>
                        <input type="tel" name="telefono" id="reg_telefono" class="form-control" placeholder="600000000">
                    </div>
                    <div class="mb-3">
                        <label for="reg_password" class="form-label text-muted small">Contraseña</label>
                        <input type="password" name="password" id="reg_password" class="form-control" required placeholder="******">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-yellow btn-sm">Registrarse</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL 2: NUEVA PETICIÓN DE SERVICIO (INCIDENCIA)
     ========================================== -->
<div class="modal fade" id="nuevaIncidenciaModal" tabindex="-1" aria-labelledby="nuevaIncidenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="nuevaIncidenciaModalLabel">Solicitar <span class="text-yellow">Nuevo Servicio</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=crear_incidencia" method="POST">
                <div class="modal-body">
                    
                    <div class="row g-3">
                        <!-- Especialidad -->
                        <div class="col-md-6">
                            <label for="especialidad_id" class="form-label text-muted small">Especialidad del servicio</label>
                            <select name="especialidad_id" id="especialidad_id" class="form-select" required>
                                <option value="" disabled selected>Selecciona una opción...</option>
                                <option value="1">Fontanería</option>
                                <option value="2">Electricidad</option>
                                <option value="3">Cerrajería</option>
                                <option value="4">Climatización</option>
                            </select>
                        </div>

                        <!-- Tipo de Urgencia -->
                        <div class="col-md-6">
                            <label for="tipo_urgencia" class="form-label text-muted small">Tipo de urgencia</label>
                            <select name="tipo_urgencia" id="tipo_urgencia" class="form-select" required>
                                <option value="Estándar" selected>Estándar (Atención regular)</option>
                                <option value="Urgente">Urgente (Prioridad alta)</option>
                            </select>
                        </div>

                        <!-- Fecha del servicio -->
                        <div class="col-md-6">
                            <label for="fecha_servicio" class="form-label text-muted small">Fecha y hora deseada</label>
                            <input type="datetime-local" name="fecha_servicio" id="fecha_servicio" class="form-control" required>
                        </div>

                        <!-- Dirección -->
                        <div class="col-md-6">
                            <label for="direccion" class="form-label text-muted small">Dirección del servicio</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" required placeholder="Calle, número, piso, ciudad">
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcion" class="form-label text-muted small">Descripción del problema</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="form-control" required placeholder="Detalla brevemente lo que necesita ser reparado..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-yellow btn-sm">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/layouts/footer.php'; 
?>