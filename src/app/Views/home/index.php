<?php
/** @var array $services @var array $reviews @var array $articles */
include __DIR__ . '/../partials/icons.php';
?>
<!-- Hero éditorial : photo pleine largeur (WebP + repli JPEG via image-set),
     voile dégradé posé en CSS (.hero::before), titre serif et deux appels à l'action. -->
<section class="hero"
         style="background-image: url('/assets/images/hero-spa.jpg');
                background-image: -webkit-image-set(url('/assets/images/hero-spa.webp') type('image/webp'), url('/assets/images/hero-spa.jpg') type('image/jpeg'));
                background-image: image-set(url('/assets/images/hero-spa.webp') type('image/webp'), url('/assets/images/hero-spa.jpg') type('image/jpeg'));">
    <div class="hero-inner">
        <p class="hero-kicker">Institut de bien-être · Bordeaux</p>
        <h1>Offrez-vous une parenthèse de bien-être</h1>
        <p class="hero-lede">Massages, soins du visage et accès spa, dans un cadre apaisant.
           Prenez le temps qu'il vous faut.</p>
        <div class="hero-actions">
            <a href="/prestations" class="btn btn-primary">Découvrir nos prestations</a>
            <a href="#deroulement" class="btn btn-ghost">Comment ça se passe</a>
        </div>
    </div>
</section>

<!-- Nos engagements : trois atouts, chacun introduit par une icône SVG inline. -->
<div class="band reveal">
    <div class="container">
        <p class="eyebrow">Nos engagements</p>
        <h2>Le bien-être, avec exigence</h2>
        <div class="features">
            <div class="feature">
                <span class="feature-icon"><?= icon('leaf', 'icon icon-lg') ?></span>
                <h3>Un cadre apaisant</h3>
                <p>Un institut pensé pour la détente, loin de l'agitation, où chaque détail invite au calme.</p>
            </div>
            <div class="feature">
                <span class="feature-icon"><?= icon('hands', 'icon icon-lg') ?></span>
                <h3>Des praticiens diplômés</h3>
                <p>Des soins personnalisés, réalisés par une équipe expérimentée et à l'écoute.</p>
            </div>
            <div class="feature">
                <span class="feature-icon"><?= icon('calendar', 'icon icon-lg') ?></span>
                <h3>Réservation en ligne</h3>
                <p>Choisissez votre prestation et votre créneau en quelques clics, à toute heure.</p>
            </div>
        </div>
    </div>
</div>

<section class="section reveal">
    <div class="media-row">
        <?= picture('equipe.jpg', 'Praticienne de l\'institut ZenSpace en plein soin') ?>
        <div class="media-text">
            <p class="eyebrow">Notre maison</p>
            <h2>Une équipe à votre écoute</h2>
            <p>
                Des praticiens diplômés et passionnés vous accueillent dans un cadre apaisant.
                Chaque soin est personnalisé pour répondre à vos besoins, dans le respect et l'écoute.
            </p>
            <a class="link-more" href="/prestations">Voir nos prestations →</a>
        </div>
    </div>
</section>

<section class="section reveal">
    <div class="section-head">
        <h2>Prestations phares</h2>
        <a class="link-more" href="/prestations">Voir tout le catalogue</a>
    </div>
    <?php if (empty($services)): ?>
        <p class="muted">Aucune prestation disponible pour le moment.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($services as $s): ?>
                <a class="card card-link" href="/prestation/<?= e($s['slug']) ?>">
                    <?php if (!empty($s['image'])): ?>
                        <div class="card-media"><?= picture($s['image'], '') ?></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="tag"><?= e($s['category_label']) ?></span>
                        <h3><?= e($s['title']) ?></h3>
                        <?= stars($s['rating_avg'] ?? 0, $s['rating_count'] ?? 0) ?>
                        <p class="meta"><?= (int) $s['duration_min'] ?> min · <span class="price"><?= price($s['price']) ?></span></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Comment ça se passe : parcours en quatre étapes (la dernière introduit la fidélité). -->
<div id="deroulement" class="band reveal">
    <div class="container">
        <p class="eyebrow">En pratique</p>
        <h2>Comment ça se passe</h2>
        <div class="steps">
            <div class="step">
                <span class="step-num">1</span>
                <h3>Choisissez votre soin</h3>
                <p>Parcourez le catalogue et filtrez selon la catégorie, le prix ou la durée.</p>
            </div>
            <div class="step">
                <span class="step-num">2</span>
                <h3>Réservez un créneau</h3>
                <p>Sélectionnez le jour et l'heure qui vous conviennent, en un clic.</p>
            </div>
            <div class="step">
                <span class="step-num">3</span>
                <h3>Profitez de l'instant</h3>
                <p>Laissez nos praticiens prendre soin de vous dans un cadre serein.</p>
            </div>
            <div class="step">
                <span class="step-num">4</span>
                <h3>Cumulez des points</h3>
                <p>Chaque prestation terminée vous rapporte des points de fidélité.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($articles)): ?>
    <section class="section reveal">
        <div class="section-head">
            <h2>Magazine bien-être</h2>
            <a class="link-more" href="/magazine">Tous les articles</a>
        </div>
        <div class="grid">
            <?php foreach ($articles as $a): ?>
                <a class="card card-link article-card" href="/magazine/<?= e($a['slug']) ?>">
                    <?php if (!empty($a['cover_image'])): ?>
                        <div class="card-media"><?= picture($a['cover_image'], '') ?></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <?php if (!empty($a['published_at'])): ?>
                            <time datetime="<?= e(date('Y-m-d', strtotime($a['published_at']))) ?>">
                                <?= e(date('d/m/Y', strtotime($a['published_at']))) ?>
                            </time>
                        <?php endif; ?>
                        <h3><?= e($a['title']) ?></h3>
                        <?php if (!empty($a['excerpt'])): ?>
                            <p class="muted"><?= e($a['excerpt']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($reviews)): ?>
    <section class="section reveal">
        <h2>Ils nous ont fait confiance</h2>
        <div class="grid">
            <?php foreach ($reviews as $r): ?>
                <?php $s = max(0, min(5, (int) $r['rating'])); ?>
                <figure class="review">
                    <p class="stars" aria-label="Note : <?= $s ?> sur 5"><?= str_repeat('★', $s) . str_repeat('☆', 5 - $s) ?></p>
                    <blockquote><?= e($r['comment']) ?></blockquote>
                    <figcaption class="muted"><strong><?= e($r['first_name']) ?></strong> — <?= e($r['service_title']) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Appel à l'action final avant le pied de page. -->
<div class="cta-band reveal">
    <div class="container">
        <h2>Prêt·e à vous offrir une pause ?</h2>
        <p>Réservez votre prochaine prestation en quelques clics et laissez-vous porter.</p>
        <a href="/prestations" class="btn btn-primary">Réserver maintenant</a>
    </div>
</div>
