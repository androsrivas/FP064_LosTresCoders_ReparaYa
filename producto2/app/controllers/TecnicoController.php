<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Roles;
use App\Core\Session;
use App\Services\TecnicoService;

class TecnicoController extends Controller
{
    private TecnicoService $service;

    public function __construct()
    {
        // Seguridad: Solo los administradores pueden gestionar técnicos
        Auth::requireRole(Roles::ADMIN);
        $this->service = new TecnicoService();
    }

    /**
     * Muestra el listado completo de técnicos.
     * Ruta: GET /admin/tecnicos
     */
    public function index(): void
    {
        $tecnicos = $this->service->listar();

        $this->view('admin/tecnicos/index', [
            'titulo'   => 'Gestión de Técnicos',
            'tecnicos' => $tecnicos
        ]);
    }

    /**
     * Muestra el formulario para registrar un nuevo técnico.
     * Ruta: GET /admin/tecnicos/nuevo
     */
    public function create(): void
    {
        $especialidades = $this->service->especialidades();

        $this->view('admin/tecnicos/form', [
            'titulo'         => 'Alta de Nuevo Técnico',
            'especialidades' => $especialidades,
            'tecnico'        => null
        ]);
    }

    /**
     * Procesa el formulario de alta de un técnico.
     * Ruta: POST /admin/tecnicos/nuevo
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tecnicos');
            return;
        }

        $nombre         = trim($_POST['nombre_completo'] ?? $_POST['nombre'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $password       = $_POST['password'] ?? '';
        $especialidadId = isset($_POST['especialidad_id']) && $_POST['especialidad_id'] !== '' 
                          ? (int) $_POST['especialidad_id'] 
                          : null;
        $telefono       = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
        $disponible     = isset($_POST['disponible']) ? (bool) $_POST['disponible'] : true;

        $resultado = $this->service->crear($nombre, $email, $password, $especialidadId, $telefono, $disponible);

        if ($resultado['exito']) {
            Session::forget('old');
            Session::forget('errors');
            $this->flash('success', $resultado['mensaje']);
            $this->redirect('/admin/tecnicos');
            return;
        }

        Session::set('errors', ['general' => [$resultado['mensaje']]]);
        Session::set('old', $_POST);
        $this->redirect('/admin/tecnicos/nuevo');
    }

    /**
     * Muestra el formulario de edición de un técnico existente.
     * Ruta: GET /admin/tecnicos/{id}/editar
     */
    public function edit(int $id): void
    {
        $tecnico = $this->service->obtener($id);

        if (!$tecnico) {
            $this->flash('danger', 'Técnico no encontrado.');
            $this->redirect('/admin/tecnicos');
            return;
        }

        $especialidades = $this->service->especialidades();

        $this->view('admin/tecnicos/form', [
            'titulo'         => 'Editar Técnico',
            'tecnico'        => $tecnico,
            'especialidades' => $especialidades
        ]);
    }

    /**
     * Guarda los cambios aplicados a un técnico.
     * Ruta: POST /admin/tecnicos/{id}/editar
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tecnicos');
            return;
        }

        $nombre         = trim($_POST['nombre_completo'] ?? $_POST['nombre'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $especialidadId = isset($_POST['especialidad_id']) && $_POST['especialidad_id'] !== '' 
                          ? (int) $_POST['especialidad_id'] 
                          : null;
        $telefono       = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
        $disponible     = isset($_POST['disponible']) ? (bool) $_POST['disponible'] : false;

        $resultado = $this->service->modificar($id, $nombre, $email, $especialidadId, $telefono, $disponible);

        if ($resultado['exito']) {
            $this->flash('success', $resultado['mensaje']);
            $this->redirect('/admin/tecnicos');
            return;
        }

        $this->flash('danger', $resultado['mensaje']);
        $this->redirect("/admin/tecnicos/{$id}/editar");
    }

    /**
     * Elimina un técnico del sistema o gestiona el error si tiene incidencias asignadas.
     * Ruta: POST /admin/tecnicos/{id}/eliminar
     */
    public function destroy(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tecnicos');
            return;
        }

        $resultado = $this->service->eliminar($id);

        if ($resultado['exito']) {
            $this->flash('success', $resultado['mensaje']);
        } else {
            $this->flash('danger', $resultado['mensaje']);
        }

        $this->redirect('/admin/tecnicos');
    }
}