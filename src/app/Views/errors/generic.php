<?php /** @var int $status @var string $title */ ?>
<div class="form-card error-page" style="text-align:center">
    <h1><?= (int) ($status ?? 500) ?></h1>
    <p><?= e($title ?? 'Une erreur est survenue.') ?></p>
    <a class="btn btn-primary" href="/">Retour à l'accueil</a>
</div>
