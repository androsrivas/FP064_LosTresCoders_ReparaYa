<?php
/**
 * @var array<string, string[]> $errors
 * @var array<int, array> $incidencias
 * @var array<int, array> $tecnicos
 * @var array<int, array> $clientes
 * @var array<int, array> $especialidades
 */
?>

<div class="container-fluid py-4">
    <!-- Encabezado y Pestañas de Navegación (Orden según mockup: Crear servicio | Incidencias | Agenda) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-yellow" id="tabBtnCrear" onclick="mostrarSeccion('secCrear')">Crear servicio</button>
            <button type="button" class="btn btn-yellow active" id="tabBtnIncidencias" onclick="mostrarSeccion('secIncidencias')">Incidencias</button>
            <a href="<?= e(url('/calendario')) ?>" class="btn btn-outline-yellow">Agenda</a>
        </div>

        <!-- Barra de búsqueda universal (Solo visible en sección Incidencias) -->
        <div class="d-flex align-items-center" id="contenedorBuscador">
            <input type="text" id="inputBuscador" class="form-control" placeholder="Búsqueda de incidencia (estado, cliente, ref...)" style="min-width: 280px;">
        </div>
    </div>

    <?= field_errors($errors, 'general') ?>

    <!-- SECCIÓN 1: Listado de Incidencias -->
    <div id="secIncidencias" class="seccion-admin">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0 text-yellow">Listado General de Incidencias</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle" id="tablaIncidencias">
                        <thead>
                            <tr>
                                <th>Localizador</th>
                                <th>Cliente</th>
                                <th>Especialidad</th>
                                <th>Fecha Servicio</th>
                                <th>Urgencia</th>
                                <th>Estado</th>
                                <th>Técnico</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incidencias)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron incidencias registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($incidencias as $inc): ?>
                                    <tr class="fila-incidencia" data-estado="<?= e($inc['estado']) ?>">
                                        <td><strong><?= e($inc['localizador'] ?? '') ?></strong></td>
                                        <td><?= e($inc['nombre_cliente'] ?? 'Cliente N/A') ?></td>
                                        <td><?= e($inc['nombre_especialidad'] ?? 'General') ?></td>
                                        <td><?= e($inc['fecha_servicio'] ?? '') ?></td>
                                        <td>
                                            <?php $esUrgente = strtolower($inc['tipo_urgencia'] ?? '') === 'urgente'; ?>
                                            <span class="badge <?= $esUrgente ? 'bg-danger text-white' : 'badge-yellow' ?>">
                                                <?= e($inc['tipo_urgencia'] ?? 'Estándar') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= e($inc['estado']) ?></span>
                                        </td>
                                        <td><?= e($inc['nombre_tecnico'] ?? 'Sin asignar') ?></td>
                                        <td class="text-end">
                                            <button type="button"
                                            class="btn btn-warning btn-sm fw-bold px-3 btn-ver-incidencia"
                                            data-incidencia-id="<?= (int)$inc['id']; ?>">
                                            Ver
                                            </button>
                                            
                                            <?php if (in_array($inc['estado'], ['Pendiente', 'Asignada'], true)): ?>
                                                <button type="button" class="btn btn-sm btn-yellow" 
                                                        onclick="abrirModalGestion(<?= htmlspecialchars(json_encode($inc), ENT_QUOTES, 'UTF-8') ?>)">
                                                    Gestionar
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: Formulario de Alta Manual de Servicio -->
    <div id="secCrear" class="seccion-admin" style="display: none;">
        <div class="card shadow-sm max-w-2xl mx-auto">
            <div class="card-header">
                <h3 class="h5 mb-0 text-yellow">Crear Nueva Incidencia Manualmente</h3>
            </div>
            <div class="card-body">
                <form method="post" action="<?= e(url('/admin/crear')) ?>">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select id="cliente_id" name="cliente_id" class="form-select" required>
                                <option value="">Seleccione un cliente...</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= e((string)$c['id']) ?>"><?= e($c['nombre']) ?> (<?= e($c['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="especialidad_id" class="form-label">Especialidad / Tipo Servicio</label>
                            <select id="especialidad_id" name="especialidad_id" class="form-select" required>
                                <option value="">Seleccione especialidad...</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?= e((string)$esp['id']) ?>"><?= e($esp['nombre_especialidad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo_urgencia" class="form-label">Tipo de Urgencia</label>
                            <select id="tipo_urgencia" name="tipo_urgencia" class="form-select" required>
                                <option value="Estándar">Estándar (Min. 48h antelación)</option>
                                <option value="Urgente">Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_servicio" class="form-label">Fecha y Hora Solicitada</label>
                            <input type="datetime-local" id="fecha_servicio" name="fecha_servicio" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección del Servicio</label>
                            <input type="text" id="direccion" name="direccion" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción de la Avería</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-outline-yellow me-2" onclick="mostrarSeccion('secIncidencias')">Cancelar</button>
                        <button type="submit" class="btn btn-yellow">Registrar Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE GESTIÓN (Asignar / Modificar / Cancelar con motivo según mockup) -->
<div class="modal fade" id="modalGestionIncidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-yellow" id="modalTituloGestion">Gestionar Incidencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= e(url('/admin/modificar')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" id="modal_incidencia_id" name="incidencia_id" value="">

                <div class="modal-body">
                    <p class="text-muted mb-3">Modifique la asignación, los datos o introduzca un motivo de actualización.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_tecnico_id" class="form-label">Asignar Técnico</label>
                            <select id="modal_tecnico_id" name="tecnico_id" class="form-select">
                                <option value="">Sin Asignar (Devolver a Pendiente)</option>
                                <?php foreach ($tecnicos as $t): ?>
                                    <option value="<?= e((string)$t['id']) ?>"><?= e($t['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_estado" class="form-label">Estado</label>
                            <select id="modal_estado" name="estado" class="form-select">
                                <option value="Pendiente">Pendiente</option>
                                <option value="Asignada">Asignada</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_tipo_urgencia" class="form-label">Urgencia</label>
                            <select id="modal_tipo_urgencia" name="tipo_urgencia" class="form-select">
                                <option value="Estándar">Estándar</option>
                                <option value="Urgente">Urgente</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_fecha_servicio" class="form-label">Fecha del Servicio</label>
                            <input type="datetime-local" id="modal_fecha_servicio" name="fecha_servicio" class="form-control">
                        </div>

                        <div class="col-12">
                            <label for="modal_direccion" class="form-label">Dirección</label>
                            <input type="text" id="modal_direccion" name="direccion" class="form-control">
                        </div>

                        <div class="col-12">
                            <label for="modal_comentario" class="form-label">Motivo o Comentario de Auditoría (Historial)</label>
                            <textarea id="modal_comentario" name="comentario" class="form-control" rows="2" placeholder="Escriba la razón de la modificación o asignación..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-yellow" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-yellow">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS DE INTERACCIÓN DE LA VISTA -->
<script>
function mostrarSeccion(idSeccion) {
    // Ocultar todas las secciones
    document.querySelectorAll('.seccion-admin').forEach(el => el.style.display = 'none');
    
    // Mostrar sección seleccionada
    document.getElementById(idSeccion).style.display = 'block';

    // Ajustar estilos de botones
    document.getElementById('tabBtnIncidencias').className = idSeccion === 'secIncidencias' ? 'btn btn-yellow active' : 'btn btn-outline-yellow';
    document.getElementById('tabBtnCrear').className = idSeccion === 'secCrear' ? 'btn btn-yellow active' : 'btn btn-outline-yellow';

    // Requisito del mockup: La barra de búsqueda SOLO es visible si la sección de Incidencias está activa
    const contenedorBuscador = document.getElementById('contenedorBuscador');
    if (contenedorBuscador) {
        contenedorBuscador.style.display = idSeccion === 'secIncidencias' ? 'flex' : 'none';
    }
}

// Filtro universal en tiempo real por cualquier columna de la tabla
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscador = document.getElementById('inputBuscador');
    const filas = document.querySelectorAll('#tablaIncidencias tbody tr.fila-incidencia');

    if (inputBuscador) {
        inputBuscador.addEventListener('input', function() {
            const termino = this.value.toLowerCase().trim();

            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(termino) ? '' : 'none';
            });
        });
    }
});

// Abrir modal de gestión con datos precargados
function abrirModalGestion(incidencia) {
    document.getElementById('modal_incidencia_id').value = incidencia.id;
    document.getElementById('modalTituloGestion').textContent = 'Gestionar Incidencia: ' + (incidencia.localizador || '');
    document.getElementById('modal_tecnico_id').value = incidencia.tecnico_id || '';
    document.getElementById('modal_estado').value = incidencia.estado || 'Pendiente';
    document.getElementById('modal_tipo_urgencia').value = incidencia.tipo_urgencia || 'Estándar';
    document.getElementById('modal_direccion').value = incidencia.direccion || '';
    
    if (incidencia.fecha_servicio) {
        document.getElementById('modal_fecha_servicio').value = incidencia.fecha_servicio.replace(' ', 'T').substring(0, 16);
    }

    var modal = new bootstrap.Modal(document.getElementById('modalGestionIncidencia'));
    modal.show();
}
</script>

<!-- INCLUSIÓN DEL MODAL REUTILIZABLE DE DETALLE DE INCIDENCIA -->
<?php require __DIR__ . '/../incidencias/_modal_detalle.php'; ?>

<!-- SCRIPT GENERAL PARA LA CARGA DINÁMICA DEL DETALLE VÍA AJAX -->
<script>
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.btn-ver-incidencia');
    if (!trigger) return;

    const incidenciaId = trigger.dataset.incidenciaId;
    if (!incidenciaId) return;

    const modalEl = document.getElementById('detalleIncidenciaModal');
    const contenedorContent = document.getElementById('contenidoDetalleIncidencia');

    if (!modalEl || !contenedorContent) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Muestra spinner inicial
    contenedorContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="text-muted small mt-2">Cargando información del servicio...</p>
        </div>`;

    modal.show();

    // Petición AJAX al controlador (Sintaxis corregida)
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
</script>