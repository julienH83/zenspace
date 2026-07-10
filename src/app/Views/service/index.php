<?php
/**
 * @var array $services    Prestations déjà filtrées côté serveur
 * @var array $categories  Catégories disponibles (slug + label)
 * @var array $filters     Filtres actifs ['category','max_price','max_duration']
 */
$filters = $filters ?? [];
// Garde-fous : valeurs par défaut si le filtre est vide.
$fCategory = (string) ($filters['category'] ?? '');
$fMaxPrice = (int) ($filters['max_price'] ?? 0) ?: 150;
$fMaxDuration = (int) ($filters['max_duration'] ?? 0) ?: 120;
?>
<div class="page-head">
    <h1>Nos prestations</h1>
</div>

<!-- Filtres : vrai formulaire GET (fonctionne sans JS) ; le JS améliore
     l'expérience en filtrant sans rechargement (voir assets/js/app.js). -->
<form id="filters" class="filters" method="get" action="/prestations" aria-label="Filtrer les prestations">
    <div class="field" style="margin:0">
        <label for="category">Catégorie</label>
        <select id="category" name="category">
            <option value="">Toutes</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= e($c['slug']) ?>" <?= ($fCategory === $c['slug']) ? 'selected' : '' ?>><?= e($c['label']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field" style="margin:0">
        <label for="max_price">Prix max : <span id="max_price_value"><?= $fMaxPrice ?> €</span></label>
        <input type="range" id="max_price" name="max_price" min="30" max="150" step="10" value="<?= $fMaxPrice ?>">
    </div>
    <div class="field" style="margin:0">
        <label for="max_duration">Durée max : <span id="max_duration_value"><?= $fMaxDuration ?> min</span></label>
        <input type="range" id="max_duration" name="max_duration" min="30" max="120" step="15" value="<?= $fMaxDuration ?>">
    </div>
    <noscript><button type="submit" class="btn btn-primary">Filtrer</button></noscript>
</form>

<!-- Liste des résultats : rendue côté PHP (déjà filtrée), mise à jour en JS. -->
<div id="results" role="region" aria-label="Résultats" aria-live="polite">
    <?php if (empty($services)): ?>
        <p class="muted">Aucune prestation disponible.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($services as $s): ?>
                <?php include __DIR__ . '/../partials/service_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
