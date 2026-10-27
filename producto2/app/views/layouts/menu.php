<?php
// producto2/app/views/layouts/menu.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['usuario_id']);
$rol        = $_SESSION['usuario_rol'] ?? null;
$nombreUser = $_SESSION['usuario_nombre'] ?? 'Usuario';
?>

<nav class="navbar navbar-expand-lg border-bottom border-dark-subtle px-3" style="background-color: #181b1f;">
    <div class="container-fluid">
        <!-- Logo / ReparaYa -->
        <a class="navbar-brand fw-bold text-yellow d-flex align-items-center gap-2" href="index.php?action=home">
            <span style="font-size: 1.4rem;">🛠️</span> <span>Repara<span class="text-white">Ya</span></span>
        </a>

        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContenido">
            <!-- Menú de navegación dinámico por rol -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white fw-medium" href="index.php?action=home">Home</a>
                </li>

                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="index.php?action=mi_perfil">Mi perfil</a>
                    </li>

                    <?php if (in_array(strtolower($rol), ['cliente', 'particular'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="index.php?action=mis_servicios">Mis servicios</a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array(strtolower($rol), ['administrador', 'admin', 'tecnico'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="index.php?action=agenda">Agenda</a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array(strtolower($rol), ['administrador', 'admin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="index.php?action=gestion">Gestión</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Botones derecha -->
            <div class="d-flex align-items-center gap-3">
                <?php if ($isLoggedIn): ?>
                    <span class="text-white-50 small">Hola, <strong class="text-white"><?= htmlspecialchars($nombreUser) ?></strong></span>
                    <a href="index.php?action=logout" class="btn btn-outline-yellow btn-sm">Logout</a>
                <?php else: ?>
                    <button type="button" class="btn btn-yellow btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Iniciar Sesión
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>