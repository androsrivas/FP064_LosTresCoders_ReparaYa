<?php
/**
 * @var array<int, array> $incidencias
 * @var string            $estadoActual
 */
?>

<h1>Gestión de Incidencias (Administrador)</h1>

<!-- Botonera de filtros alineada a los ENUM de la BBDD -->
<div class="mb-4">
    <?php 
    $estados = ['Todas', 'Pendiente', 'Asignada', 'Finalizada', 'Cancelada'];
    foreach ($estados as $est): 
        $active = ($estadoActual === $est) ? 'btn-primary' : 'btn-outline-primary';
        $href = ($est === 'Todas') ? url('/admin/incidencias') : url('/admin/incidencias?estado=' . $est);
    ?>
        <a href="<?= e($href) ?>" class="btn <?= $active ?> btn-sm me-1"><?= e($est) ?></a>
    <?php endforeach; ?>
</div>

<!-- Tarjetas de incidencias -->
<div class="contenedor-incidencias">
    <?php if (empty($incidencias)): ?>
        <p>No hay incidencias registradas<?= $estadoActual !== 'Todas' ? ' con el estado "' . e($estadoActual) . '"' : '' ?>.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($incidencias as $incidencia): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Localizador: <?= e($incidencia['localizador']) ?></strong>
                            <span class="badge bg-secondary"><?= e($incidencia['estado']) ?></span>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Especialidad:</strong> <?= e($incidencia['especialidad'] ?? 'General') ?></p>
                            <p class="mb-1"><strong>Técnico:</strong> <?= e($incidencia['nombre_tecnico'] ?? 'Sin asignar') ?></p>
                            <p class="mb-1"><strong>Urgencia:</strong> <?= e($incidencia['tipo_urgencia']) ?></p>
                            <p class="mb-1"><strong>Fecha Servicio:</strong> <?= e($incidencia['fecha_servicio']) ?></p>
                        </div>
                        <div class="card-footer text-end">
                            <a href="<?= e(url('/incidencias/' . $incidencia['id'])) ?>" class="btn btn-sm btn-warning">
                                Acceder
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>