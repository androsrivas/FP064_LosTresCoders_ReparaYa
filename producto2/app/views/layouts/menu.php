<?php require APP_PATH . '/views/partials/nav.php'?>

<main>
    <?php foreach (\App\Core\Session::pullFlash() as $flash): ?>
        <div class="flash flash.<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
</main>