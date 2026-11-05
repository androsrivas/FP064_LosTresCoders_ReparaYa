<?php
declare(strict_types=1);

$errors = $errors ?? \App\Core\Session::get('errors', []);
$old = $old ?? \App\Core\Session::get('old', []);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReparaYa - Gestión de Incidencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('/assets/css/estilos.css'); ?>" rel="stylesheet">
</head>
<body class="bg-dark text-light m-0 p-0">

    <!-- Modal de Login -->
    <?php if (!\App\Core\Auth::check()): ?>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="loginModalLabel">Iniciar Sesión en <span class="text-yellow">ReparaYa</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= e(url('/login')); ?>" method="POST">
                    <?= csrf_field(); ?>
    
                    <div class="modal-body">
                        <!-- Error general (credenciales incorrectas, etc.) -->
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger py-2 small fw-bold mb-3" role="alert">
                                <?= e(is_array($errors['general']) ? implode(' ', $errors['general']) : $errors['general']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="email" class="form-label text-muted small">Correo Electrónico</label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-control" 
                                value="<?= e($old['email'] ?? ''); ?>" 
                                autocomplete="email" 
                                required 
                                placeholder="ejemplo@correo.com"
                            >   
                            <?= field_errors($errors, 'email'); ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label text-muted small">Contraseña</label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control" 
                                autocomplete="current-password" 
                                required 
                                placeholder="******"
                            >
                            <?= field_errors($errors, 'password'); ?>
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
    <?php if (!empty($errors)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('loginModal');
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
    <?php endif; ?>
    <script>
        sessionStorage.clear();
    </script>
    <?php endif; ?>