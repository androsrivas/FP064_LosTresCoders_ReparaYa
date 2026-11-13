<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Cliente;
use App\Models\Incidencia;
use App\Services\IncidenciaService;
use App\Services\TecnicoService;
use App\Services\TipoServicioService;

class AdminController extends Controller
{
    private const MAX_NOMBRE_SERVICIO = 50;

    private IncidenciaService $service;
    private TecnicoService $tecnicoService;
    private TipoServicioService $tipoServicioService;

    public function __construct()
    {
        Auth::requireRole(Roles::ADMIN);
        $this->service = new IncidenciaService();
        $this->tecnicoService = new TecnicoService();
        $this->tipoServicioService = new TipoServicioService();
    }

    /**
     * Alias de entrada para /admin.
     */
    public function index(): void
    {
        $this->incidencias();
    }

    /**
     * Muestra el panel principal de administración con el listado completo de incidencias,
     * técnicos, clientes y tipos de servicio (especialidades).
     */
    public function incidencias(): void
    {
        $incidencias    = Incidencia::obtenerTodas();
        $tecnicos       = $this->tecnicoService->listar();
        $clientes       = Cliente::all();
        $especialidades = $this->tipoServicioService->listar();

        $this->view('admin/gestion', [
            'titulo'         => 'Panel de Gestión',
            'incidencias'    => $incidencias,
            'tecnicos'       => $tecnicos,
            'clientes'       => $clientes,
            'especialidades' => $especialidades
        ]);
    }

    // =========================================================================
    // GESTIÓN DE INCIDENCIAS
    // =========================================================================

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $user = Auth::user();
        $rol  = strtolower((string) ($user['rol'] ?? Session::get('usuario_rol', '')));

        $clienteId = !empty($_POST['cliente_id'])
            ? (int) $_POST['cliente_id']
            : (int) ($user['id'] ?? $user['usuario_id'] ?? Session::get('usuario_id', 0));

        $payload = [
            'cliente_id'      => ($rol === 'admin' && isset($_POST['cliente_id'])) ? (int) $_POST['cliente_id'] : $clienteId,
            'especialidad_id' => (int) ($_POST['especialidad_id'] ?? 0),
            'tipo_urgencia'   => trim($_POST['tipo_urgencia'] ?? 'Estándar'),
            'fecha_servicio'  => trim($_POST['fecha_servicio'] ?? ''),
            'descripcion'     => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion'       => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : ''
        ];

        $resultado = $this->service->crearIncidencia($payload);

        if ($resultado['exito']) {
            Session::forget('old');
            Session::forget('errors');
            $this->flash('success', 'Incidencia registrada con éxito. Localizador: ' . $resultado['localizador']);
            $this->redirect('/gestion');
            return;
        }

        Session::set('errors', ['general' => [$resultado['mensaje']]]);
        Session::set('old', $payload);
        $this->redirect('/gestion');
    }

    public function asignar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $tecnicoId    = (int) ($_POST['tecnico_id'] ?? 0);
        $adminUserId = (int) (
            Auth::id()
            ?? Auth::user()['id']
            ?? Session::get('usuario_id')
            ?? 0
        );

        if ($incidenciaId <= 0 || $tecnicoId <= 0) {
            $this->flash('danger', 'Debe seleccionar una incidencia y un técnico válidos.');
            $this->redirect('/gestion');
            return;
        }

        $resultado = $this->service->asignarTecnicoPorAdmin($incidenciaId, $tecnicoId, $adminUserId);

        if ($resultado['exito']) {
            Session::forget('form_data');
            $this->flash('success', $resultado['mensaje']);
        } else {
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('/gestion');
    }

    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $adminUserId = (int) (
            Auth::id()
            ?? Auth::user()['id']
            ?? Session::get('usuario_id')
            ?? 0
        );

        $payload = [
            'tecnico_id'      => isset($_POST['tecnico_id']) && $_POST['tecnico_id'] !== '' ? (int) $_POST['tecnico_id'] : null,
            'especialidad_id' => isset($_POST['especialidad_id']) ? (int) $_POST['especialidad_id'] : null,
            'descripcion'     => is_string($_POST['descripcion'] ?? null) ? trim($_POST['descripcion']) : '',
            'direccion'       => is_string($_POST['direccion'] ?? null) ? trim($_POST['direccion']) : '',
            'fecha_servicio'  => is_string($_POST['fecha_servicio'] ?? null) ? trim($_POST['fecha_servicio']) : '',
            'tipo_urgencia'   => is_string($_POST['tipo_urgencia'] ?? null) ? trim($_POST['tipo_urgencia']) : '',
            'estado'          => is_string($_POST['estado'] ?? null) ? trim($_POST['estado']) : '',
            'comentario'      => is_string($_POST['comentario'] ?? $_POST['observaciones'] ?? null)
                ? trim($_POST['comentario'] ?? $_POST['observaciones']) : ''
        ];

        $resultado = $this->service->modificarPorAdmin($incidenciaId, array_filter($payload, fn($v) => !is_null($v)), $adminUserId);

        if ($resultado['exito']) {
            Session::forget('form_data');
            $this->flash('success', $resultado['mensaje']);
        } else {
            Session::set('form_data', $payload);
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('/gestion');
    }

    public function cancelar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/gestion');
            return;
        }

        $incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
        $observacion  = is_string($_POST['observacion'] ?? null) ? trim($_POST['observacion']) : 'Cancelada por el Administrador';
        $adminUserId  = Auth::id() ?? (int) Session::get('usuario_id', 0);

        $resultado = $this->service->cancelarPorAdmin($incidenciaId, $observacion, $adminUserId);

        if ($resultado['exito']) {
            $this->flash('success', 'Incidencia cancelada correctamente.');
        } else {
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('/gestion');
    }

    // =========================================================================
    // CRUD DE TÉCNICOS (Desde /gestion)
    // =========================================================================

    public function storeTecnico(): void
    {
        $this->requireCsrf('/gestion');

        $nombre         = trim($this->input('nombre_completo') ?: $this->input('nombre'));
        $email          = trim($this->input('email'));
        $password       = (string) ($_POST['password'] ?? '');
        $especialidadRaw = trim($this->input('especialidad_id'));
        $especialidadId = $especialidadRaw !== '' ? (int) $especialidadRaw : null;
        $telefonoRaw    = trim($this->input('telefono'));
        $telefono       = $telefonoRaw !== '' ? $telefonoRaw : null;
        $disponible     = isset($_POST['disponible']);

        $v = (new Validator([
            'nombre_completo' => $nombre,
            'email'           => $email,
            'password'        => $password,
        ]))
            ->required('nombre_completo', 'nombre completo')
            ->max('nombre_completo', 100, 'nombre completo')
            ->required('email', 'correo electrónico')
            ->email('email', 'correo electrónico')
            ->required('password', 'contraseña')
            ->min('password', 6, 'contraseña');

        if ($v->fails()) {
            $errores = $v->errors();
            $primerCampo = reset($errores);
            $primerError = is_array($primerCampo) ? ($primerCampo[0] ?? 'Datos del técnico no válidos.') : 'Datos del técnico no válidos.';
            $this->flash('danger', $primerError);
            $this->redirect('/gestion');
            return;
        }

        $resultado = $this->tecnicoService->crear(
            $nombre,
            $email,
            $password,
            $especialidadId,
            $telefono,
            $disponible
        );

        $this->flash($resultado['exito'] ? 'success' : 'danger', $resultado['mensaje']);
        $this->redirect('/gestion');
    }

    public function updateTecnico(string $id): void
    {
        $this->requireCsrf('/gestion');

        $tecnicoId       = (int) $id;
        $nombre          = trim($this->input('nombre_completo') ?: $this->input('nombre'));
        $email           = trim($this->input('email'));
        $especialidadRaw = trim($this->input('especialidad_id'));
        $especialidadId  = $especialidadRaw !== '' ? (int) $especialidadRaw : null;
        $telefonoRaw     = trim($this->input('telefono'));
        $telefono        = $telefonoRaw !== '' ? $telefonoRaw : null;
        $disponible      = isset($_POST['disponible']);

        $v = (new Validator([
            'nombre_completo' => $nombre,
            'email'           => $email,
        ]))
            ->required('nombre_completo', 'nombre completo')
            ->max('nombre_completo', 100, 'nombre completo')
            ->required('email', 'correo electrónico')
            ->email('email', 'correo electrónico');

        if ($v->fails()) {
            $errores = $v->errors();
            $primerCampo = reset($errores);
            $primerError = is_array($primerCampo) ? ($primerCampo[0] ?? 'Datos del técnico no válidos.') : 'Datos del técnico no válidos.';
            $this->flash('danger', $primerError);
            $this->redirect('/gestion');
            return;
        }

        $resultado = $this->tecnicoService->modificar(
            $tecnicoId,
            $nombre,
            $email,
            $especialidadId,
            $telefono,
            $disponible
        );

        $this->flash($resultado['exito'] ? 'success' : 'danger', $resultado['mensaje']);
        $this->redirect('/gestion');
    }

    public function destroyTecnico(string $id): void
    {
        $this->requireCsrf('/gestion');

        $resultado = $this->tecnicoService->eliminar((int) $id);

        $this->flash($resultado['exito'] ? 'success' : 'danger', $resultado['mensaje']);
        $this->redirect('/gestion');
    }

    // =========================================================================
    // CRUD DE ESPECIALIDADES / TIPOS DE SERVICIO (Desde /gestion)
    // =========================================================================

    public function storeServicio(): void
    {
        $this->requireCsrf('/gestion');

        $data = $this->datosFormularioServicio();
        $v = $this->validarServicio($data);

        if ($v->fails()) {
            $errores = $v->errors();
            $primerCampo = reset($errores);
            $primerError = is_array($primerCampo) ? ($primerCampo[0] ?? 'Datos de la especialidad no válidos.') : 'Datos de la especialidad no válidos.';
            $this->flash('danger', $primerError);
            $this->redirect('/gestion');
            return;
        }

        $resultado = $this->tipoServicioService->crear(...$this->paraGuardarServicio($data));

        $this->flash($resultado['exito'] ? 'success' : 'danger', $resultado['mensaje']);
        $this->redirect('/gestion');
    }

    public function updateServicio(string $id): void
    {
        $this->requireCsrf('/gestion');

        $data = $this->datosFormularioServicio();
        $v = $this->validarServicio($data);

        if ($v->fails()) {
            $errores = $v->errors();
            $primerCampo = reset($errores);
            $primerError = is_array($primerCampo) ? ($primerCampo[0] ?? 'Datos de la especialidad no válidos.') : 'Datos de la especialidad no válidos.';
            $this->flash('danger', $primerError);
            $this->redirect('/gestion');
            return;
        }

        $resultado = $this->tipoServicioService->modificar((int) $id, ...$this->paraGuardarServicio($data));

        $this->flash($resultado['exito'] ? 'success' : 'danger', $resultado['mensaje']);
        $this->redirect('/gestion');
    }

    public function destroyServicio(string $id): void
    {
        $this->requireCsrf('/gestion');

        $resultado = $this->tipoServicioService->eliminar((int) $id);

        $this->flash($resultado['exito'] ? 'success' : 'danger', $resultado['mensaje']);
        $this->redirect('/gestion');
    }

    // =========================================================================
    // HELPERS PRIVADOS DE VALIDACIÓN PARA SERVICIOS
    // =========================================================================

    private function datosFormularioServicio(): array
    {
        return [
            'nombre_especialidad' => trim($this->input('nombre_especialidad')),
            'descripcion'         => trim($this->input('descripcion')),
            'responsabilidades'   => trim($this->input('responsabilidades')),
            'salario_base'        => trim($this->input('salario_base')),
            'precio_base'         => trim($this->input('precio_base')),
        ];
    }

    private function validarServicio(array $data): Validator
    {
        return (new Validator($data))
            ->required('nombre_especialidad', 'nombre')
            ->max('nombre_especialidad', self::MAX_NOMBRE_SERVICIO, 'nombre')
            ->required('precio_base', 'precio base')
            ->decimal('precio_base', 'precio base')
            ->decimal('salario_base', 'salario base');
    }

    private function paraGuardarServicio(array $data): array
    {
        $opcional = static fn(string $v): ?string => $v === '' ? null : $v;
        $importe  = static fn(string $v): string => str_replace(',', '.', $v);

        return [
            'nombre'            => $data['nombre_especialidad'],
            'descripcion'       => $opcional($data['descripcion']),
            'responsabilidades' => $opcional($data['responsabilidades']),
            'salarioBase'       => $data['salario_base'] === '' ? null : $importe($data['salario_base']),
            'precioBase'        => $importe($data['precio_base']),
        ];
    }
}