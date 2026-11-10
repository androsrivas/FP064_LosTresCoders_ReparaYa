<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Roles;

$menus = [
    Roles::ADMIN => [
        ['Home', '/'],
        ['Mi perfil', '/perfil'],
        ['Agenda', '/agenda'],
        ['Gestión', '/gestion'],
    ],

    Roles::TECNICO => [
        ['Home', '/'],
        ['Mi perfil', '/perfil'],
        ['Agenda', '/agenda'],
    ],

    Roles::PARTICULAR => [
        ['Home', '/'],
        ['Mi perfil', '/perfil'],
    ],

    'guest' => [
        ['Home', '/'],
    ],
];

$userRole = Auth::role() ?? 'guest';
$items = $menus[$userRole] ?? $menus['guest'];
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<header class="sticky-top w-100 bg-dark border-bottom border-secondary border-opacity-25" style="z-index: 1030;">
    <nav class="navbar navbar-expand-lg border-bottom border-secondary border-opacity-25 px-4 py-2 w-100" style="background-color: var(--bg-main);">
        <div class="container-fluid">
            <!-- Logo / ReparaYa -->
            <a class="navbar-brand fw-bold text-yellow d-flex align-items-center gap-2" href="<?= e(url('/')); ?>">
                <span style="font-size: 1.4rem;">🛠️</span> <span>Repara<span class="text-white">Ya</span></span>
            </a>

            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido" aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContenido">
                <!-- Menú de navegación dinámico por rol -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php foreach ($items as [$label, $path]): ?>
                        <?php 
                            $urlPath = url($path);
                            $isActive = ($current === $path || str_ends_with((string)$current, $path));
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive ? 'text-white fw-bold active' : 'text-white-50' ?>" 
                            href="<?= e($urlPath); ?>"
                                <?= $isActive ? 'aria-current="page"' : '' ?>>
                                <?= e($label); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Botones y estado de usuario a la derecha -->
                <div class="d-flex align-items-center gap-3">
                    <?php if (Auth::check()): ?>
                        <span class="text-white-50 small">
                            Hola, <strong class="text-white"><?= e((string) (Auth::user()['nombre'] ?? 'Usuario')); ?></strong>
                        </span>
                    
                        <!-- Formulario Logout seguro con CSRF -->
                        <form method="POST" action="<?= e(url('/logout')); ?>" class="m-0">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-yellow btn-sm">Logout</button>
                        </form>
                    <?php else: ?>
                        <!-- Accionador del Modal de Login definido en header.php -->
                        <button type="button" class="btn btn-yellow btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                            Iniciar Sesión
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Mensajes Flash del sistema (Errores / Éxitos globales) -->
<?php $flashes = \App\Core\Session::pullFlash(); ?>
<?php if (!empty($flashes)): ?>
    <div class="container mt-3" style="max-width: 720px;">
        <?php foreach ($flashes as $flash): ?>
            <?php 
                $isError = ($flash['type'] === 'error');
                $borderClass = $isError ? 'border-danger text-danger' : 'border-warning text-warning';
            ?>
            <div class="alert bg-dark <?= $borderClass; ?> border border-1 fade show py-2 px-3 shadow-sm rounded d-flex align-items-center d-flex justify-content-between mb-2" role="alert">
                <div class="d-flex align-items-center gap-2 small fw-semibold">
                    <span><?= $isError ? '⚠️' : 'ℹ️'; ?></span>
                    <span><?= e($flash['message']); ?></span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Inicio del contenedor principal de vistas -->
<main class="container py-4 flex-grow-1">