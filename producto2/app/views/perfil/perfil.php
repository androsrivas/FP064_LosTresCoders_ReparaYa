<?php
declare(strict_types=1);

$errors = $errors ?? [];
$old = $old ?? [];
$usuario = $usuario ?? [];
$incidencias = $incidencias ?? [];

$nombre = $old['nombre'] ?? $usuario['nombre'] ?? '';
$email = $old['email'] ?? $usuario['email'] ?? '';
$telefono = $old['telefono'] ?? $usuario['telefono'] ?? '';
?>

<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary">
        <h2 class="h3 fw-bold text-light m-0">
            👤 Mi <span class="text-yellow">Perfil</span>
        </h2>
        <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase">
            <?= e($usuario['rol'] ?? 'CLIENTE'); ?>
        </span>
    </div>

    <div class="row g-4">

        <!-- Columna Izquierda: Datos de usuario -->
        <div class="col-lg-5 mb-4">
            <!-- El contenedor principal con estilo de panel -->
            <div class="perfil-panel perfil-panel-accent p-4 h-100 d-flex flex-column justify-content-between">

                <div>
                    <!-- Encabezado con línea -->
                    <div class="perfil-panel-header">
                        <h5 class="text-warning fw-bold mb-0">
                            📋 Datos de usuario
                        </h5>
                    </div>

                    <!-- Lista de datos -->
                    <ul class="list-group">
                        <li class="list-group-item text-light px-3 py-3">
                            <span class="text-muted d-block small">Nombre completo:</span>
                            <span class="fw-semibold fs-6"><?= e($usuario['nombre'] ?? 'Sin especificar'); ?></span>
                        </li>
                        <li class="list-group-item text-light px-3 py-3">
                            <span class="text-muted d-block small">Correo electrónico:</span>
                            <span class="fw-semibold fs-6"><?= e($usuario['email'] ?? 'Sin especificar'); ?></span>
                        </li>
                        <li class="list-group-item text-light px-3 py-3">
                            <span class="text-muted d-block small">Teléfono de contacto:</span>
                            <span class="fw-semibold fs-6"><?= e($usuario['telefono'] ?? 'No registrado'); ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Botón abajo del todo -->
                <div class="mt-4 pt-3 border-top border-secondary text-end">
                    <button type="button" class="btn btn-warning fw-bold px-4" data-bs-toggle="modal"
                        data-bs-target="#editPerfilModal">
                        ✏️ Editar perfil
                    </button>
                </div>

            </div>
        </div>

        <!-- COLUMNA DERECHA: Incidencias asociadas al cliente -->
        <div class="col-lg-7 mb-4">
            <div class="perfil-panel perfil-panel-accent p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <!-- Encabezado con la línea divisoria del panel -->
                    <div class="perfil-panel-header d-flex align-items-center gap-2">
                        <h5 class="text-warning fw-bold mb-0">
                            🛠️ Incidencias asociadas al cliente
                        </h5>
                    </div>

                    <?php if (empty($incidencias)): ?>
                        <!-- Mensaje en caso de no tener incidencias -->
                        <div class="text-center py-5">
                            <p class="text-muted mb-0 fs-5">No tiene incidencias asociadas.</p>
                        </div>
                    <?php else: ?>
                        <!-- Listado de tarjetas de incidencias -->
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($incidencias as $incidencia): ?>
                                <?php
                                // -- ESTADO -- //
                                $estado = $incidencia['estado'] ?? 'Pendiente';
                                $badgeBg = match ($estado) {
                                    'Finalizada' => 'badge-status-finalizada',
                                    'Asignada' => 'badge-status-asignada',
                                    'En curso' => 'badge-status-en-curso',
                                    'Cancelada' => 'badge-status-cancelada',
                                    default => 'badge-status-pendiente'
                                };
                                // --- TIPO DE URGENCIA ---
                                $urgencia = $incidencia['tipo_urgencia'] ?? 'Estándar';
                                $badgeUrgencia = match ($urgencia) {
                                    'Urgente' => 'badge-urgencia-urgente',
                                    default => 'badge-urgencia-estandar'
                                };
                                ?>
                                <div
                                    class="card text-light px-3 py-3 d-flex flex-row align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <!-- Localizador -->
                                            <span class="fw-bold text-warning">
                                                <?= e($incidencia['localizador'] ?? ('#' . $incidencia['id'])); ?>
                                            </span>
                                            <!-- Badge Urgencia -->
                                            <span class="badge badge-status <?= $badgeUrgencia; ?> small">
                                                ⚡<?= e($urgencia); ?>
                                            </span>
                                            <!-- Badge Estado -->
                                            <span class="badge badge-status <?= $badgeBg; ?> small">
                                                <?= e($estado); ?>
                                            </span>
                                        </div>
                                        <div class="small text-muted">
                                            Especialidad: <strong
                                                class="text-light"><?= e($incidencia['nombre_especialidad'] ?? 'General'); ?></strong>
                                        </div>
                                    </div>

                                    <div>
                                        <!-- BOTÓN ADAPTADO PARA ABRIR EL MODAL VÍA AJAX -->
                                        <button type="button"
                                            class="btn btn-warning btn-sm fw-bold px-3 btn-ver-incidencia"
                                            data-incidencia-id="<?= (int)$incidencia['id']; ?>">
                                            Ver Detalle
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL DE EDICIÓN DE PERFIL -->
<div class="modal fade" id="editPerfilModal" tabindex="-1" aria-labelledby="editPerfilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="editPerfilModalLabel">
                    Editar <span class="text-yellow">Perfil</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form action="<?= e(url('/perfil')); ?>" method="POST" novalidate>
                <?= csrf_field(); ?>

                <div class="modal-body">

                    <!-- Alerta error general -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small fw-bold mb-3" role="alert">
                            <?= e(is_array($errors['general']) ? implode(' ', $errors['general']) : $errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label text-muted small">Nombre completo</label>
                        <input type="text" name="nombre" id="nombre"
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['nombre']) ? 'is-invalid' : ''; ?>"
                            value="<?= e($nombre); ?>" required maxlength="100">
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['nombre'] ?? null) ? implode(' ', $errors['nombre']) : ($errors['nombre'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small">Correo Electrónico</label>
                        <input type="email" name="email" id="email"
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>"
                            value="<?= e($email); ?>" required maxlength="100">
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['email'] ?? null) ? implode(' ', $errors['email']) : ($errors['email'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label text-muted small">Teléfono (opcional)</label>
                        <input type="tel" name="telefono" id="telefono"
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['telefono']) ? 'is-invalid' : ''; ?>"
                            value="<?= e($telefono); ?>" maxlength="20">
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['telefono'] ?? null) ? implode(' ', $errors['telefono']) : ($errors['telefono'] ?? ''); ?>
                        </div>
                    </div>

                    <hr class="border-secondary my-4">
                    <p class="text-muted small fw-bold mb-2">Cambiar Contraseña (Opcional)</p>

                    <div class="mb-3">
                        <label for="password_actual" class="form-label text-muted small">Contraseña Actual</label>
                        <input type="password" name="password_actual" id="password_actual"
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['password_actual']) ? 'is-invalid' : ''; ?>"
                            placeholder="******">
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password_actual'] ?? null) ? implode(' ', $errors['password_actual']) : ($errors['password_actual'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-muted small">Nueva Contraseña</label>
                        <input type="password" name="password" id="password"
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['password']) ? 'is-invalid' : ''; ?>"
                            placeholder="Mínimo 8 caracteres">
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password'] ?? null) ? implode(' ', $errors['password']) : ($errors['password'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label text-muted small">Repite la nueva
                            contraseña</label>
                        <input type="password" name="password_confirm" id="password_confirm"
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['password_confirm']) ? 'is-invalid' : ''; ?>"
                            placeholder="******">
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password_confirm'] ?? null) ? implode(' ', $errors['password_confirm']) : ($errors['password_confirm'] ?? ''); ?>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-yellow btn-sm fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- INCLUSIÓN DEL MODAL REUTILIZABLE DE DETALLE DE INCIDENCIA -->
<?php require __DIR__ . '/../incidencias/_modal_detalle.php'; ?>

<!-- SCRIPT GENERAL PARA LA CARGA DINÁMICA DEL DETALLE VÍA AJAX -->
<script>
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.btn-ver-incidencia');
    if (!trigger) return;

    const incidenciaId = trigger.dataset.incidenciaId;
    if (!incidenciaId) return;

    const modalEl = document.getElementById('detalleIncidenciaModal');
    const contenedorContent = document.getElementById('contenidoDetalleIncidencia');

    if (!modalEl || !contenedorContent) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Muestra spinner inicial
    contenedorContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="text-muted small mt-2">Cargando información del servicio...</p>
        </div>`;

    modal.show();

    // Petición AJAX al controlador (Sintaxis corregida)
    fetch(`<?= e(url('/incidencia/detalle/')); ?>${incidenciaId}`)
        .then(response => {
            if (!response.ok) throw new Error('Error de permisos o servicio');
            return response.text();
        })
        .then(html => {
            contenedorContent.innerHTML = html;
        })
        .catch(err => {
            contenedorContent.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-octagon me-2"></i>No se pudo cargar la información del servicio.
                </div>`;
        });
});
</script>

<!-- Re-apertura automática del Modal si hay errores tras enviar POST -->
<?php if (!empty($errors)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('editPerfilModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        });
    </script>
<?php endif; ?>