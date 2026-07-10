<?php
/** @var array $services @var array $reviews @var array $articles */
include __DIR__ . '/../partials/icons.php';
?>
<!-- Héros à deux colonnes : discours + appels à l'action à gauche, photo à droite. -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <p class="hero-kicker">Institut de bien-être · Bordeaux</p>
            <h1>Réservez votre soin bien-être en quelques clics</h1>
            <p class="hero-lede">Massages, soins du visage, spa &amp; hammam. Choisissez votre prestation,
               votre créneau, et laissez-vous porter.</p>
            <div class="hero-actions">
                <a href="/prestations" class="btn btn-primary">Voir les prestations</a>
                <a href="/contact" class="btn btn-ghost">Nous contacter</a>
            </div>
            <ul class="hero-points">
                <li>Praticiens diplômés</li>
                <li>Réservation en ligne</li>
                <li>Points de fidélité</li>
            </ul>
        </div>
        <div class="hero-media">
            <?= picture('hero-spa.jpg', 'Ambiance apaisante de l\'institut ZenSpace', 'eager') ?>
        </div>
    </div>
</section>

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
