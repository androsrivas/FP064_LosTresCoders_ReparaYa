<?php
declare(strict_types=1);

$errors      = $errors ?? [];
$old         = $old ?? [];
$usuario     = $usuario ?? [];
$incidencias = $incidencias ?? [];

$nombre   = $old['nombre'] ?? $usuario['nombre'] ?? '';
$email    = $old['email'] ?? $usuario['email'] ?? '';
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
        
        <!-- COLUMNA IZQUIERDA: Datos de Usuario (Lectura + Botón Modal) -->
        <div class="col-lg-5">
            <div class="card bg-dark border-secondary text-light h-100 shadow-sm">
                <div class="card-header bg-dark border-secondary fw-bold text-yellow py-3 d-flex align-items-center gap-2">
                    <span>📋</span> Datos de usuario
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item bg-transparent text-light border-secondary px-0 py-3">
                            <span class="text-muted d-block small">Nombre completo:</span>
                            <span class="fw-semibold fs-6"><?= e($usuario['nombre'] ?? 'Sin especificar'); ?></span>
                        </li>
                        <li class="list-group-item bg-transparent text-light border-secondary px-0 py-3">
                            <span class="text-muted d-block small">Correo electrónico:</span>
                            <span class="fw-semibold fs-6"><?= e($usuario['email'] ?? 'Sin especificar'); ?></span>
                        </li>
                        <li class="list-group-item bg-transparent text-light border-secondary px-0 py-3">
                            <span class="text-muted d-block small">Teléfono de contacto:</span>
                            <span class="fw-semibold fs-6"><?= e($usuario['telefono'] ?? 'No registrado'); ?></span>
                        </li>
                    </ul>

                    <div class="mt-4 pt-3 border-top border-secondary text-end">
                        <button type="button" class="btn btn-yellow fw-bold" data-bs-toggle="modal" data-bs-target="#editPerfilModal">
                            ✏️ Botón editar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Incidencias asociadas al cliente -->
        <div class="col-lg-7">
            <div class="card bg-dark border-secondary text-light h-100 shadow-sm">
                <div class="card-header bg-dark border-secondary fw-bold text-yellow py-3 d-flex align-items-center gap-2">
                    <span>🛠️</span> Incidencias asociadas al cliente
                </div>
                <div class="card-body">
                    
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
                                    $estado = $incidencia['estado'] ?? 'Pendiente';
                                    $badgeBg = match($estado) {
                                        'Finalizada' => 'bg-success',
                                        'Asignada'   => 'bg-info text-dark',
                                        'En curso'   => 'bg-primary',
                                        'Cancelada'  => 'bg-danger',
                                        default      => 'bg-warning text-dark'
                                    };
                                ?>
                                <div class="card bg-dark border-secondary text-light shadow-sm">
                                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-yellow">
                                                    <?= e($incidencia['localizador'] ?? ('#' . $incidencia['id'])); ?>
                                                </span>
                                                <span class="badge <?= $badgeBg; ?> small">
                                                    <?= e($estado); ?>
                                                </span>
                                            </div>
                                            <div class="small text-muted">
                                                Especialidad: <strong class="text-light"><?= e($incidencia['nombre_especialidad'] ?? 'General'); ?></strong>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <a href="<?= e(url('/index.php?action=detalle_servicio&id=' . $incidencia['id'])); ?>" class="btn btn-yellow btn-sm fw-bold px-3">
                                                Acceder
                                            </a>
                                        </div>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <input 
                            type="text" 
                            name="nombre" 
                            id="nombre" 
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['nombre']) ? 'is-invalid' : ''; ?>" 
                            value="<?= e($nombre); ?>" 
                            required 
                            maxlength="100"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['nombre'] ?? null) ? implode(' ', $errors['nombre']) : ($errors['nombre'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small">Correo Electrónico</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                            value="<?= e($email); ?>" 
                            required 
                            maxlength="100"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['email'] ?? null) ? implode(' ', $errors['email']) : ($errors['email'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label text-muted small">Teléfono (opcional)</label>
                        <input 
                            type="tel" 
                            name="telefono" 
                            id="telefono" 
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['telefono']) ? 'is-invalid' : ''; ?>" 
                            value="<?= e($telefono); ?>" 
                            maxlength="20"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['telefono'] ?? null) ? implode(' ', $errors['telefono']) : ($errors['telefono'] ?? ''); ?>
                        </div>
                    </div>

                    <hr class="border-secondary my-4">
                    <p class="text-muted small fw-bold mb-2">Cambiar Contraseña (Opcional)</p>

                    <div class="mb-3">
                        <label for="password_actual" class="form-label text-muted small">Contraseña Actual</label>
                        <input 
                            type="password" 
                            name="password_actual" 
                            id="password_actual" 
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['password_actual']) ? 'is-invalid' : ''; ?>" 
                            placeholder="******"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password_actual'] ?? null) ? implode(' ', $errors['password_actual']) : ($errors['password_actual'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-muted small">Nueva Contraseña</label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['password']) ? 'is-invalid' : ''; ?>" 
                            placeholder="Mínimo 8 caracteres"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password'] ?? null) ? implode(' ', $errors['password']) : ($errors['password'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label text-muted small">Repite la nueva contraseña</label>
                        <input 
                            type="password" 
                            name="password_confirm" 
                            id="password_confirm" 
                            class="form-control bg-dark text-light border-secondary <?= !empty($errors['password_confirm']) ? 'is-invalid' : ''; ?>" 
                            placeholder="******"
                        >
                        <div class="text-danger small mt-1 fw-semibold">
                            <?= is_array($errors['password_confirm'] ?? null) ? implode(' ', $errors['password_confirm']) : ($errors['password_confirm'] ?? ''); ?>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-yellow btn-sm fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Re-apertura automática del Modal si hay errores tras enviar POST -->
<?php if (!empty($errors)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('editPerfilModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>
<?php endif; ?>