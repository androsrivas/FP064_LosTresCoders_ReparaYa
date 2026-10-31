<?php
/** @var App\Core\Router $router */

// Públicas
$router->get('/', 'HomeController@index');

// !!! Descomentar cada bloque cuando exista el controlador/acción

// Auth (CRUD A)
$router->get('/login',     'AuthController@showLogin');
$router->post('/login',    'AuthController@login');
$router->get('/registro',  'AuthController@showRegister');
$router->post('/registro', 'AuthController@register');
$router->post('/logout',   'AuthController@logout');
$router->get('/perfil',    'PerfilController@edit');
$router->post('/perfil',   'PerfilController@update');

// Particular (CRUD B)  -> Auth::homeFor(particular) = /cliente/avisos
// $router->get('/cliente/avisos',          'IncidenciaController@index');
// $router->get('/cliente/nueva',           'IncidenciaController@create');
// $router->post('/cliente/nueva',          'IncidenciaController@store');
// $router->get('/cliente/avisos/{id}',     'IncidenciaController@show');
// $router->post('/cliente/avisos/{id}/cancelar', 'IncidenciaController@cancel');

// Admin (CRUD C y D)  -> Auth::homeFor(admin) = /admin
// $router->get('/admin',              'AdminController@index');
// $router->get('/admin/incidencias',  'AdminController@incidencias');
// $router->get('/admin/calendario',   'CalendarioController@index');
// $router->get('/admin/tecnicos',     'TecnicoController@index');
// $router->get('/admin/servicios',    'TipoServicioController@index');

// Técnico (CRUD C)  -> Auth::homeFor(tecnico) = /tecnico/agenda
// $router->get('/tecnico/agenda',      'AgendaController@index');
// $router->get('/tecnico/incidencias', 'AgendaController@incidencias');