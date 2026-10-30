<?php

require_once __DIR__ . '/layouts/header.php';

// Si el controlador aún no pasa los datos, los consultamos directamente de la BD
if (!isset($servicios) && isset($pdo) && isset($_SESSION['usuario_id'])) {
    $clienteId = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("
        SELECT i.*, e.nombre AS especialidad 
        FROM incidencias i
        LEFT JOIN especialidades e ON i.especialidad_id = e.id
        WHERE i.cliente_id = :cliente_id
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([':cliente_id' => $clienteId]);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $servicios = $servicios ?? [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">Mis Servicios</h2>
        <p class="text-muted small mb-0">Historial y seguimiento de tus solicitudes de reparación</p>
    </div>
    <button type="button" class="btn btn-yellow btn-sm" data-bs-toggle="modal" data-bs-target="#nuevaIncidenciaModal">
        + Solicitar nuevo servicio
    </button>
</div>

<!-- Listado en rejilla de Tarjetas Dinámicas -->
<div class="row g-4">
    <?php if (empty($servicios)): ?>
        <div class="col-12 text-center py-5">
            <div class="card p-5">
                <p class="text-muted mb-0">No tienes servicios solicitados actualmente.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($servicios as $servicio): ?>
            <?php
            // Definir clases e identificadores visuales según el estado
            $estadoNormalizado = strtolower($servicio['estado']);
            
            switch ($estadoNormalizado) {
                case 'finalizada':
                    $badgeClass = 'bg-success text-white';
                    $borderStyle = 'border-left: 4px solid #198754 !important;';
                    break;
                case 'cancelada':
                    $badgeClass = 'bg-danger text-white';
                    $borderStyle = 'border-left: 4px solid #dc3545 !important;';
                    break;
                case 'en curso':
                case 'asignada':
                    $badgeClass = 'badge-yellow';
                    $borderStyle = 'border-left: 4px solid #ffb703 !important;';
                    break;
                default: // Pendiente de asignar
                    $badgeClass = 'bg-secondary text-white';
                    $borderStyle = 'border-left: 4px solid #6c757d !important;';
                    break;
            }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 p-3 d-flex flex-column justify-content-between" style="<?= $borderStyle ?>">
                    <div>
                        <!-- Cabecera de la tarjeta: Localizador y Estado -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-bold text-yellow font-monospace small">
                                <?= htmlspecialchars($servicio['localizador']) ?>
                            </span>
                            <span class="badge <?= $badgeClass ?> px-2 py-1 small">
                                <?= htmlspecialchars($servicio['estado']) ?>
                            </span>
                        </div>

                        <!-- Especialidad -->
                        <h5 class="text-white fw-bold mb-2">
                            <?= htmlspecialchars($servicio['especialidad'] ?? 'Servicio General') ?>
                        </h5>

                        <!-- Descripción breve -->
                        <p class="text-muted small mb-3">
                            <?= htmlspecialchars(mb_strimwidth($servicio['descripcion'], 0, 85, "...")) ?>
                        </p>
                    </div>

                    <div>
                        <hr class="border-secondary opacity-25 my-2">
                        
                        <!-- Info de fecha y urgencia -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">
                                📅 <?= date('d/m/Y H:i', strtotime($servicio['fecha_servicio'])) ?>
                            </span>
                            <span class="badge bg-dark border border-secondary text-light small">
                                <?= htmlspecialchars($servicio['tipo_urgencia']) ?>
                            </span>
                        </div>

                        <!-- Botón para acceder al detalle -->
                        <a href="index.php?action=detalle_servicio&id=<?= $servicio['id'] ?>" class="btn btn-outline-yellow w-100 btn-sm fw-bold">
                            Ver detalle del servicio
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
require_once __DIR__ . '/layouts/footer.php'; 
?>