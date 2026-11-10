<?php
/**
 * @var array<int, array> $pendientes Tarjetas del lateral derecho (Mensajes/Incidencias sin responder)
 */
?>

<!-- Carga de FullCalendar 5 -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

<div class="container-fluid py-4">
    <div class="row g-4">
        
        <!-- COLUMNA IZQUIERDA: Vista de Calendario (Boceto) -->
        <div class="col-lg-8 col-xl-9">
            <div class="card shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 text-yellow mb-0">Agenda de Servicios</h2>
                    <div>
                        <span class="badge bg-danger me-2">Urgente</span>
                        <span class="badge badge-yellow">Estándar</span>
                    </div>
                </div>
                <!-- Contenedor FullCalendar -->
                <div id="calendar" style="min-height: 600px;"></div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Vista de incidencias / mensajes recibidos sin responder (Boceto) -->
        <div class="col-lg-4 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-header border-bottom">
                    <h3 class="h6 mb-0 text-yellow">Mensajes / Avisos Activos</h3>
                    <small class="text-muted">Pendientes de gestión</small>
                </div>
                <div class="card-body p-3 style-scroll" style="max-height: 650px; overflow-y: auto;">
                    <?php if (empty($pendientes)): ?>
                        <p class="text-muted text-center py-4 mb-0">No hay avisos pendientes.</p>
                    <?php else: ?>
                        <?php foreach ($pendientes as $aviso): ?>
                            <div class="card bg-dark border-subtle mb-3 p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-yellow"><?= e($aviso['estado']) ?></span>
                                    <small class="text-muted"><?= e(substr($aviso['fecha_servicio'] ?? '', 0, 10)) ?></small>
                                </div>
                                <h6 class="text-white mb-1"><?= e($aviso['nombre_especialidad'] ?? 'General') ?></h6>
                                <p class="small text-muted mb-2 text-truncate"><?= e($aviso['descripcion'] ?? '') ?></p>
                                <div class="text-end">
                                    <a href="<?= e(url('/incidencia/detalle/' . $aviso['id'])) ?>" class="btn btn-xs btn-outline-yellow">
                                        Ver Detalle
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- SCRIPTS DE CALENDARIO -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Botones Mensual, Semanal, Diario según boceto
        },
        events: '<?= e(url('/calendario/eventos')) ?>',
        eventClick: function(info) {
            // Requisito del boceto: Al hacer clic navega directamente al detalle
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        }
    });

    calendar.render();
});
</script>