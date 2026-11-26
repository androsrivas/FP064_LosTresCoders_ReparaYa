// Vista Mis Gestiones (profile/show): reapertura de modales con errores, pestañas dinámicas y buscador.
// Se carga al final del layout (@stack('scripts')), cuando Bootstrap ya existe.

// 1. Reapertura automática del modal que tenga errores de validación
document.querySelectorAll('.modal[data-auto-open="true"]').forEach((modalEl) => {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});

// 2. Cambio de sección dinámica, buscador y paginación en cliente (10 por página)
const tabButtons = document.querySelectorAll('[data-gestora-tab]');
const tabPanels = document.querySelectorAll('[data-gestora-panel]');
const sectionTitle = document.getElementById('gestoraSectionTitle');
const newBtn = document.getElementById('gestoraNewBtn');
const searchInput = document.getElementById('gestoraSearchInput');

const ITEMS_PER_PAGE = 10;
const panelPages = {};
let selectedCommissionPeriod = null;

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
        placeholder: 'Buscar por nombre, dirección o zona...',
    },
    liquidaciones: {
        title: 'Mis liquidaciones',
        btnText: null,
        modalTarget: null,
        placeholder: 'Buscar por fecha, comunidad, servicio o importe...',
    },
};

const normalizeText = (text) =>
    text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

function getOrCreatePaginationBar(activePanel) {
    let bar = activePanel.querySelector('[data-table-pagination]');
    if (!bar) {
        const tableWrapper = activePanel.querySelector('.table-responsive');
        if (!tableWrapper) return null;

        bar = document.createElement('div');
        bar.setAttribute('data-table-pagination', 'true');
        bar.className = 'd-flex flex-wrap align-items-center justify-content-end gap-2 pt-3 mt-2 border-top border-secondary border-opacity-25';
        bar.innerHTML = `
            <span class="small text-white-50" data-pagination-info></span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Paginación de tabla">
                <button type="button" class="btn btn-outline-secondary text-light" data-page-prev title="Página anterior">‹</button>
                <button type="button" class="btn btn-outline-secondary text-light" data-page-next title="Página siguiente">›</button>
            </div>
        `;

        bar.querySelector('[data-page-prev]').addEventListener('click', () => {
            const current = panelPages[activePanel.id] || 1;
            if (current > 1) {
                panelPages[activePanel.id] = current - 1;
                filterActiveTable(false);
            }
        });

        bar.querySelector('[data-page-next]').addEventListener('click', () => {
            const current = panelPages[activePanel.id] || 1;
            panelPages[activePanel.id] = current + 1;
            filterActiveTable(false);
        });

        tableWrapper.insertAdjacentElement('afterend', bar);
    }
    return bar;
}

function filterActiveTable(resetPage = true) {
    const activePanel = document.querySelector('[data-gestora-panel]:not(.d-none)');
    if (!activePanel) return;

    const tbody = activePanel.querySelector('tbody');
    if (!tbody) return;

    if (resetPage) {
        panelPages[activePanel.id] = 1;
    }

    const query = searchInput ? normalizeText(searchInput.value) : '';
    const dataRows = Array.from(tbody.querySelectorAll('tr:not([data-empty-search])'));

    // Filtramos las filas según buscador y período mensual
    const matchedRows = dataRows.filter((row) => {
        const cells = Array.from(row.querySelectorAll('td')).filter(
            (td) => !td.querySelector('button, form')
        );
        const rowText = normalizeText(cells.map((td) => td.textContent).join(' '));
        const matchesQuery = query === '' || rowText.includes(query);

        const matchesPeriod =
            activePanel.id !== 'tab-liquidaciones' ||
            !selectedCommissionPeriod ||
            row.dataset.period === selectedCommissionPeriod;

        return matchesQuery && matchesPeriod;
    });

    // Paginación de 10 en 10 sobre los resultados filtrados
    const totalItems = matchedRows.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
    let currentPage = panelPages[activePanel.id] || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    panelPages[activePanel.id] = currentPage;

    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = startIndex + ITEMS_PER_PAGE;
    const visibleSet = new Set(matchedRows.slice(startIndex, endIndex));

    dataRows.forEach((row) => {
        row.classList.toggle('d-none', !visibleSet.has(row));
    });

    // Barra inferior siempre visible con flechas deshabilitadas cuando no hay más páginas
    const paginationBar = getOrCreatePaginationBar(activePanel);
    if (paginationBar) {
        if (totalItems === 0) {
            paginationBar.classList.add('d-none');
        } else {
            paginationBar.classList.remove('d-none');
            const from = startIndex + 1;
            const to = Math.min(endIndex, totalItems);
            paginationBar.querySelector('[data-pagination-info]').innerHTML =
                `Mostrando <span class="text-light fw-semibold">${from}-${to}</span> de <span class="text-light fw-semibold">${totalItems}</span>`;
            paginationBar.querySelector('[data-page-prev]').disabled = currentPage <= 1;
            paginationBar.querySelector('[data-page-next]').disabled = currentPage >= totalPages;
        }
    }

    // Fila informativa si no hay coincidencias
    let emptyRow = tbody.querySelector('tr[data-empty-search]');
    if (totalItems === 0 && dataRows.length > 0) {
        if (!emptyRow) {
            const colCount = activePanel.querySelectorAll('thead th').length || 1;
            emptyRow = document.createElement('tr');
            emptyRow.setAttribute('data-empty-search', 'true');
            emptyRow.innerHTML = `<td colspan="${colCount}" class="text-center text-white-50 py-4">No se encontraron resultados para "<span class="text-light"></span>".</td>`;
            tbody.appendChild(emptyRow);
        }
        emptyRow.querySelector('span').textContent =
            (searchInput && searchInput.value.trim()) || selectedCommissionPeriod || '';
        emptyRow.classList.remove('d-none');
    } else if (emptyRow) {
        emptyRow.classList.add('d-none');
    }
}

if (searchInput) {
    searchInput.addEventListener('input', () => filterActiveTable(true));
}

function activateGestoraTab(target) {
    const config = sectionConfig[target];
    if (!config) return;

    tabButtons.forEach((b) => {
        const active = b.dataset.gestoraTab === target;
        b.classList.toggle('btn-yellow', active);
        b.classList.toggle('btn-outline-yellow', !active);
        b.classList.toggle('active', active);
    });

    tabPanels.forEach((panel) => {
        panel.classList.toggle('d-none', panel.id !== `tab-${target}`);
    });

    if (sectionTitle) sectionTitle.textContent = config.title;
    if (newBtn) {
        if (config.btnText) {
            newBtn.classList.remove('d-none');
            newBtn.textContent = config.btnText;
            newBtn.setAttribute('data-bs-target', config.modalTarget);
        } else {
            newBtn.classList.add('d-none');
        }
    }
    if (searchInput) {
        searchInput.placeholder = config.placeholder;
    }

    filterActiveTable(false);
}

tabButtons.forEach((btn) => {
    btn.addEventListener('click', () => activateGestoraTab(btn.dataset.gestoraTab));
});

const gestoraPanel = document.getElementById('gestoraPanel');
if (gestoraPanel?.dataset.initialTab) {
    activateGestoraTab(gestoraPanel.dataset.initialTab);
} else {
    filterActiveTable(true);
}

// 3. Rellenar modal de edición de comunidad
document.querySelectorAll('.js-edit-community').forEach((btn) => {
    btn.addEventListener('click', () => {
        const form = document.getElementById('editCommunityForm');
        if (!form) return;

        const { action, id, name, address, zoneId } = btn.dataset;

        form.action = action;
        document.getElementById('edit_community_action').value = action;
        document.getElementById('edit_community_id').value = id;
        document.getElementById('edit_community_name').value = name;
        document.getElementById('edit_community_address').value = address;
        document.getElementById('edit_community_zone_id').value = zoneId ?? '';

        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    });
});

// 4. Filtro mensual al pulsar en tarjetas del carrusel de liquidaciones
const periodCards = document.querySelectorAll('[data-period-card]');
const clearPeriodBtn = document.getElementById('clearPeriodFilter');

const applyCommissionPeriodFilter = () => {
    periodCards.forEach((card) => {
        const isSelected = card.dataset.periodCard === selectedCommissionPeriod;
        card.classList.toggle('border-warning', isSelected);
        card.classList.toggle('border-secondary', !isSelected);
    });

    if (clearPeriodBtn) {
        clearPeriodBtn.classList.toggle('d-none', !selectedCommissionPeriod);
    }

    filterActiveTable(true);
};

periodCards.forEach((card) => {
    const togglePeriod = () => {
        const period = card.dataset.periodCard;
        selectedCommissionPeriod = selectedCommissionPeriod === period ? null : period;
        applyCommissionPeriodFilter();
    };

    card.addEventListener('click', togglePeriod);
    card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            togglePeriod();
        }
    });
});

if (clearPeriodBtn) {
    clearPeriodBtn.addEventListener('click', () => {
        selectedCommissionPeriod = null;
        applyCommissionPeriodFilter();
    });
}