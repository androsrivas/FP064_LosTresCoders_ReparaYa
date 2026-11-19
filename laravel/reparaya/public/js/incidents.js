// Listado de incidencias: detalle en modal y confirmación de cancelación.
// Se carga al final del layout (@stack('scripts')), cuando Bootstrap ya existe.

// "Ver": carga el parcial incidents.show por AJAX dentro del modal
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-detail-url]');
    if (!button) return;

    const content = document.getElementById('detailContent');
    content.textContent = 'Cargando...';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();

    try {
        const res = await fetch(button.dataset.detailUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        content.innerHTML = await res.text();
    } catch (err) {
        content.textContent = `No se ha podido cargar el detalle (${err.message}).`;
    }
});

// "Cancelar": pide confirmación a los formularios con data-confirm
document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !confirm(message)) {
        event.preventDefault();
    }
});
