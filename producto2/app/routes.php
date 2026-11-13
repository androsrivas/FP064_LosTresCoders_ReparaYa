<?php
/** @var App\Core\Router $router */

// =============================================================================
// PÚBLICAS Y AUTENTICACIÓN
// =============================================================================
$router->get('/', 'HomeController@index');

$router->get('/login',     'AuthController@showLogin');
$router->post('/login',    'AuthController@login');
$router->get('/registro',  'AuthController@showRegister');
$router->post('/registro', 'AuthController@register');
$router->post('/logout',   'AuthController@logout');

$router->get('/perfil',    'PerfilController@edit');
$router->post('/perfil',   'PerfilController@update');

// =============================================================================
// PARTICULAR / CLIENTE  -> Auth::homeFor(particular) = /cliente/avisos
// =============================================================================
$router->get('/cliente/avisos',                 'IncidenciaController@index');
$router->get('/cliente/nueva',                  'IncidenciaController@crear');
$router->post('/cliente/nueva',                 'IncidenciaController@crear');
$router->get('/cliente/avisos/{id}',            'IncidenciaController@show');
$router->post('/cliente/avisos/{id}/cancelar',  'IncidenciaController@cancelarCliente');

// =============================================================================
// COMPARTIDAS (Detalle Modal y Agenda)
// =============================================================================
$router->get('/incidencia/detalle/{id}', 'IncidenciaController@verDetalle');
$router->post('/incidencia/detalle/{id}/mensaje', 'IncidenciaController@enviarMensaje');
$router->get('/agenda',                  'AgendaController@index');
$router->get('/agenda/detalle/{id}',     'AgendaController@show');

// =============================================================================
// TÉCNICO  -> Auth::homeFor(tecnico) = /tecnico/agenda
// =============================================================================
$router->get('/tecnico/agenda',            'AgendaController@index');
$router->get('/tecnico/incidencias',       'AgendaController@incidencias');
$router->post('/tecnico/incidencia/estado', 'AgendaController@cambiarEstado');

// =============================================================================
// ADMINISTRACIÓN Y GESTIÓN  -> Auth::homeFor(admin) = /admin
// =============================================================================
$router->get('/admin',             'AdminController@index');
$router->get('/admin/incidencias', 'AdminController@incidencias');
$router->get('/gestion',           'AdminController@incidencias');

// Incidencias (Admin)
$router->post('/admin/crear',      'AdminController@store');
$router->post('/admin/asignar',    'AdminController@asignar');
$router->post('/admin/actualizar', 'AdminController@actualizar');
$router->post('/admin/modificar',  'AdminController@actualizar');
$router->post('/admin/cancelar',   'AdminController@cancelar');

// CRUD Técnicos (integrado en /gestion)
$router->post('/gestion/tecnicos/crear',          'AdminController@storeTecnico');
$router->post('/gestion/tecnicos/{id}/editar',    'AdminController@updateTecnico');
$router->post('/gestion/tecnicos/{id}/eliminar',  'AdminController@destroyTecnico');

// CRUD Especialidades / Tipos de Servicio (integrado en /gestion)
$router->post('/gestion/servicios/crear',         'AdminController@storeServicio');
$router->post('/gestion/servicios/{id}/editar',   'AdminController@updateServicio');
$router->post('/gestion/servicios/{id}/eliminar', 'AdminController@destroyServicio');