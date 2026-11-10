<?php
/** @var App\Core\Router $router */

// Públicas
$router->get('/', 'HomeController@index');

// !!! Descomentar cada bloque cuando exista el controlador/acción

// Auth (CRUD A)
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/registro', 'AuthController@showRegister');
$router->post('/registro', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');
$router->get('/perfil', 'PerfilController@edit');
$router->post('/perfil', 'PerfilController@update');

// Particular (CRUD B)  -> Auth::homeFor(particular) = /cliente/avisos
$router->get('/cliente/avisos',          'IncidenciaController@index');
$router->get('/cliente/nueva',           'IncidenciaController@crear');
$router->post('/cliente/nueva',          'IncidenciaController@crear');
$router->get('/cliente/avisos/{id}',     'IncidenciaController@show');
$router->get('/incidencia/detalle/{id}', 'IncidenciaController@verDetalle');
$router->post('/cliente/avisos/{id}/cancelar',  'IncidenciaController@cancelarCliente');

// Admin (CRUD C y D)  -> Auth::homeFor(admin) = /admin
 $router->get('/admin',              'AdminController@index');
 $router->get('/admin/incidencias',  'AdminController@incidencias');
 $router->get('/admin/calendario',   'CalendarioController@index');
// $router->get('/admin/servicios',    'TipoServicioController@index');
$router->get('/agenda', 'AgendaController@index');
$router->get('/gestion', 'AdminController@index');

// Técnicos (admin)  — vistas pendientes: admin/tecnicos/index y admin/tecnicos/form
$router->get('/admin/tecnicos', 'TecnicoController@index');
$router->get('/admin/tecnicos/nuevo', 'TecnicoController@create');
$router->post('/admin/tecnicos/nuevo', 'TecnicoController@store');
$router->get('/admin/tecnicos/{id}/editar', 'TecnicoController@edit');
$router->post('/admin/tecnicos/{id}/editar', 'TecnicoController@update');
$router->post('/admin/tecnicos/{id}/eliminar', 'TecnicoController@destroy');

// Técnicos (admin)  — vistas pendientes: admin/tecnicos/index y admin/tecnicos/form
$router->get('/admin/tecnicos', 'TecnicoController@index');
$router->get('/admin/tecnicos/nuevo', 'TecnicoController@create');
$router->post('/admin/tecnicos/nuevo', 'TecnicoController@store');
$router->get('/admin/tecnicos/{id}/editar', 'TecnicoController@edit');
$router->post('/admin/tecnicos/{id}/editar', 'TecnicoController@update');
$router->post('/admin/tecnicos/{id}/eliminar', 'TecnicoController@destroy');

// Técnico (CRUD C)  -> Auth::homeFor(tecnico) = /tecnico/agenda
 $router->get('/tecnico/agenda',      'AgendaController@index');
 $router->get('/tecnico/incidencias', 'AgendaController@incidencias');