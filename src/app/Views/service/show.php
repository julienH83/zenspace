<?php
/** @var array $service */
use App\Core\Auth;
?>
<article class="form-card form-wide">
    <?php if (!empty($service['image'])): ?>
        <img class="detail-banner" src="/assets/images/<?= e($service['image']) ?>" alt="<?= e($service['title']) ?>">
    <?php endif; ?>
    <span class="tag"><?= e($service['category_label']) ?></span>
    <h1><?= e($service['title']) ?></h1>
    <p class="muted"><?= (int) $service['duration_min'] ?> minutes — <span class="price"><?= price($service['price']) ?></span></p>

    <h2>Description</h2>
    <p><?= nl2br(e($service['description'])) ?></p>

    <div class="actions">
        <?php if (Auth::check()): ?>
            <a class="btn btn-primary" href="/reserver/<?= (int) $service['id'] ?>">Réserver cette prestation</a>
        <?php else: ?>
            <a class="btn btn-primary" href="/connexion">Se connecter pour réserver</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="/prestations">Retour au catalogue</a>
    </div>
</article>
