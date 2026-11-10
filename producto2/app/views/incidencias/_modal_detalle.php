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