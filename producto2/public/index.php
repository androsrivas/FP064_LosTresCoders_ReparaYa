<?php
// producto2/public/index.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/IncidenciaController.php';

// Conexión PDO expuesta globalmente por config.php
global $pdo; 

//Cargar los controladores
$incidenciaController = new IncidenciaController($pdo);


// Si no viene action, redirigimos por defecto a 'home'
$action = $_GET['action'] ?? 'home';

switch ($action) {

    case 'home':
        require_once __DIR__ . '/../app/views/home.php';
        break;

    case 'mi_perfil':
        require_once __DIR__ . '/../app/views//mi_perfil.php';
        break;

    case 'mis_servicios':
        require_once __DIR__ . '/../app/views/mis_servicios.php';
        break;

    case 'detalle_servicio':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $datosVista = $incidenciaController->verDetalle($id);
        require_once __DIR__ . '/../app/views/detalle_servicio.php';
        break;

    case 'agenda':
        require_once __DIR__ . '/../app/views/agenda.php';
        break;

    case 'gestion':
        require_once __DIR__ . '/../app/views/gestion.php';
        break;



    // --- ACCIONES POST (Formularios) ---
    case 'crear_incidencia':
        $incidenciaController->crear();
        break;

    case 'cambiar_estado_tecnico':
        $incidenciaController->cambiarEstadoTecnico();
        break;

    case 'cancelar_cliente':
        $incidenciaController->cancelarCliente();
        break;

    case 'modificar_admin':
        $incidenciaController->modificarAdmin();
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada.";
        break;
}