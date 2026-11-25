// Vista Mis Gestiones (profile/show): reapertura de modales con errores, pestañas dinámicas y buscador.
// Se carga al final del layout (@stack('scripts')), cuando Bootstrap ya existe.

// 1. Reapertura automática del modal que tenga errores de validación
document.querySelectorAll('.modal[data-auto-open="true"]').forEach((modalEl) => {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});

// 2. Cambio de sección dinámica y buscador para Gestora (Incidencias, Comunidades, Liquidaciones)
const tabButtons = document.querySelectorAll('[data-gestora-tab]');
const tabPanels = document.querySelectorAll('[data-gestora-panel]');
const sectionTitle = document.getElementById('gestoraSectionTitle');
const newBtn = document.getElementById('gestoraNewBtn');
const searchInput = document.getElementById('gestoraSearchInput');

const sectionConfig = {
    incidencias: {
        title: 'Incidencias de mis comunidades',
        btnText: '+ Nueva Incidencia',
        modalTarget: '#nuevaIncidenciaModal',
        placeholder: 'Buscar por referencia, comunidad, fecha, estado...',
    },
    comunidades: {
        title: 'Mis comunidades',
        btnText: '+ Nueva Comunidad',
        modalTarget: '#nuevaComunidadModal',
        placeholder: 'Buscar por nombre, dirección o CIF...',
    },
    liquidaciones: {
        title: 'Mis liquidaciones',
        btnText: '+ Nueva Liquidación',
        modalTarget: '#nuevaLiquidacionModal',
        placeholder: 'Buscar por período, comunidad, importe, estado...',
    },
};

const normalizeText = (text) =>
    text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

function filterActiveTable() {
    if (!searchInput) return;

    const activePanel = document.querySelector('[data-gestora-panel]:not(.d-none)');
    if (!activePanel) return;

    const tbody = activePanel.querySelector('tbody');
    if (!tbody) return;

    const query = normalizeText(searchInput.value);
    const dataRows = tbody.querySelectorAll('tr:not([data-empty-search])');
    let visibleCount = 0;

    dataRows.forEach((row) => {
        // Excluimos la columna de botones de acción (Ver / Cancelar)
        const cells = Array.from(row.querySelectorAll('td')).filter(
            (td) => !td.querySelector('button, form')
        );
        const rowText = normalizeText(cells.map((td) => td.textContent).join(' '));
        const matches = query === '' || rowText.includes(query);

        row.classList.toggle('d-none', !matches);
        if (matches) visibleCount++;
    });

    // Fila informativa si no hay coincidencias
    let emptyRow = tbody.querySelector('tr[data-empty-search]');
    if (visibleCount === 0 && dataRows.length > 0) {
        if (!emptyRow) {
            const colCount = activePanel.querySelectorAll('thead th').length || 1;
            emptyRow = document.createElement('tr');
            emptyRow.setAttribute('data-empty-search', 'true');
            emptyRow.innerHTML = `<td colspan="${colCount}" class="text-center text-white-50 py-4">No se encontraron resultados para "<span class="text-light"></span>".</td>`;
            tbody.appendChild(emptyRow);
        }
        emptyRow.querySelector('span').textContent = searchInput.value.trim();
        emptyRow.classList.remove('d-none');
    } else if (emptyRow) {
        emptyRow.classList.add('d-none');
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterActiveTable);
}

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
        if (searchInput) {
            searchInput.placeholder = config.placeholder;
            filterActiveTable();
        }
    });
});