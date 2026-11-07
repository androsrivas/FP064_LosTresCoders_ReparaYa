<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Core\Validator;
use App\Services\TecnicoService;

/** CRUD de técnicos. Solo administradores. */
final class TecnicoController extends Controller
{
    private const MAX_NOMBRE = 100;
    private const MAX_EMAIL = 100;
    private const MIN_PASSWORD = 8;
    private const MAX_PASSWORD = 72;

    private TecnicoService $servicio;

    public function __construct()
    {
        Auth::requireRole(Roles::ADMIN);
        $this->servicio = new TecnicoService();
    }

    /** GET /admin/tecnicos */
    public function index(): void
    {
        $this->view('admin/tecnicos/index', [
            'titulo' => 'Técnicos',
            'tecnicos' => $this->servicio->listar(),
        ]);
    }

    /** GET /admin/tecnicos/nuevo */
    public function create(): void
    {
        $this->renderForm(null);
    }

    /** POST /admin/tecnicos/nuevo */
    public function store(): void
    {
        $this->requireCsrf('/admin/tecnicos/nuevo');

        $data = $this->datosFormulario();
        $password = $this->input('password');

        $v = $this->validar($data + [
            'password' => $password,
            'password_confirm' => $this->input('password_confirm'),
        ])
            ->required('password', 'contraseña')
            ->min('password', self::MIN_PASSWORD, 'contraseña')
            ->max('password', self::MAX_PASSWORD, 'contraseña')
            ->matches('password_confirm', 'password', 'Las contraseñas no coinciden.');

        if (!$v->fails()) {
            $r = $this->servicio->crear(...$this->paraGuardar($data) + ['password' => $password]);

            if ($r['exito']) {
                $this->flash('success', $r['mensaje']);
                $this->redirect('/admin/tecnicos');
            }

            $v->add('general', $r['mensaje']);
        }

        $this->renderForm(null, $v->errors(), $data);
    }

    /** GET /admin/tecnicos/{id}/editar */
    public function edit(string $id): void
    {
        $this->renderForm($this->buscarOVolver((int) $id));
    }

    /** POST /admin/tecnicos/{id}/editar */
    public function update(string $id): void
    {
        $id = (int) $id;
        $this->requireCsrf("/admin/tecnicos/$id/editar");

        $tecnico = $this->buscarOVolver($id);
        $data = $this->datosFormulario();
        $v = $this->validar($data);

        if (!$v->fails()) {
            $r = $this->servicio->modificar($id, ...$this->paraGuardar($data));

            if ($r['exito']) {
                $this->flash('success', $r['mensaje']);
                $this->redirect('/admin/tecnicos');
            }

            $v->add('general', $r['mensaje']);
        }

        $this->renderForm($tecnico, $v->errors(), $data);
    }

    /** POST /admin/tecnicos/{id}/eliminar */
    public function destroy(string $id): void
    {
        $this->requireCsrf('/admin/tecnicos');

        $r = $this->servicio->eliminar((int) $id);

        $this->flash($r['exito'] ? 'success' : 'error', $r['mensaje']);
        $this->redirect('/admin/tecnicos');
    }

    /** Entrada del formulario tal como la escribió el usuario (para repintarla si hay errores). */
    private function datosFormulario(): array
    {
        return [
            'nombre' => trim($this->input('nombre')),
            'email' => trim($this->input('email')),
            'telefono' => trim($this->input('telefono')),
            'especialidad_id' => trim($this->input('especialidad_id')),
            // Un checkbox desmarcado no se envía: ausente = no disponible
            'disponible' => $this->input('disponible') === '1' ? '1' : '0',
        ];
    }

    private function validar(array $data): Validator
    {
        return (new Validator($data))
            ->required('nombre', 'nombre')
            ->max('nombre', self::MAX_NOMBRE, 'nombre')
            ->required('email', 'correo')
            ->email('email')
            ->max('email', self::MAX_EMAIL, 'correo')
            ->telefono('telefono');
    }

    /**
     * Convierte la entrada validada a los tipos del servicio.
     * Claves con el nombre de sus parámetros, para pasarlo con "...".
     * Una especialidad no numérica queda en 0 y el servicio la rechaza por inexistente.
     */
    private function paraGuardar(array $data): array
    {
        return [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'especialidadId' => $data['especialidad_id'] === '' ? null : (int) $data['especialidad_id'],
            'telefono' => $data['telefono'] === '' ? null : $data['telefono'],
            'disponible' => $data['disponible'] === '1',
        ];
    }

    private function buscarOVolver(int $id): array
    {
        $tecnico = $this->servicio->obtener($id);

        if ($tecnico === null) {
            $this->flash('error', 'Técnico no encontrado.');
            $this->redirect('/admin/tecnicos');
        }

        return $tecnico;
    }

    /** @param array|null $tecnico null = alta; la fila actual = edición. */
    private function renderForm(?array $tecnico, array $errors = [], array $old = []): void
    {
        $this->form(
            'admin/tecnicos/form',
            $tecnico === null ? 'Nuevo técnico' : 'Editar técnico',
            $errors,
            $old,
            [
                'tecnico' => $tecnico,
                'especialidades' => $this->servicio->especialidades(),
            ]
        );
    }
}