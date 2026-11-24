// Vista Mis Gestiones (profile/show): reapertura de modales con errores y pestañas dinámicas de gestora.
// Se carga al final del layout (@stack('scripts')), cuando Bootstrap ya existe.

// 1. Reapertura automática del modal que tenga errores de validación
document.querySelectorAll('.modal[data-auto-open="true"]').forEach((modalEl) => {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});

// 2. Cambio de sección dinámica para Gestora (Incidencias, Comunidades, Liquidaciones)
const tabButtons = document.querySelectorAll('[data-gestora-tab]');
const tabPanels = document.querySelectorAll('[data-gestora-panel]');
const sectionTitle = document.getElementById('gestoraSectionTitle');
const newBtn = document.getElementById('gestoraNewBtn');

const sectionConfig = {
    incidencias: {
        title: '🛠️ Incidencias de mis comunidades',
        btnText: '+ Nueva Incidencia',
        modalTarget: '#nuevaIncidenciaModal',
    },
    comunidades: {
        title: '🏢 Mis comunidades',
        btnText: '+ Nueva Comunidad',
        modalTarget: '#nuevaComunidadModal',
    },
    liquidaciones: {
        title: '💶 Mis liquidaciones',
        btnText: '+ Nueva Liquidación',
        modalTarget: '#nuevaLiquidacionModal',
    },
};

tabButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.gestoraTab;
        const config = sectionConfig[target];
        if (!config) return;

        tabButtons.forEach((b) => {
            const active = b === btn;
            b.classList.toggle('btn-yellow', active);
            b.classList.toggle('btn-outline-yellow', !active);
            b.classList.toggle('active', active);
        });

        tabPanels.forEach((panel) => {
            panel.classList.toggle('d-none', panel.id !== `tab-${target}`);
        });

        if (sectionTitle) sectionTitle.textContent = config.title;
        if (newBtn) {
            newBtn.textContent = config.btnText;
            newBtn.setAttribute('data-bs-target', config.modalTarget);
        }
    });
});