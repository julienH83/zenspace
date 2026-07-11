<?php
/** @var array $services @var array $reviews @var array $articles */
include __DIR__ . '/../partials/icons.php';
?>
<!-- Héros dramatique plein cadre : grande photo voilée, titre serif, détails or. -->
<section class="hero"
         style="background-image: url('/assets/images/hero-spa.jpg');
                background-image: -webkit-image-set(url('/assets/images/hero-spa.webp') type('image/webp'), url('/assets/images/hero-spa.jpg') type('image/jpeg'));
                background-image: image-set(url('/assets/images/hero-spa.webp') type('image/webp'), url('/assets/images/hero-spa.jpg') type('image/jpeg'));">
    <div class="hero-inner">
        <span class="hero-rule" aria-hidden="true"></span>
        <p class="hero-eyebrow">Institut de bien-être · Bordeaux</p>
        <h1>L'art de <span class="gold">prendre soin</span> de vous</h1>
        <p class="hero-lede">Massages, soins du visage, spa &amp; hammam. Réservez votre parenthèse
           d'exception en quelques clics.</p>
        <div class="hero-actions">
            <a href="/prestations" class="btn btn-primary">Réserver un soin</a>
            <a href="/prestations" class="btn btn-ghost">Découvrir la carte</a>
        </div>
        <ul class="hero-points">
            <li>Praticiens diplômés</li>
            <li>Sur rendez-vous</li>
            <li>Points de fidélité</li>
        </ul>
    </div>
</section>

<!-- Bandeau de chiffres-clés : crédibilité immédiate (look site pro). -->
<div class="stats-band">
    <div class="container stats-grid">
        <div class="stat"><span class="stat-num">15<span class="stat-unit">+</span></span><span class="stat-label">ans d'expérience</span></div>
        <div class="stat"><span class="stat-num">6</span><span class="stat-label">prestations signature</span></div>
        <div class="stat"><span class="stat-num">4,9<span class="stat-unit">/5</span></span><span class="stat-label">note moyenne</span></div>
        <div class="stat"><span class="stat-num">2000<span class="stat-unit">+</span></span><span class="stat-label">soins prodigués</span></div>
    </div>
</div>

<!-- Prestations mises en avant, dès l'entrée (cœur du site). -->
<section class="section reveal">
    <div class="section-head">
        <h2>Nos prestations phares</h2>
        <a class="link-more" href="/prestations">Tout le catalogue →</a>
    </div>
    <?php if (empty($services)): ?>
        <p class="muted">Aucune prestation disponible pour le moment.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($services as $s): ?>
                <?php include __DIR__ . '/../partials/service_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Nos engagements : trois atouts, chacun introduit par une icône SVG inline. -->
<div class="band reveal">
    <div class="container">
        <div class="features">
            <div class="feature">
                <span class="feature-icon"><?= icon('leaf', 'icon icon-lg') ?></span>
                <h3>Un cadre apaisant</h3>
                <p>Un institut pensé pour la détente, loin de l'agitation.</p>
            </div>
            <div class="feature">
                <span class="feature-icon"><?= icon('hands', 'icon icon-lg') ?></span>
                <h3>Des praticiens diplômés</h3>
                <p>Des soins personnalisés par une équipe à l'écoute.</p>
            </div>
            <div class="feature">
                <span class="feature-icon"><?= icon('calendar', 'icon icon-lg') ?></span>
                <h3>Réservation en ligne</h3>
                <p>Choisissez votre créneau en quelques clics, à toute heure.</p>
            </div>
        </div>
    </div>
</div>

<!-- Comment ça se passe : parcours en quatre étapes. -->
<section class="section reveal">
    <p class="eyebrow">En pratique</p>
    <h2>Comment ça se passe</h2>
    <div class="steps">
        <div class="step">
            <span class="step-num">1</span>
            <h3>Choisissez votre soin</h3>
            <p>Filtrez le catalogue par catégorie, prix ou durée.</p>
        </div>
        <div class="step">
            <span class="step-num">2</span>
            <h3>Réservez un créneau</h3>
            <p>Sélectionnez le jour et l'heure qui vous conviennent.</p>
        </div>
        <div class="step">
            <span class="step-num">3</span>
            <h3>Profitez de l'instant</h3>
            <p>Laissez nos praticiens prendre soin de vous.</p>
        </div>
        <div class="step">
            <span class="step-num">4</span>
            <h3>Cumulez des points</h3>
            <p>Chaque prestation terminée vous rapporte des points.</p>
        </div>
    </div>
</section>

<?php if (!empty($reviews)): ?>
    <div class="band reveal">
        <div class="container">
            <div class="section-head">
                <h2>Ils nous ont fait confiance</h2>
            </div>
            <div class="grid">
                <?php foreach ($reviews as $r): ?>
                    <?php $st = max(0, min(5, (int) $r['rating'])); ?>
                    <figure class="review">
                        <p class="stars" aria-label="Note : <?= $st ?> sur 5"><?= str_repeat('★', $st) . str_repeat('☆', 5 - $st) ?></p>
                        <blockquote><?= e($r['comment']) ?></blockquote>
                        <figcaption class="muted"><strong><?= e($r['first_name']) ?></strong> — <?= e($r['service_title']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($articles)): ?>
    <!-- Magazine : secondaire, en bas de page (le site n'est pas une revue). -->
    <section class="section reveal">
        <div class="section-head">
            <h2>Conseils bien-être</h2>
            <a class="link-more" href="/magazine">Tous les articles →</a>
        </div>
        <div class="magazine-strip">
            <?php foreach ($articles as $a): ?>
                <a class="magazine-item" href="/magazine/<?= e($a['slug']) ?>">
                    <?php if (!empty($a['published_at'])): ?>
                        <time datetime="<?= e(date('Y-m-d', strtotime($a['published_at']))) ?>"><?= e(date('d/m/Y', strtotime($a['published_at']))) ?></time>
                    <?php endif; ?>
                    <span class="magazine-title"><?= e($a['title']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Appel à l'action final. -->
<div class="cta-band reveal">
    <div class="container">
        <h2>Prêt·e à vous offrir une pause ?</h2>
        <p>Réservez votre prochaine prestation en quelques clics.</p>
        <a href="/prestations" class="btn btn-primary">Réserver maintenant</a>
    </div>
</div>
