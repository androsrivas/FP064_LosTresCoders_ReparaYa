// Vista principal (home): reapertura automática del modal de nueva incidencia si hay errores de validación.
// Se carga al final del layout (@stack('scripts')), cuando Bootstrap ya existe.

const newIncidentModal = document.getElementById('nuevaIncidenciaModal');
if (newIncidentModal?.dataset.autoOpen === 'true') {
    bootstrap.Modal.getOrCreateInstance(newIncidentModal).show();
}