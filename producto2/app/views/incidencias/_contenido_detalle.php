<?php
/** @var array $incidencia */
/** @var array $historial */
/** @var array $mensajes */
/** @var string $userRol */
/** @var int $userId */

// Mapeo a clases CSS personalizadas
$clasesEstado = [
    'Finalizada' => 'badge-status-finalizada',
    'Asignada'   => 'badge-status-asignada',
    'En curso'   => 'badge-status-en-curso',
    'Cancelada'  => 'badge-status-cancelada',
    'Pendiente'  => 'badge-status-pendiente',
];
$badgeEstadoClass = $clasesEstado[$incidencia['estado'] ?? 'Pendiente'] ?? 'badge-status-pendiente';

$esUrgente = strtolower($incidencia['tipo_urgencia'] ?? '') === 'urgente';
$badgeUrgenciaClass = $esUrgente ? 'badge-urgencia-urgente' : 'badge-urgencia-estandar';

// Nombre de especialidad procedente del JOIN en la consulta SQL
$especialidadNombre = $incidencia['nombre_especialidad'] 
    ?? $incidencia['especialidad_nombre'] 
    ?? $incidencia['especialidad'] 
    ?? 'General';
?>

<!-- ========================================== -->
<!-- 1. BLOQUE SUPERIOR: Información + Historial -->
<!-- ========================================== -->
<div class="card bg-dark border-secondary mb-3">
    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white fw-bold">
            <i class="bi bi-file-text me-2 text-warning"></i>Información de la Incidencia
            <span class="badge bg-secondary ms-2 small">#<?= e($incidencia['localizador'] ?? $incidencia['id']); ?></span>
        </h6>
        <!-- Estado -->
        <span class="badge-status <?= $badgeEstadoClass; ?>">
            <?= e($incidencia['estado'] ?? 'Pendiente'); ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3 small">
            <div class="col-md-6">
                <span class="text-muted d-block">Especialidad:</span>
                <strong class="text-light"><?= e($especialidadNombre); ?></strong>
            </div>
            <div class="col-md-6">
                <span class="text-muted d-block">Tipo de urgencia:</span>
                <!-- Urgencia -->
                <span class="badge-status d-inline-block mt-1 <?= $badgeUrgenciaClass; ?>">
                    <?= e($incidencia['tipo_urgencia'] ?? 'Estándar'); ?>
                </span>
            </div>
            <div class="col-md-6">
                <span class="text-muted d-block">Fecha del servicio:</span>
                <strong><?= e(date('d/m/Y H:i', strtotime($incidencia['fecha_servicio']))); ?></strong>
            </div>
            <div class="col-md-6">
                <span class="text-muted d-block">Dirección:</span>
                <strong><?= e($incidencia['direccion']); ?></strong>
            </div>
            <div class="col-12">
                <span class="text-muted d-block">Descripción del problema:</span>
                <!-- Caja con fondo oscuro coherente sin romper el contraste -->
                <p class="mb-0 text-light bg-black bg-opacity-50 p-2 rounded border border-secondary mt-1">
                    <?= nl2br(e($incidencia['descripcion'] ?? 'Sin descripción.')); ?>
                </p>
            </div>
        </div>

        <!-- Acciones especiales si el usuario autenticado es TÉCNICO -->
        <?php if ($userRol === 'tecnico' && in_array($incidencia['estado'] ?? '', ['Asignada', 'En curso'])): ?>
            <hr class="border-secondary">
            <div class="d-flex align-items-center justify-content-between">
                <span class="small text-muted">Acciones de estado (Técnico):</span>
                <?php if (($incidencia['estado'] ?? '') === 'Asignada'): ?>
                    <button type="button" class="btn btn-primary btn-sm btn-cambiar-estado" data-estado="En curso" data-id="<?= $incidencia['id']; ?>">
                        <i class="bi bi-play-fill me-1"></i>Pasar a "En curso"
                    </button>
                <?php elseif (($incidencia['estado'] ?? '') === 'En curso'): ?>
                    <button type="button" class="btn btn-success btn-sm btn-cambiar-estado" data-estado="Finalizada" data-id="<?= $incidencia['id']; ?>">
                        <i class="bi bi-check-circle me-1"></i>Finalizar servicio
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Acciones especiales si el usuario autenticado es ADMINISTRADOR -->
        <?php if ($userRol === 'admin'): ?>
            <hr class="border-secondary">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="abrirEdicionAdmin(<?= $incidencia['id']; ?>)">
                    <i class="bi bi-pencil me-1"></i>Editar Incidencia
                </button>
                <?php if (($incidencia['estado'] ?? '') !== 'Cancelada'): ?>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="cancelarIncidenciaAdmin(<?= $incidencia['id']; ?>)">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Historial de cambios con fecha y hora -->
        <hr class="border-secondary">
        <h6 class="text-muted small fw-bold mb-2"><i class="bi bi-clock-history me-1"></i>Historial de Modificaciones</h6>
        <?php if (!empty($historial)): ?>
            <ul class="list-group list-group-flush small rounded border border-secondary">
                <?php foreach ($historial as $h): ?>
                    <?php 
                    $textoComentario = $h['comentario'] ?? 'Sin comentario';
                    $usuarioCambio = $h['usuario_nombre'] ?? $h['nombre_usuario'] ?? $h['usuario'] ?? ('Usuario #' . ($h['usuario_id'] ?? ''));
                    $fechaCambio = $h['created_at'] ?? null;
                    ?>
                    <li class="list-group-item bg-dark text-light border-secondary py-2 px-3 d-flex justify-content-between align-items-center">
                        <span><?= e($textoComentario); ?> <span class="text-muted small">(por <?= e($usuarioCambio); ?>)</span></span>
                        <span class="text-muted small ms-2">
                            <?= $fechaCambio ? e(date('d/m/Y H:i', strtotime($fechaCambio))) : ''; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted small fst-italic mb-0">Sin modificaciones registradas.</p>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. BLOQUE INFERIOR: Mensajería / Foro      -->
<!-- ========================================== -->
<div class="card bg-dark border-secondary">
    <div class="card-header border-secondary">
        <h6 class="mb-0 text-white fw-bold">
            <i class="bi bi-chat-dots me-2 text-info"></i>Mensajería Técnico - Cliente
        </h6>
    </div>
    <div class="card-body">
        <!-- Historial de Mensajes de comentarios_incidencias -->
        <div class="mensajes-contenedor overflow-auto p-3 bg-black bg-opacity-25 rounded border border-secondary mb-3" style="max-height: 250px;">
            <?php if (!empty($mensajes)): ?>
                <?php foreach ($mensajes as $msg): ?>
                    <?php 
                    $emisorId = (int) ($msg['usuario_id'] ?? 0);
                    $esMio = ($emisorId === $userId); 
                    ?>
                    <div class="d-flex mb-2 <?= $esMio ? 'justify-content-end' : 'justify-content-start'; ?>">
                        <div class="p-2 rounded <?= $esMio ? 'bg-warning text-dark fw-semibold' : 'bg-secondary text-white'; ?>" style="max-width: 80%;">
                            <div class="small fw-bold border-bottom border-dark border-opacity-25 pb-1 mb-1">
                                <?= e($msg['emisor_nombre'] ?? $msg['nombre_usuario'] ?? ('Usuario #' . $emisorId)); ?> 
                                <span class="fw-normal opacity-75 small">(<?= e(date('H:i d/m', strtotime($msg['created_at'] ?? 'now'))); ?>)</span>
                            </div>
                            <!-- Se lee $msg['comentario'] -->
                            <div class="small"><?= nl2br(e($msg['comentario'] ?? '')); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-chat-left-dots fs-3 d-block mb-1"></i>
                    <p class="mb-0 fw-bold">Comunícate con tu técnico</p>
                    <small>Usa el formulario inferior para enviar dudas o aclaraciones.</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Formulario para nuevo mensaje -->
        <form id="formNuevoMensaje" onsubmit="enviarMensajeForo(event, <?= (int)$incidencia['id']; ?>)">
            <div class="input-group">
                <input type="text" name="comentario" id="inputMensajeForo" class="form-control bg-dark border-secondary text-white" placeholder="Escribe un mensaje..." required autocomplete="off">
                <button type="submit" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-send-fill me-1"></i>Enviar
                </button>
            </div>
        </form>
    </div>
</div>