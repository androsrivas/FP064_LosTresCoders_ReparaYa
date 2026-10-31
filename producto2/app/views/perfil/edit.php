<?php
/**
 * @var array<string, string[]> $errors Errores por campo
 * @var array<string, string>   $old    Valores previos del formulario
 */
?>

<h1>Mi perfil</h1>

<form method="post" action="<?= e(url('/perfil')) ?>">
    <?= crsf_field() ?>

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

    <fieldset>
        <legend>Cambiar contraseña (déjalo en blanco para mantener la actual)</legend>

        <div>
            <label for="password_actual">Contraseña actual</label>
            <input type="password" id="password_actual" name="password_actual" autocomplete="current-password">
            <?= field_errors($errors, 'password_actual') ?>
        </div>

        <div>
            <label for="password">Nueva constraseña (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password">
            <?= field_errors($errors, 'password') ?>
        </div>
        
        <div>
            <label for="password_confirm">Repite la nueva constraseña</label>
            <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password">
            <?= field_errors($errors, 'password_confirm') ?>
        </div>
    </fieldset>

    <button type="submit">Guardar cambios</button>
</form>