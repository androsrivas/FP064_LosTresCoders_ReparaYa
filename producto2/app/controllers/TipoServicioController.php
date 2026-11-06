<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Core\Validator;
use App\Services\TipoServicioService;

/** CRUD de tipos de servicio (especialidades). Solo administradores. */
final class TipoServicioController extends Controller
{
    private const MAX_NOMBRE = 50;

    private TipoServicioService $servicio;

    public function __construct()
    {
        Auth::requireRole(Roles::ADMIN);
        $this->servicio = new TipoServicioService();
    }

    /** GET /admin/servicios */
    public function index(): void
    {
        $this->view('admin/servicios/index', [
            'titulo' => 'Tipos de servicio',
            'servicios' => $this->servicio->listar(),
        ]);
    }

    /** GET /admin/servicios/nuevo */
    public function create(): void
    {
        $this->renderForm(null);
    }

    /** POST /admin/servicios/nuevo */
    public function store(): void
    {
        $this->requireCsrf('/admin/servicios/nuevo');

        $data = $this->datosFormulario();
        $v = $this->validar($data);

        if (!$v->fails()) {
            $r = $this->servicio->crear(...$this->paraGuardar($data));

            if ($r['exito']) {
                $this->flash('success', $r['mensaje']);
                $this->redirect('/admin/servicios');
            }

            $v->add('general', $r['mensaje']);
        }

        $this->renderForm(null, $v->errors(), $data);
    }

    /** GET /admin/servicios/{id}/editar */
    public function edit(string $id): void
    {
        $servicio = $this->servicio->obtener((int) $id);

        if ($servicio === null) {
            $this->flash('error', 'Tipo de servicio no encontrado.');
            $this->redirect('/admin/servicios');
        }

        $this->renderForm($servicio);
    }

    /** POST /admin/servicios/{id}/editar */
    public function update(string $id): void
    {
        $id = (int) $id;
        $this->requireCsrf("/admin/servicios/$id/editar");

        $servicio = $this->servicio->obtener($id);

        if ($servicio === null) {
            $this->flash('error', 'Tipo de servicio no encontrado.');
            $this->redirect('/admin/servicios');
        }

        $data = $this->datosFormulario();
        $v = $this->validar($data);

        if (!$v->fails()) {
            $r = $this->servicio->modificar($id, ...$this->paraGuardar($data));

            if ($r['exito']) {
                $this->flash('success', $r['mensaje']);
                $this->redirect('/admin/servicios');
            }

            $v->add('general', $r['mensaje']);
        }

        $this->renderForm($servicio, $v->errors(), $data);
    }

    /** POST /admin/servicios/{id}/eliminar */
    public function destroy(string $id): void
    {
        $this->requireCsrf('/admin/servicios');

        $r = $this->servicio->eliminar((int) $id);

        $this->flash($r['exito'] ? 'success' : 'error', $r['mensaje']);
        $this->redirect('/admin/servicios');
    }

    /** Entrada del formulario tal como la escribió el usuario (para repintarla si hay errores). */
    private function datosFormulario(): array
    {
        return [
            'nombre_especialidad' => trim($this->input('nombre_especialidad')),
            'descripcion' => trim($this->input('descripcion')),
            'responsabilidades' => trim($this->input('responsabilidades')),
            'salario_base' => trim($this->input('salario_base')),
            'precio_base' => trim($this->input('precio_base')),
        ];
    }

    private function validar(array $data): Validator
    {
        return (new Validator($data))
            ->required('nombre_especialidad', 'nombre')
            ->max('nombre_especialidad', self::MAX_NOMBRE, 'nombre')
            ->required('precio_base', 'precio base')
            ->decimal('precio_base', 'precio base')
            ->decimal('salario_base', 'salario base');
    }

    /**
     * Convierte la entrada validada al formato de la BD: opcionales vacíos a null
     * y coma decimal a punto (MySQL no acepta "19,99" en un DECIMAL).
     * Claves con el nombre de los parámetros del servicio, para pasarlo con "...".
     */
    private function paraGuardar(array $data): array
    {
        $opcional = static fn(string $v): ?string => $v === '' ? null : $v;
        $importe = static fn(string $v): string => str_replace(',', '.', $v);

        return [
            'nombre' => $data['nombre_especialidad'],
            'descripcion' => $opcional($data['descripcion']),
            'responsabilidades' => $opcional($data['responsabilidades']),
            'salarioBase' => $data['salario_base'] === '' ? null : $importe($data['salario_base']),
            'precioBase' => $importe($data['precio_base']),
        ];
    }

    /** @param array|null $servicio null = alta; la fila actual = edición. */
    private function renderForm(?array $servicio, array $errors = [], array $old = []): void
    {
        $this->form(
            'admin/servicios/form',
            $servicio === null ? 'Nuevo tipo de servicio' : 'Editar tipo de servicio',
            $errors,
            $old,
            ['servicio' => $servicio]
        );
    }
}
