<?php
declare(strict_types=1);

use App\Core\Auth;

// Recuperación de errores y valores previos para los modales
$errors = $errors ?? \App\Core\Session::get('errors', []);
$old = $old ?? \App\Core\Session::get('old', []);

$isLoggedIn = Auth::check();
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
            <?php else: ?>
                <a href="<?= e(url('/perfil')); ?>" class="btn btn-outline-yellow btn-lg px-4">
                    Ir a Mi Perfil
                </a>
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

                <!-- Botón para abrir el Modal de Nueva Incidencia o solicitar Login -->
                <?php if ($isLoggedIn): ?>
                    <button type="button" class="btn btn-yellow w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
                        Solicitar Servicio
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-yellow w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Iniciar Sesión para Solicitar
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
<?php if (!$isLoggedIn): ?>
<div class="modal fade <?= !empty($errors) && isset($old['nombre']) ? 'show d-block' : '' ?>" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="registroModalLabel">Crear Cuenta en <span class="text-yellow">ReparaYa</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= e(url('/registro')); ?>" method="POST">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <!-- Alerta de error general en rojo -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small fw-bold mb-3" role="alert">
                            <?= e(is_array($errors['general']) ? implode(' ', $errors['general']) : $errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="reg_nombre" class="form-label text-muted small">Nombre completo</label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="reg_nombre" 
                            class="form-control <?= !empty($errors['nombre']) ? 'is-invalid' : ''; ?>" 
                            value="<?= e($old['nombre'] ?? ''); ?>" 
                            required 
                            maxlength="100" 
                            autocomplete="name" 
                            placeholder="Juan Pérez"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['nombre'] ?? null) ? implode(' ', $errors['nombre']) : ($errors['nombre'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reg_email" class="form-label text-muted small">Correo Electrónico</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="reg_email" 
                            class="form-control <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                            value="<?= e($old['email'] ?? ''); ?>" 
                            required 
                            maxlength="100" 
                            autocomplete="email" 
                            placeholder="correo@ejemplo.com"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['email'] ?? null) ? implode(' ', $errors['email']) : ($errors['email'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reg_telefono" class="form-label text-muted small">Teléfono (opcional)</label>
                        <input 
                            type="tel" 
                            name="telefono" 
                            id="reg_telefono" 
                            class="form-control" 
                            value="<?= e($old['telefono'] ?? ''); ?>" 
                            maxlength="20" 
                            autocomplete="tel" 
                            placeholder="600000000"
                        >
                        <?= field_errors($errors, 'telefono'); ?>
                    </div>

                    <div class="mb-3">
                        <label for="reg_password" class="form-label text-muted small">Contraseña (mínimo 8 caracteres)</label>
                        <input 
                            type="password" 
                            name="password" 
                            id="reg_password" 
                            class="form-control" 
                            required 
                            minlength="8" 
                            maxlength="72" 
                            autocomplete="new-password" 
                            placeholder="******"
                        >
                        <?= field_errors($errors, 'password'); ?>
                    </div>

                    <div class="mb-3">
                        <label for="reg_password_confirm" class="form-label text-muted small">Repite la contraseña</label>
                        <input 
                            type="password" 
                            name="password_confirm" 
                            id="reg_password_confirm" 
                            class="form-control <?= !empty($errors['password_confirm']) ? 'is-invalid' : ''; ?>" 
                            required 
                            autocomplete="new-password" 
                            placeholder="******"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password_confirm'] ?? null) ? implode(' ', $errors['password_confirm']) : ($errors['password_confirm'] ?? ''); ?>
                        </div>
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
<?php endif; ?>

<!-- ==========================================
     MODAL 2: NUEVA PETICIÓN DE SERVICIO (INCIDENCIA) -- PENDIENTE DE INCORPORAR EL TROLLER
     ========================================== -->
<?php if ($isLoggedIn): ?>
<div class="modal fade" id="nuevaIncidenciaModal" tabindex="-1" aria-labelledby="nuevaIncidenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="nuevaIncidenciaModalLabel">Solicitar <span class="text-yellow">Nuevo Servicio</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= e(url('/incidencias/crear')); ?>" method="POST">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <?= field_errors($errors, 'general'); ?>
                    
                    <div class="row g-3">
                        <!-- Especialidad -->
                        <div class="col-md-6">
                            <label for="especialidad_id" class="form-label text-muted small">Especialidad del servicio</label>
                            <select name="especialidad_id" id="especialidad_id" class="form-select" required>
                                <option value="" disabled selected>Selecciona una opción...</option>
                                <option value="1" <?= ($old['especialidad_id'] ?? '') === '1' ? 'selected' : ''; ?>>Fontanería</option>
                                <option value="2" <?= ($old['especialidad_id'] ?? '') === '2' ? 'selected' : ''; ?>>Electricidad</option>
                                <option value="3" <?= ($old['especialidad_id'] ?? '') === '3' ? 'selected' : ''; ?>>Cerrajería</option>
                                <option value="4" <?= ($old['especialidad_id'] ?? '') === '4' ? 'selected' : ''; ?>>Climatización<option>
                            </select>
                            <?= field_errors($errors, 'especialidad_id'); ?>
                        </div>

                        <!-- Tipo de Urgencia -->
                        <div class="col-md-6">
                            <label for="tipo_urgencia" class="form-label text-muted small">Tipo de urgencia</label>
                            <select name="tipo_urgencia" id="tipo_urgencia" class="form-select" required>
                                <option value="Estándar" <?= ($old['tipo_urgencia'] ?? 'Estándar') === 'Estándar' ? 'selected' : ''; ?>>Estándar (Atención regular)</option>
                                <option value="Urgente" <?= ($old['tipo_urgencia'] ?? '') === 'Urgente' ? 'selected' : ''; ?>>Urgente (Prioridad alta)</option>
                            </select>
                            <?= field_errors($errors, 'tipo_urgencia'); ?>
                        </div>

                        <!-- Fecha del servicio -->
                        <div class="col-md-6">
                            <label for="fecha_servicio" class="form-label text-muted small">Fecha y hora deseada</label>
                            <input type="datetime-local" name="fecha_servicio" id="fecha_servicio" class="form-control" value="<?= e($old['fecha_servicio'] ?? ''); ?>" required>
                            <?= field_errors($errors, 'fecha_servicio'); ?>
                        </div>

                        <!-- Dirección -->
                        <div class="col-md-6">
                            <label for="direccion" class="form-label text-muted small">Dirección del servicio</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" value="<?= e($old['direccion'] ?? ''); ?>" required placeholder="Calle, número, piso, ciudad">
                            <?= field_errors($errors, 'direccion'); ?>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcion" class="form-label text-muted small">Descripción del problema</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="form-control" required placeholder="Detalla brevemente lo que necesita ser reparado..."><?= e($old['descripcion'] ?? ''); ?></textarea>
                            <?= field_errors($errors, 'descripcion'); ?>
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

<!-- Script JS para reapertura automática del Modal de Registro si hay errores -->
<?php if (!empty($errors) && (isset($old['nombre']) || isset($errors['nombre']) || isset($errors['email']) || isset($errors['password_confirm']))): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('registroModal');
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    </script>
<?php endif; ?>

<?php endif; ?>