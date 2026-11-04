<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReparaYa - Gestión de Incidencias</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link href="<?= url('/assets/css/estilos.css'); ?>" rel="stylesheet">
</head>
<body>

    <!-- Header / Navbar -->
    <header class="sticky-top bg-dark border-bottom border-secondary border-opacity-25" style="z-index: 1030;">
        <?php require_once __DIR__ . '/menu.php'; ?>
    </header>

    <!-- Modal de Login -->
    <?php if (!\App\Core\Session::has('usuario_id')): ?>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="loginModalLabel">Iniciar Sesión en <span class="text-yellow">ReparaYa</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= url('/login'); ?>" method="POST">
                    <?= csrf_field(); ?>
                    <div class="modal-body">
                        <?php if (\App\Core\Session::has('error_login')): ?>
                            <div class="alert alert-danger py-2">
                                <?= e(\App\Core\Session::get('error_login')); ?>
                            </div>
                            <?php \App\Core\Session::forget('error_login'); ?>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="email" class="form-label text-muted small">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="ejemplo@correo.com">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-muted small">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="******">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-yellow btn-sm">Acceder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- JS Session Storage Sync -->
    <?php if (\App\Core\Session::has('usuario_id')): ?>
    <script>
        sessionStorage.setItem('usuario_id', '<?= e((string) \App\Core\Session::get('usuario_id')) ?>');
        sessionStorage.setItem('usuario_rol', '<?= e((string) \App\Core\Session::get('usuario_rol')) ?>');
        sessionStorage.setItem('usuario_nombre', '<?= e((string) \App\Core\Session::get('usuario_nombre')) ?>');
    </script>
    <?php else: ?>
    <script>
        sessionStorage.clear();
    </script>
    <?php endif; ?>

    <main class="container my-5">