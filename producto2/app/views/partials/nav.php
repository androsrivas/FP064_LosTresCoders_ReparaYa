<?php
declare(strict_types=1);

namespace App\Views;

use App\Core\Auth;
use App\Core\Roles;

$menus = [
    Roles::ADMIN => [
        ['Panel', '/admin'],
        ['Incidencias', '/admin/incidencias'],
        ['Calendario', '/admin/calendario'],
        ['Técnicos', '/admin/tecnicos'],
        ['Servicios', '/admin/servicios'],
        ['Mi perfil', '/perfil'],
    ],

    Roles::TECNICO => [
        ['Mi agenda', '/tecnico/agenda'],
        ['Mis incidencias', '/tecnico/incidencias'],
        ['Mi perfil', '/perfil'],
    ],

    Roles::PARTICULAR => [
        ['Mis avisos', '/cliente/avisos'],
        ['Nueva solicitud', '/cliente/nueva'],
        ['Mi perfil', '/perfil'],
    ],

    'guest' => [
        ['Inicio', '/'],
        ['Iniciar sesión', '/login'],
        ['Registrarse', '/registro'],
    ],
];

$items = $menus[Auth::role() ?? 'guest'] ?? $menus['guest'];
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$e = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?>
<nav class="navbar" aria-label="Menú principal">
    <ul>
        <?php foreach ($items as [$label, $path]): ?>
            <li>
                <a href="<?= $e(url($path)) ?>"
                    <?= str_ends_with((string) $current, $path) ? 'aria-current="page" class="active"' : '' ?>>
                    <?= $e($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (Auth::check()): ?>
        <span class="navbar-user"><?= $e((string) Auth::user()['nombre']) ?></span>
        <form method="post" action="<?= $e(url('/logout')) ?>">
            <button type="submit">Cerrar sessión</button>
        </form>
    <?php endif; ?>
</nav>