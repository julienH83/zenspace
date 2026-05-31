<?php
/** @var array $services @var array $categories */
?>
<div class="page-head">
    <h1>Nos prestations</h1>
</div>

<!-- Filtres : traités en JavaScript (voir assets/js/app.js), sans rechargement -->
<form id="filters" class="filters">
    <div class="field" style="margin:0">
        <label for="category">Catégorie</label>
        <select id="category" name="category">
            <option value="">Toutes</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= e($c['slug']) ?>"><?= e($c['label']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field" style="margin:0">
        <label for="max_price">Prix max : <span id="max_price_value">150 €</span></label>
        <input type="range" id="max_price" name="max_price" min="30" max="150" step="10" value="150">
    </div>
    <div class="field" style="margin:0">
        <label for="max_duration">Durée max : <span id="max_duration_value">120 min</span></label>
        <input type="range" id="max_duration" name="max_duration" min="30" max="120" step="15" value="120">
    </div>
    <noscript><button type="submit" class="btn btn-primary">Filtrer</button></noscript>
</form>

<!-- Liste des résultats : remplie au chargement (PHP) puis mise à jour en JS -->
<div id="results">
    <?php if (empty($services)): ?>
        <p class="muted">Aucune prestation disponible.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($services as $s): ?>
                <article class="card">
                    <?php if (!empty($s['image'])): ?>
                        <div class="card-media">
                            <img src="/assets/images/<?= e($s['image']) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="tag"><?= e($s['category_label']) ?></span>
                        <h3><?= e($s['title']) ?></h3>
                        <p class="meta"><?= (int) $s['duration_min'] ?> min</p>
                        <p class="price"><?= price($s['price']) ?></p>
                        <a class="btn btn-primary btn-block" href="/prestation/<?= e($s['slug']) ?>">Voir le détail</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
