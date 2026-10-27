<?php
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
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body>

    <?php include_once __DIR__ . '/menu.php'; ?>

    <!-- Modal de Login -->
    <?php if (!isset($_SESSION['usuario_id'])): ?>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="loginModalLabel">Iniciar Sesión en <span class="text-yellow">ReparaYa</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/producto2/public/index.php?action=login" method="POST">
                    <div class="modal-body">
                        <?php if (isset($_SESSION['error_login'])): ?>
                            <div class="alert alert-danger py-2">
                                <?= htmlspecialchars($_SESSION['error_login']); ?>
                            </div>
                            <?php unset($_SESSION['error_login']); ?>
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
    <?php if (isset($_SESSION['usuario_id'])): ?>
    <script>
        sessionStorage.setItem('usuario_id', '<?= $_SESSION['usuario_id'] ?>');
        sessionStorage.setItem('usuario_rol', '<?= $_SESSION['usuario_rol'] ?>');
        sessionStorage.setItem('usuario_nombre', '<?= htmlspecialchars($_SESSION['usuario_nombre'], ENT_QUOTES) ?>');
    </script>
    <?php else: ?>
    <script>
        sessionStorage.clear();
    </script>
    <?php endif; ?>

    <main class="container my-5">