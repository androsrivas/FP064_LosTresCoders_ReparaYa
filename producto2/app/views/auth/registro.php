<?php
/**
 * @var array<string, string[]> $errors Errores por campo
 * @var array<string, string>   $old    Valores previos del formulario
 */
?>

<h1>Crear cuenta</h1>

<form method="post" action="<?= e(url('/registro')) ?>">
    <?= csrf_field() ?>

    <div>
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="<?= e($old['nombre'] ?? '') ?>" required maxlength="100" autocomplete="name">
        <?= field_errors($errors, 'nombre') ?>
    </div>

    <div>
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required maxlength="100" autocomplete="email">
        <?= field_errors($errors, 'email') ?>
    </div>
    
    <div>
        <label for="password">Contraseña (mínimo 9 caracteres)</label>
        <input type="password" id="pasword" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
        <?= field_errors($errors, 'password') ?>
    </div>
    
    <div>
        <label for="password_confirm">Repite la contraseña</label>
        <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
        <?= field_errors($errors, 'password_confirm') ?>
    </div>

    <button type="submit">Crear cuenta</button>
</form>

<p>¿Ya tienes cuenta? <a href="<?= e(url('/login')) ?>">Inicia sesión</a></p>