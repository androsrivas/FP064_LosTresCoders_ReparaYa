<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Incidencia;

final class CalendarioController extends Controller
{
    public function __construct()
    {
        //Restricción a nivel de servidor: Solo rol 'admin'
        Auth::requireRole('admin');
    }

    /**
     * Muestra la vista principal del calendario (Mensual, Semanal, Diario)
     */
    public function index(): void
    {
        $vista = isset($_GET['vista']) && in_array($_GET['vista'], ['mensual', 'semanal', 'diario'], true) 
            ? $_GET['vista'] 
            : 'mensual';

        //Obtenemos todas las incidencias de la base de datos
        $incidencias = Incidencia::obtenerPorEstado('Todas');

        // Formateamos los eventos respetando los campos reales de la BBDD
        $eventos = array_map(function (array $incidencia) {
            $esUrgente = strtolower($incidencia['tipo_urgencia'] ?? '') === 'urgente';
            
            return [
                'id'            => (int) $incidencia['id'],
                'localizador'   => $incidencia['localizador'] ?? '',
                'title'         => '[' . $incidencia['estado'] . '] ' . ($incidencia['nombre_especialidad'] ?? 'Incidencia'),
                'start'         => $incidencia['fecha_servicio'],
                'estado'        => $incidencia['estado'],
                'tipo_urgencia' => $incidencia['tipo_urgencia'] ?? 'Estándar',
                'direccion'     => $incidencia['direccion'] ?? '',
                'descripcion'   => $incidencia['descripcion'] ?? '',
                'clase_badge'   => $esUrgente ? 'bg-danger text-white' : 'badge-yellow',
                'url_detalle'   => '/incidencia/detalle/' . $incidencia['id']
            ];
        }, $incidencias);

        $this->view('admin/calendario', [
            'titulo'  => 'Calendario de Avisos',
            'vista'   => $vista,
            'eventos' => $eventos
        ]);
    }

    /**
     * Endpoint API JSON consumido por FullCalendar
     */
    public function apiEventos(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $incidencias = Incidencia::obtenerPorEstado('Todas');

        $eventos = array_map(function (array $incidencia) {
            $esUrgente = strtolower($incidencia['tipo_urgencia'] ?? '') === 'urgente';
            
            return [
                'id'              => (int) $incidencia['id'],
                'title'           => '[' . $incidencia['estado'] . '] ' . ($incidencia['localizador'] ?? '') . ' - ' . ($incidencia['nombre_especialidad'] ?? 'Aviso'),
                'start'           => $incidencia['fecha_servicio'],
                'backgroundColor' => $esUrgente ? '#dc3545' : '#ffb703',
                'borderColor'     => $esUrgente ? '#b02a37' : '#e0a100',
                'textColor'       => $esUrgente ? '#ffffff' : '#000000',
                'url'             => '/incidencia/detalle/' . $incidencia['id'],
                'extendedProps'   => [
                    'estado'        => $incidencia['estado'],
                    'tipo_urgencia' => $incidencia['tipo_urgencia'] ?? 'Estándar',
                    'direccion'     => $incidencia['direccion'] ?? '',
                    'descripcion'   => $incidencia['descripcion'] ?? ''
                ]
            ];
        }, $incidencias);

        echo json_encode($eventos);
        exit;
    }
}