<<<<<<< HEAD
<<<<<<< HEAD
<nav>
    <a href="<?= url('/') ?>">Inicio</a>
</nav>
=======
=======
>>>>>>> origin/main
<?php require APP_PATH . '/views/partials/nav.php'?>

<main>
    <?php foreach (\App\Core\Session::pullFlash() as $flash): ?>
        <div class="flash flash.<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
<<<<<<< HEAD
</main>
>>>>>>> 010b90a606fac36e09090f711f2161148af91a4e
=======
</main>
>>>>>>> origin/main
