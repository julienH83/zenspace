<?php
/**
 * Carte de prestation (accueil + catalogue).
 * Attend $s : ligne `service` (title, slug, image, category_label, duration_min,
 * price, rating_avg, rating_count).
 *
 * Le média et le titre pointent vers la fiche ; un bouton « Réserver » explicite
 * sert d'appel à l'action (look site de services, pas carte-lien éditoriale).
 *
 * @var array $s
 */
$slug = '/prestation/' . e($s['slug']);
?>
<article class="card service-card">
    <?php if (!empty($s['image'])): ?>
        <a class="card-media" href="<?= $slug ?>" tabindex="-1" aria-hidden="true"><?= picture($s['image'], '') ?></a>
    <?php endif; ?>
    <div class="card-body">
        <span class="tag"><?= e($s['category_label']) ?></span>
        <h3><a href="<?= $slug ?>"><?= e($s['title']) ?></a></h3>
        <?= stars($s['rating_avg'] ?? 0, $s['rating_count'] ?? 0) ?>
        <p class="meta"><?= (int) $s['duration_min'] ?> min</p>
        <div class="card-foot">
            <span class="price"><?= price($s['price']) ?></span>
            <a class="btn btn-primary btn-sm" href="<?= $slug ?>">Réserver</a>
        </div>
    </div>
</article>
