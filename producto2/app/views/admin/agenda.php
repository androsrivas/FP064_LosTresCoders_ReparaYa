<?php
/** @var array $incidencias */
/** @var array $tecnico */
/** @var string $titulo */

use App\Core\Auth;
use App\Core\Roles;

$user = Auth::user();
$userRol = $user['rol'] ?? 'tecnico';
$esAdmin = ($userRol === Roles::ADMIN || $userRol === 'admin');

// Ordenar incidencias por fecha_servicio descendiente (más reciente arriba, más antigua abajo)
usort($incidencias, function ($a, $b) {
    return strtotime($b['fecha_servicio']) <=> strtotime($a['fecha_servicio']);
});

// Agrupar incidencias por fecha (Y-m-d) para pintar rápido en el calendario
$incidenciasPorFecha = [];
foreach ($incidencias as $inc) {
    $fechaClave = date('Y-m-d', strtotime($inc['fecha_servicio']));
    $incidenciasPorFecha[$fechaClave][] = $inc;
}

$clasesEstado = [
    'Finalizada' => 'bg-success text-white',
    'Asignada'   => 'bg-info text-dark',
    'En curso'   => 'bg-warning text-dark',
    'Cancelada'  => 'bg-danger text-white',
    'Pendiente'  => 'bg-secondary text-white',
];
?>

<div class="container-fluid py-4 text-white">
    <!-- CABECERA Y SECTOR DE VISTAS -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom border-secondary pb-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-calendar-week me-2 text-warning"></i><?= e($titulo ?? 'Agenda de Servicios'); ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= $esAdmin ? 'Panel global de agenda de administración' : 'Gestión y seguimiento de tus incidencias asignadas'; ?>
            </p>
        </div>

        <!-- Botones conmutadores de vista -->
        <div class="btn-group" role="group" aria-label="Cambiar Vista">
            <button type="button" class="btn btn-outline-warning active" id="btnVistaMensual" onclick="cambiarVistaAgenda('mensual')">
                <i class="bi bi-calendar3 me-1"></i>Mensual
            </button>
            <button type="button" class="btn btn-outline-warning" id="btnVistaSemanal" onclick="cambiarVistaAgenda('semanal')">
                <i class="bi bi-calendar-range me-1"></i>Semanal
            </button>
            <button type="button" class="btn btn-outline-warning" id="btnVistaDiaria" onclick="cambiarVistaAgenda('diaria')">
                <i class="bi bi-calendar-day me-1"></i>Diario
            </button>
        </div>
    </div>

    <!-- MAIN GRID CONTAINER -->
    <div class="row g-4">
        <!-- COLUMNA IZQUIERDA: CALENDARIO DINÁMICO -->
        <div class="col-lg-8 col-xl-9">
            <div class="card bg-dark border-secondary shadow-sm">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fw-bold text-light" id="tituloCalendario">
                        <!-- Generado por JS -->
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary text-light" onclick="navegarPeriodo(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary text-light" onclick="navegarPeriodo(0)">
                            Hoy
                        </button>
                        <button type="button" class="btn btn-outline-secondary text-light" onclick="navegarPeriodo(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- VISTA MENSUAL -->
                    <div id="contenedorMensual">
                        <div class="row g-0 text-center fw-bold text-muted border-bottom border-secondary mb-2 pb-2 small">
                            <div class="col">Lun</div>
                            <div class="col">Mar</div>
                            <div class="col">Mié</div>
                            <div class="col">Jue</div>
                            <div class="col">Vie</div>
                            <div class="col">Sáb</div>
                            <div class="col">Dom</div>
                        </div>
                        <div id="gridCalendarioMensual" class="row g-1"></div>
                    </div>

                    <!-- VISTA SEMANAL -->
                    <div id="contenedorSemanal" class="d-none">
                        <div id="gridCalendarioSemanal" class="row g-2"></div>
                    </div>

                    <!-- VISTA DIARIA -->
                    <div id="contenedorDiario" class="d-none">
                        <div id="gridCalendarioDiario" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: TARJETAS DE INCIDENCIAS (FECHA DESCENDENTE) -->
        <div class="col-lg-4 col-xl-3">
            <div class="card bg-dark border-secondary shadow-sm h-100">
                <div class="card-header border-secondary py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-light">
                        <i class="bi bi-card-checklist me-2 text-warning"></i>Incidencias Registradas
                    </h6>
                    <span class="badge bg-secondary rounded-pill"><?= count($incidencias); ?></span>
                </div>
                <div class="card-body p-3 overflow-auto" style="max-height: 75vh;">
                    <?php if (empty($incidencias)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            <p class="mb-0">No hay incidencias registradas en la agenda.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($incidencias as $inc): ?>
                                <?php 
                                $badgeClass = $clasesEstado[$inc['estado'] ?? 'Pendiente'] ?? 'bg-secondary text-white';
                                $fechaFormat = date('d/m/Y H:i', strtotime($inc['fecha_servicio']));
                                ?>
                                <div class="card bg-black bg-opacity-40 border-secondary hover-border-warning transition-all">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-secondary small">#<?= e($inc['localizador'] ?? $inc['id']); ?></span>
                                            <span class="badge <?= $badgeClass; ?> small"><?= e($inc['estado']); ?></span>
                                        </div>
                                        <h6 class="card-title text-warning mb-1 text-truncate">
                                            <?= e($inc['nombre_especialidad'] ?? $inc['especialidad'] ?? 'Servicio General'); ?>
                                        </h6>
                                        <p class="card-text text-light small mb-2 text-multiline-truncate" style="-webkit-line-clamp: 2; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= e($inc['descripcion'] ?? 'Sin descripción disponible.'); ?>
                                        </p>
                                        <div class="text-muted extra-small mb-3">
                                            <i class="bi bi-clock me-1"></i><?= $fechaFormat; ?>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-outline-warning btn-sm w-100 btn-ver-incidencia" 
                                                data-incidencia-id="<?= (int)$inc['id']; ?>">
                                            <i class="bi bi-eye me-1"></i>Ver Detalle
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INCLUSIÓN DEL MODAL REUTILIZABLE -->
<?php require __DIR__ . '/../incidencias/_modal_detalle.php'; ?>

<!-- ESTILOS ADICIONALES PARA LA AGENDA -->
<style>
/* ESTILOS ADICIONALES PARA LA AGENDA */
#gridCalendarioMensual {
    display: grid;
    grid-template-columns: repeat(7, 1fr); /* 7 columnas idénticas para los 7 días */
    gap: 4px;
}

.celda-dia {
    min-height: 90px;
    height: 90px; /* Altura uniforme para todas las celdas */
    background-color: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 4px;
    padding: 6px;
    transition: background-color 0.2s ease;
    display: flex;
    flex-direction: column;
    overflow-y: auto; /* Permite scroll discreto si hay muchos eventos en un solo día */
}

.celda-dia::-webkit-scrollbar {
    width: 3px;
}
.celda-dia::-webkit-scrollbar-thumb {
    background: rgba(255, 193, 7, 0.4);
    border-radius: 3px;
}

.celda-dia:hover {
    background-color: rgba(255, 193, 7, 0.05);
}

.celda-dia.fuera-mes {
    opacity: 0.35;
}

.celda-dia.hoy {
    border: 1px solid #ffc107;
}

.evento-item {
    font-size: 0.7rem;
    padding: 2px 4px;
    border-radius: 3px;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.hover-border-warning:hover {
    border-color: #ffc107 !important;
}

.extra-small {
    font-size: 0.75rem;
}
</style>

<!-- LÓGICA JAVASCRIPT DEL CALENDARIO Y MODAL -->
<script>
const datosIncidencias = <?= json_encode($incidenciasPorFecha, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
let vistaActual = 'mensual';
let fechaReferencia = new Date();

document.addEventListener('DOMContentLoaded', function () {
    renderizarCalendario();
});

function cambiarVistaAgenda(nuevaVista) {
    vistaActual = nuevaVista;

    document.querySelectorAll('.btn-group button').forEach(btn => btn.classList.remove('active'));
    document.getElementById('contenedorMensual').classList.add('d-none');
    document.getElementById('contenedorSemanal').classList.add('d-none');
    document.getElementById('contenedorDiario').classList.add('d-none');

    if (nuevaVista === 'mensual') {
        document.getElementById('btnVistaMensual').classList.add('active');
        document.getElementById('contenedorMensual').classList.remove('d-none');
    } else if (nuevaVista === 'semanal') {
        document.getElementById('btnVistaSemanal').classList.add('active');
        document.getElementById('contenedorSemanal').classList.remove('d-none');
    } else {
        document.getElementById('btnVistaDiaria').classList.add('active');
        document.getElementById('contenedorDiario').classList.remove('d-none');
    }

    renderizarCalendario();
}

function navegarPeriodo(delta) {
    if (delta === 0) {
        fechaReferencia = new Date();
    } else if (vistaActual === 'mensual') {
        fechaReferencia.setMonth(fechaReferencia.getMonth() + delta);
    } else if (vistaActual === 'semanal') {
        fechaReferencia.setDate(fechaReferencia.getDate() + (delta * 7));
    } else if (vistaActual === 'diaria') {
        fechaReferencia.setDate(fechaReferencia.getDate() + delta);
    }
    renderizarCalendario();
}

function renderizarCalendario() {
    const tituloEl = document.getElementById('tituloCalendario');
    if (!tituloEl) return;
    
    if (vistaActual === 'mensual') {
        const mesNombre = fechaReferencia.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
        tituloEl.textContent = mesNombre.charAt(0).toUpperCase() + mesNombre.slice(1);
        renderizarMensual();
    } else if (vistaActual === 'semanal') {
        tituloEl.textContent = "Vista Semanal";
        renderizarSemanal();
    } else {
        tituloEl.textContent = fechaReferencia.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        renderizarDiario();
    }
}

function renderizarMensual() {
    const grid = document.getElementById('gridCalendarioMensual');
    if (!grid) return;
    grid.innerHTML = '';

    const año = fechaReferencia.getFullYear();
    const mes = fechaReferencia.getMonth();

    const primerDiaMes = new Date(año, mes, 1);
    const ultimoDiaMes = new Date(año, mes + 1, 0);

    let diaSemanaInicio = primerDiaMes.getDay() - 1; 
    if (diaSemanaInicio === -1) diaSemanaInicio = 6;

    const totalDiasPrevios = diaSemanaInicio;
    const totalDiasMes = ultimoDiaMes.getDate();

    const hoyStr = (new Date()).toISOString().split('T')[0];

    // Días del mes anterior
    const ultimoDiaMesAnterior = new Date(año, mes, 0).getDate();
    for (let i = totalDiasPrevios - 1; i >= 0; i--) {
        const numDia = ultimoDiaMesAnterior - i;
        grid.appendChild(crearCeldaDia(numDia, true));
    }

    // Días del mes actual
    for (let d = 1; d <= totalDiasMes; d++) {
        const mStr = String(mes + 1).padStart(2, '0');
        const dStr = String(d).padStart(2, '0');
        const claveFecha = `${año}-${mStr}-${dStr}`;
        const esHoy = (claveFecha === hoyStr);

        grid.appendChild(crearCeldaDia(d, false, esHoy, datosIncidencias[claveFecha] || []));
    }
}

function crearCeldaDia(numeroDia, esFueraDeMes, esHoy = false, listaIncidencias = []) {
    const celda = document.createElement('div');
    celda.className = `celda-dia ${esFueraDeMes ? 'fuera-mes' : ''} ${esHoy ? 'hoy' : ''}`;
    
    const badgeDia = document.createElement('div');
    badgeDia.className = `fw-bold mb-1 small ${esHoy ? 'text-warning' : 'text-muted'}`;
    badgeDia.textContent = numeroDia;
    celda.appendChild(badgeDia);

    listaIncidencias.forEach(inc => {
        const item = document.createElement('div');
        item.className = `evento-item btn-ver-incidencia ${obtenerBadgeClase(inc.estado)}`;
        item.dataset.incidenciaId = inc.id;
        item.title = `#${inc.localizador || inc.id} - ${inc.nombre_especialidad || 'Servicio'}`;
        item.textContent = `#${inc.localizador || inc.id} - ${inc.nombre_especialidad || 'Servicio'}`;
        celda.appendChild(item);
    });

    return celda;
}

function renderizarSemanal() {
    const grid = document.getElementById('gridCalendarioSemanal');
    if (!grid) return;
    grid.innerHTML = '';

    const curr = new Date(fechaReferencia);
    const first = curr.getDate() - (curr.getDay() === 0 ? 6 : curr.getDay() - 1);

    for (let i = 0; i < 7; i++) {
        const next = new Date(curr.setDate(first + i));
        const dateStr = next.toISOString().split('T')[0];
        const incidencias = datosIncidencias[dateStr] || [];

        const col = document.createElement('div');
        col.className = 'col-md-12';
        col.innerHTML = `
            <div class="card bg-black bg-opacity-25 border-secondary mb-1">
                <div class="card-header py-1 px-3 bg-dark border-secondary d-flex justify-content-between text-warning small fw-bold">
                    <span>${next.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'short' })}</span>
                    <span class="badge bg-secondary">${incidencias.length} incidencias</span>
                </div>
                <div class="card-body p-2">
                    ${incidencias.map(inc => `
                        <div class="d-flex justify-content-between align-items-center p-2 mb-1 bg-dark border border-secondary rounded btn-ver-incidencia" data-incidencia-id="${inc.id}" style="cursor:pointer;">
                            <span class="small text-light fw-bold">#${inc.localizador || inc.id} - ${inc.nombre_especialidad || 'Servicio'}</span>
                            <span class="badge ${obtenerBadgeClase(inc.estado)}">${inc.estado}</span>
                        </div>
                    `).join('') || '<span class="text-muted extra-small p-1 d-block">Sin servicios este día.</span>'}
                </div>
            </div>
        `;
        grid.appendChild(col);
    }
}

function renderizarDiario() {
    const grid = document.getElementById('gridCalendarioDiario');
    if (!grid) return;
    grid.innerHTML = '';

    const dateStr = fechaReferencia.toISOString().split('T')[0];
    const incidencias = datosIncidencias[dateStr] || [];

    if (incidencias.length === 0) {
        grid.innerHTML = `
            <div class="text-center py-5 text-muted border border-secondary rounded">
                <i class="bi bi-calendar-event fs-1 d-block mb-2"></i>
                <p class="mb-0">No hay incidencias programadas para el día seleccionado.</p>
            </div>
        `;
        return;
    }

    incidencias.forEach(inc => {
        const item = document.createElement('div');
        item.className = 'p-3 bg-dark border border-secondary rounded d-flex justify-content-between align-items-center btn-ver-incidencia';
        item.dataset.incidenciaId = inc.id;
        item.style.cursor = 'pointer';
        item.innerHTML = `
            <div>
                <h6 class="mb-1 text-warning fw-bold">#${inc.localizador || inc.id} - ${inc.nombre_especialidad || 'Servicio'}</h6>
                <p class="mb-0 small text-light">${inc.descripcion || 'Sin descripción'}</p>
            </div>
            <span class="badge ${obtenerBadgeClase(inc.estado)} px-3 py-2">${inc.estado}</span>
        `;
        grid.appendChild(item);
    });
}

function obtenerBadgeClase(estado) {
    switch (estado) {
        case 'Finalizada': return 'bg-success text-white';
        case 'Asignada':   return 'bg-info text-dark';
        case 'En curso':   return 'bg-warning text-dark';
        case 'Cancelada':  return 'bg-danger text-white';
        default:           return 'bg-secondary text-white';
    }
}

// SCRIPT GENERAL PARA LA CARGA DINÁMICA DEL MODAL DE DETALLE VÍA AJAX
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.btn-ver-incidencia');
    if (!trigger) return;

    const incidenciaId = trigger.dataset.incidenciaId;
    if (!incidenciaId) return;

    const modalEl = document.getElementById('detalleIncidenciaModal');
    const contenedorContent = document.getElementById('contenidoDetalleIncidencia');

    if (!modalEl || !contenedorContent) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    contenedorContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="text-muted small mt-2">Cargando información del servicio...</p>
        </div>`;

    modal.show();

    fetch(`<?= e(url('/incidencia/detalle/')); ?>${incidenciaId}`)
        .then(response => {
            if (!response.ok) throw new Error('Error de permisos o servicio');
            return response.text();
        })
        .then(html => {
            contenedorContent.innerHTML = html;
        })
        .catch(err => {
            contenedorContent.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-octagon me-2"></i>No se pudo cargar la información del servicio.
                </div>`;
        });
});

document.addEventListener('submit', function (e) {
    const form = e.target.closest('.form-cambiar-estado-tecnico');
    if (!form) return;

    e.preventDefault();

    const formData = new FormData(form);
    const incidenciaId = formData.get('incidencia_id');

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Error al actualizar el estado');
        return fetch(`<?= e(url('/incidencia/detalle/')); ?>${incidenciaId}`);
    })
    .then(response => response.text())
    .then(html => {
        const contenedorContent = document.getElementById('contenidoDetalleIncidencia');
        if (contenedorContent) {
            contenedorContent.innerHTML = html;
        }
        if (typeof renderizarCalendario === 'function') {
            window.location.reload();
        }
    })
    .catch(err => {
        alert('Ocurrió un error al intentar cambiar el estado de la incidencia.');
    });
});
</script>