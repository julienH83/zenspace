<?php
/**
 * Carte de prestation (accueil + catalogue) — version « premium ».
 * Attend $s : ligne `service` (title, slug, image, category_label, duration_min,
 * price, rating_avg, rating_count).
 *
 * Média + titre pointent vers la fiche ; le prix est un badge posé sur la photo
 * et un bouton « Réserver » sert d'appel à l'action explicite.
 *
 * @var array $s
 */
// Chargement de icon() si la vue appelante ne l'a pas déjà inclus (catalogue,
// API JSON de filtrage). include_once → aucun coût si déjà chargé.
include_once __DIR__ . '/icons.php';
$slug = '/prestation/' . e($s['slug']);
?>
<article class="card service-card">
    <div class="card-media-wrap">
        <?php if (!empty($s['image'])): ?>
            <a class="card-media" href="<?= $slug ?>" tabindex="-1" aria-hidden="true"><?= picture($s['image'], '') ?></a>
        <?php endif; ?>
        <span class="price-badge"><?= price($s['price']) ?></span>
    </div>
    <div class="card-body">
        <span class="tag-chip"><?= e($s['category_label']) ?></span>
        <h3><a href="<?= $slug ?>"><?= e($s['title']) ?></a></h3>
        <?= stars($s['rating_avg'] ?? 0, $s['rating_count'] ?? 0) ?>
        <p class="meta meta-duration"><?= icon('clock', 'icon-inline') ?><span><?= (int) $s['duration_min'] ?>&nbsp;min</span></p>
        <a class="btn btn-primary btn-sm card-cta" href="<?= $slug ?>">Réserver ce soin</a>
    </div>
</article>
