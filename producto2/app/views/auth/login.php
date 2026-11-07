
<?php
/**
 * @var array<string, string[]> $errors Errores por campo
 * @var array<string, string>   $old    Valores previos del formulario
 */
?>

<h1>Iniciar sesión</h1>

<?= field_errors($errors, 'general') ?>

<form method="post" action="<?= e(url('/login')) ?>">
    <?= csrf_field() ?>

    <div>
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="<?=  e($old['email'] ?? '') ?>" required autocomplete="email">
        <?= field_errors($errors, 'email') ?>
    </div>

    <div>
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <?= field_errors($errors, 'password') ?>
    </div>

    <button type="submit">Entrar</button>
</form>

<p>¿No tienes cuenta? <a href="<?= e(url('/registro')) ?>">Regístrate</a></p>