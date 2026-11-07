<!-- FullCalendar 5 & Bootstrap 5 CSS / JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Agenda de Incidencias</h2>
        <div>
            <span class="badge bg-danger me-2">Urgente</span>
            <span class="badge bg-primary">Estándar</span>
        </div>
    </div>

    <!-- Contenedor principal de la plantilla Calendar 20 -->
    <div id="calendar" class="card p-3 shadow-sm"></div>
</div>

<!-- Modal Bootstrap para ver el Detalle Completo -->
<div class="modal fade" id="modalDetalleIncidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Detalle de Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Cliente:</strong> <span id="modalCliente"></span></p>
                <p><strong>Especialidad:</strong> <span id="modalEspecialidad"></span></p>
                <p><strong>Técnico:</strong> <span id="modalTecnico"></span></p>
                <p><strong>Tipo de Servicio:</strong> <span id="modalTipo" class="badge"></span></p>
                <p><strong>Estado Actual:</strong> <span id="modalEstado" class="badge bg-secondary"></span></p>
                <p><strong>Fecha del Servicio:</strong> <span id="modalFecha"></span></p>
                <hr>
                <p><strong>Descripción:</strong></p>
                <p id="modalDescripcion" class="text-muted"></p>
            </div>
            <div class="modal-footer">
                <a id="btnIrADetalle" href="#" class="btn btn-primary">Ver Historial / Gestionar</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var modalElement = new bootstrap.Modal(document.getElementById('modalDetalleIncidencia'));

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay' //Requisito: Vistas Mensual, Semanal y Diaria
        },
        events: '/calendario/eventos',

        //Evento al clicar en una incidencia -> Abre el Modal con Detalle Completo
        eventClick: function(info) {
            var props = info.event.extendedProps;

            document.getElementById('modalCliente').textContent = props.cliente;
            document.getElementById('modalEspecialidad').textContent = props.especialidad;
            document.getElementById('modalTecnico').textContent = props.tecnico;
            document.getElementById('modalFecha').textContent = info.event.start.toLocaleString();
            document.getElementById('modalDescripcion').textContent = props.descripcion;

            //Badges de Estado y Tipo
            var elTipo = document.getElementById('modalTipo');
            elTipo.textContent = props.tipo;
            elTipo.className = 'badge ' + (props.tipo.toLowerCase() === 'urgente' ? 'bg-danger' : 'bg-primary');

            document.getElementById('modalEstado').textContent = props.estado;

            //Botón para navegar a la vista de detalle tradicional con historial
            document.getElementById('btnIrADetalle').href = '/incidencia/detalle/' + info.event.id;

            modalElement.show();
        }
    });

    calendar.render();
});
</script>