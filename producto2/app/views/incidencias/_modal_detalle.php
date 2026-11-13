<!-- Modal Reutilizable de Detalle de Incidencia -->
<div class="modal fade" id="detalleIncidenciaModal" tabindex="-1" aria-labelledby="detalleIncidenciaModalLabel" aria-hidden="true">
    <!-- Se cambia modal-lg a modal-xl para más amplitud -->
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="detalleIncidenciaModalLabel">
                    <i class="bi bi-tools me-2 text-warning"></i>Detalles del Servicio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body p-4" id="contenidoDetalleIncidencia">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="text-muted small mt-2">Cargando información del servicio...</p>
                </div>
            </div>

            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function enviarMensajeForo(event, incidenciaId) {
    event.preventDefault();

    const form = event.target;
    const input = document.getElementById('inputMensajeForo');
    const btnSubmit = form.querySelector('button[type="submit"]');
    const contenedorContent = document.getElementById('contenidoDetalleIncidencia');

    if (!input || !input.value.trim()) return;

    const formData = new FormData(form);
    if (btnSubmit) btnSubmit.disabled = true;

    fetch(`<?= e(url('/incidencia/detalle/')); ?>${incidenciaId}/mensaje`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.exito) {
            alert(data.mensaje || 'No se pudo enviar el mensaje.');
            if (btnSubmit) btnSubmit.disabled = false;
            return;
        }

        // Recargar el parcial del modal para pintar el mensaje recién guardado
        return fetch(`<?= e(url('/incidencia/detalle/')); ?>${incidenciaId}`)
            .then(r => r.text())
            .then(html => {
                if (contenedorContent) {
                    contenedorContent.innerHTML = html;
                    const cajaMensajes = contenedorContent.querySelector('.mensajes-contenedor');
                    if (cajaMensajes) {
                        cajaMensajes.scrollTop = cajaMensajes.scrollHeight;
                    }
                    const nuevoInput = document.getElementById('inputMensajeForo');
                    if (nuevoInput) nuevoInput.focus();
                }
            });
    })
    .catch(() => {
        alert('Error de conexión al enviar el mensaje.');
        if (btnSubmit) btnSubmit.disabled = false;
    });
}
</script>