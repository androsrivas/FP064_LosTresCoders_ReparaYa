<?php
/**
 * @var array<string, string[]> $errors
 * @var array<int, array> $incidencias
 * @var array<int, array> $tecnicos
 * @var array<int, array> $clientes
 * @var array<int, array> $especialidades
 */
$clasesEstado = [
    'Finalizada' => 'badge-status-finalizada',
    'Asignada'   => 'badge-status-asignada',
    'En curso'   => 'badge-status-en-curso',
    'Cancelada'  => 'badge-status-cancelada',
    'Pendiente'  => 'badge-status-pendiente',
];
?>

<div class="container-fluid py-4">
    <!-- Encabezado y Pestañas de Navegación -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="btn-group flex-wrap" role="group">
            <button type="button" class="btn btn-outline-yellow" id="tabBtnCrear"
                onclick="mostrarSeccion('secCrear')">Crear servicio</button>
            <button type="button" class="btn btn-yellow active" id="tabBtnIncidencias"
                onclick="mostrarSeccion('secIncidencias')">Incidencias</button>
            <button type="button" class="btn btn-outline-yellow" id="tabBtnTecnicos"
                onclick="mostrarSeccion('secTecnicos')">Técnicos</button>
            <button type="button" class="btn btn-outline-yellow" id="tabBtnServicios"
                onclick="mostrarSeccion('secServicios')">Especialidades</button>
            <a href="<?= e(url('/agenda')) ?>" class="btn btn-outline-yellow">Agenda</a>
        </div>

        <!-- Barra de búsqueda universal (Visible en las tablas de Incidencias, Técnicos y Especialidades) -->
        <div class="d-flex align-items-center" id="contenedorBuscador">
            <input type="text" id="inputBuscador" class="form-control color-buscador"
                placeholder="Búsqueda rápida en el listado..." style="min-width: 280px;">
        </div>
    </div>

    <?= field_errors($errors, 'general') ?>

    <!-- ===================================================================== -->
    <!-- SECCIÓN 1: Listado de Incidencias                                     -->
    <!-- ===================================================================== -->
    <div id="secIncidencias" class="seccion-admin">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0 text-yellow">Listado General de Incidencias</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle tabla-filtrable" id="tablaIncidencias">
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
                                    <tr class="fila-incidencia fila-filtrable" data-estado="<?= e($inc['estado']) ?>">
                                        <td><strong><?= e($inc['localizador'] ?? '') ?></strong></td>
                                        <td><?= e($inc['nombre_cliente'] ?? 'Cliente N/A') ?></td>
                                        <td><?= e($inc['nombre_especialidad'] ?? 'General') ?></td>
                                        <td><?= e($inc['fecha_servicio'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $esUrgente = strtolower($inc['tipo_urgencia'] ?? '') === 'urgente';
                                            $badgeUrgenciaClass = $esUrgente ? 'badge-urgencia-urgente' : 'badge-urgencia-estandar';
                                            ?>
                                            <span class="badge-status d-inline-block <?= $badgeUrgenciaClass ?>">
                                                <?= e($inc['tipo_urgencia'] ?? 'Estándar') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoActual = $inc['estado'] ?? 'Pendiente';
                                            $badgeEstadoClass = $clasesEstado[$estadoActual] ?? 'badge-status-pendiente';
                                            ?>
                                            <span class="badge-status d-inline-block <?= $badgeEstadoClass ?>">
                                                <?= e($estadoActual) ?>
                                            </span>
                                        </td>
                                        <td><?= e($inc['nombre_tecnico'] ?? 'Sin asignar') ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-warning btn-sm fw-bold px-3 btn-ver-incidencia"
                                                data-incidencia-id="<?= (int) $inc['id']; ?>">
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

    <!-- ===================================================================== -->
    <!-- SECCIÓN 2: Formulario de Alta Manual de Servicio                      -->
    <!-- ===================================================================== -->
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
                                    <option value="<?= e((string) $c['id']) ?>"><?= e($c['nombre']) ?>
                                        (<?= e($c['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="especialidad_id" class="form-label">Especialidad / Tipo Servicio</label>
                            <select id="especialidad_id" name="especialidad_id" class="form-select" required>
                                <option value="">Seleccione especialidad...</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?= e((string) $esp['id']) ?>"><?= e($esp['nombre_especialidad']) ?></option>
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
                        <button type="button" class="btn btn-outline-yellow me-2"
                            onclick="mostrarSeccion('secIncidencias')">Cancelar</button>
                        <button type="submit" class="btn btn-yellow">Registrar Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- SECCIÓN 3: CRUD de Técnicos                                           -->
    <!-- ===================================================================== -->
    <div id="secTecnicos" class="seccion-admin" style="display: none;">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0 text-yellow">Gestión de Técnicos</h3>
                <button type="button" class="btn btn-yellow btn-sm fw-bold" onclick="abrirModalTecnico()">
                    <i class="bi bi-person-plus-fill me-1"></i>Nuevo Técnico
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle tabla-filtrable" id="tablaTecnicos">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tecnicos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay técnicos registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tecnicos as $tec): ?>
                                    <tr class="fila-filtrable">
                                        <td><strong><?= e($tec['nombre_completo'] ?? $tec['nombre'] ?? '') ?></strong></td>
                                        <td><?= e($tec['email'] ?? '—') ?></td>
                                        <td><?= e($tec['telefono'] ?? '—') ?></td>
                                        <td><?= e($tec['nombre_especialidad'] ?? 'Sin especialidad') ?></td>
                                        <td>
                                            <?php $estaDisponible = (bool) ($tec['disponible'] ?? true); ?>
                                            <span class="badge-status d-inline-block <?= $estaDisponible ? 'badge-status-finalizada' : 'badge-status-cancelada' ?>">
                                                <?= $estaDisponible ? 'Disponible' : 'No disponible' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-yellow me-1"
                                                onclick="abrirModalTecnico(<?= htmlspecialchars(json_encode($tec), ENT_QUOTES, 'UTF-8') ?>)">
                                                Editar
                                            </button>
                                            <form method="post" action="<?= e(url('/gestion/tecnicos/' . (int)$tec['id'] . '/eliminar')) ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar a este técnico?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Eliminar
                                                </button>
                                            </form>
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

    <!-- ===================================================================== -->
    <!-- SECCIÓN 4: CRUD de Especialidades / Tipos de Servicio                 -->
    <!-- ===================================================================== -->
    <div id="secServicios" class="seccion-admin" style="display: none;">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0 text-yellow">Gestión de Especialidades / Tipos de Servicio</h3>
                <button type="button" class="btn btn-yellow btn-sm fw-bold" onclick="abrirModalServicio()">
                    <i class="bi bi-plus-circle-fill me-1"></i>Nueva Especialidad
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle tabla-filtrable" id="tablaServicios">
                        <thead>
                            <tr>
                                <th>Especialidad</th>
                                <th>Descripción</th>
                                <th>Salario Base</th>
                                <th>Precio Base</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($especialidades)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay especialidades registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($especialidades as $srv): ?>
                                    <tr class="fila-filtrable">
                                        <td><strong><?= e($srv['nombre_especialidad'] ?? '') ?></strong></td>
                                        <td class="text-muted small"><?= e($srv['descripcion'] ?? '—') ?></td>
                                        <td><?= $srv['salario_base'] !== null && $srv['salario_base'] !== '' ? e((string)$srv['salario_base']) . ' €' : '—' ?></td>
                                        <td><span class="badge-status d-inline-block badge-urgencia-estandar"><?= e((string)($srv['precio_base'] ?? '0.00')) ?> €</span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-yellow me-1"
                                                onclick="abrirModalServicio(<?= htmlspecialchars(json_encode($srv), ENT_QUOTES, 'UTF-8') ?>)">
                                                Editar
                                            </button>
                                            <form method="post" action="<?= e(url('/gestion/servicios/' . (int)$srv['id'] . '/eliminar')) ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar esta especialidad?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Eliminar
                                                </button>
                                            </form>
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
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: GESTIÓN DE INCIDENCIAS (Existente)                               -->
<!-- ========================================================================= -->
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
                                    <option value="<?= e((string) $t['id']) ?>"><?= e($t['nombre_completo']) ?></option>
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
                            <textarea id="modal_comentario" name="comentario" class="form-control" rows="2"
                                placeholder="Escriba la razón de la modificación o asignación..."></textarea>
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

<!-- ========================================================================= -->
<!-- MODAL 2: CREAR / EDITAR TÉCNICO                                           -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCrudTecnico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-yellow" id="modalTituloTecnico">Nuevo Técnico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="formCrudTecnico" action="<?= e(url('/gestion/tecnicos/crear')) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="tec_nombre_completo" class="form-label">Nombre Completo</label>
                            <input type="text" id="tec_nombre_completo" name="nombre_completo" class="form-control" maxlength="100" required>
                        </div>

                        <div class="col-md-6">
                            <label for="tec_email" class="form-label">Correo Electrónico</label>
                            <input type="email" id="tec_email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="tec_telefono" class="form-label">Teléfono (Opcional)</label>
                            <input type="text" id="tec_telefono" name="telefono" class="form-control">
                        </div>

                        <div class="col-12" id="grupoPasswordTecnico">
                            <label for="tec_password" class="form-label">Contraseña Inicial (mín. 6 caracteres)</label>
                            <input type="password" id="tec_password" name="password" class="form-control" minlength="6">
                        </div>

                        <div class="col-12">
                            <label for="tec_especialidad_id" class="form-label">Especialidad</label>
                            <select id="tec_especialidad_id" name="especialidad_id" class="form-select">
                                <option value="">Sin especialidad asignada</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?= e((string) $esp['id']) ?>"><?= e($esp['nombre_especialidad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="tec_disponible" name="disponible" value="1" checked>
                                <label class="form-check-label" for="tec_disponible">Técnico disponible para asignaciones</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-yellow" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-yellow" id="btnSubmitTecnico">Guardar Técnico</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: CREAR / EDITAR ESPECIALIDAD (TIPO DE SERVICIO)                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCrudServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-yellow" id="modalTituloServicio">Nueva Especialidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="formCrudServicio" action="<?= e(url('/gestion/servicios/crear')) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="srv_nombre" class="form-label">Nombre de la Especialidad</label>
                            <input type="text" id="srv_nombre" name="nombre_especialidad" class="form-control" maxlength="50" required>
                        </div>

                        <div class="col-md-6">
                            <label for="srv_precio_base" class="form-label">Precio Base (€)</label>
                            <input type="number" step="0.01" min="0" id="srv_precio_base" name="precio_base" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="srv_salario_base" class="form-label">Salario Base (€) (Opcional)</label>
                            <input type="number" step="0.01" min="0" id="srv_salario_base" name="salario_base" class="form-control">
                        </div>

                        <div class="col-12">
                            <label for="srv_descripcion" class="form-label">Descripción</label>
                            <textarea id="srv_descripcion" name="descripcion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="srv_responsabilidades" class="form-label">Responsabilidades (Opcional)</label>
                            <textarea id="srv_responsabilidades" name="responsabilidades" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-yellow" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-yellow" id="btnSubmitServicio">Guardar Especialidad</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS DE INTERACCIÓN DE LA VISTA -->
<script>
    const URL_BASE_TECNICOS = <?= json_encode(url('/gestion/tecnicos')) ?>;
    const URL_BASE_SERVICIOS = <?= json_encode(url('/gestion/servicios')) ?>;

    function mostrarSeccion(idSeccion) {
        // Ocultar todas las secciones
        document.querySelectorAll('.seccion-admin').forEach(el => el.style.display = 'none');

        // Mostrar sección seleccionada
        document.getElementById(idSeccion).style.display = 'block';

        // Ajustar estilos de botones de pestaña
        const tabs = {
            'secCrear': 'tabBtnCrear',
            'secIncidencias': 'tabBtnIncidencias',
            'secTecnicos': 'tabBtnTecnicos',
            'secServicios': 'tabBtnServicios'
        };

        Object.entries(tabs).forEach(([sec, btnId]) => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.className = sec === idSeccion ? 'btn btn-yellow active' : 'btn btn-outline-yellow';
            }
        });

        // Mostrar barra de búsqueda en las secciones de tablas y resetear filtro
        const contenedorBuscador = document.getElementById('contenedorBuscador');
        const inputBuscador = document.getElementById('inputBuscador');
        if (contenedorBuscador) {
            contenedorBuscador.style.display = idSeccion !== 'secCrear' ? 'flex' : 'none';
        }
        if (inputBuscador) {
            inputBuscador.value = '';
            document.querySelectorAll('.fila-filtrable').forEach(f => f.style.display = '');
        }
    }

    // Filtro universal en tiempo real para la tabla activa
    document.addEventListener('DOMContentLoaded', function () {
        const inputBuscador = document.getElementById('inputBuscador');

        if (inputBuscador) {
            inputBuscador.addEventListener('input', function () {
                const termino = this.value.toLowerCase().trim();
                const filas = document.querySelectorAll('.fila-filtrable');

                filas.forEach(fila => {
                    const textoFila = fila.textContent.toLowerCase();
                    fila.style.display = textoFila.includes(termino) ? '' : 'none';
                });
            });
        }
    });

    // Abrir modal de gestión de incidencia con datos precargados
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

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalGestionIncidencia'));
        modal.show();
    }

    // Abrir modal de Técnico (Alta si tecnico === null, Edición si viene objeto)
    function abrirModalTecnico(tecnico = null) {
        const form = document.getElementById('formCrudTecnico');
        const titulo = document.getElementById('modalTituloTecnico');
        const grupoPass = document.getElementById('grupoPasswordTecnico');
        const inputPass = document.getElementById('tec_password');

        if (tecnico) {
            titulo.textContent = 'Editar Técnico: ' + (tecnico.nombre_completo || '');
            form.action = `${URL_BASE_TECNICOS}/${tecnico.id}/editar`;
            document.getElementById('tec_nombre_completo').value = tecnico.nombre_completo || tecnico.nombre || '';
            document.getElementById('tec_email').value = tecnico.email || '';
            document.getElementById('tec_telefono').value = tecnico.telefono || '';
            document.getElementById('tec_especialidad_id').value = tecnico.especialidad_id || '';
            document.getElementById('tec_disponible').checked = Boolean(Number(tecnico.disponible ?? 1));

            // Al editar no se cambia contraseña desde aquí
            grupoPass.style.display = 'none';
            inputPass.required = false;
            inputPass.value = '';
        } else {
            titulo.textContent = 'Nuevo Técnico';
            form.action = `${URL_BASE_TECNICOS}/crear`;
            form.reset();
            document.getElementById('tec_disponible').checked = true;
            grupoPass.style.display = 'block';
            inputPass.required = true;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCrudTecnico'));
        modal.show();
    }

    // Abrir modal de Especialidad / Servicio (Alta si servicio === null, Edición si viene objeto)
    function abrirModalServicio(servicio = null) {
        const form = document.getElementById('formCrudServicio');
        const titulo = document.getElementById('modalTituloServicio');

        if (servicio) {
            titulo.textContent = 'Editar Especialidad: ' + (servicio.nombre_especialidad || '');
            form.action = `${URL_BASE_SERVICIOS}/${servicio.id}/editar`;
            document.getElementById('srv_nombre').value = servicio.nombre_especialidad || '';
            document.getElementById('srv_precio_base').value = servicio.precio_base ?? '';
            document.getElementById('srv_salario_base').value = servicio.salario_base ?? '';
            document.getElementById('srv_descripcion').value = servicio.descripcion || '';
            document.getElementById('srv_responsabilidades').value = servicio.responsabilidades || '';
        } else {
            titulo.textContent = 'Nueva Especialidad';
            form.action = `${URL_BASE_SERVICIOS}/crear`;
            form.reset();
        }

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCrudServicio'));
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
</script>