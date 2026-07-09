<?php
/** @var array $services @var array $reviews */
?>
<!-- Hero éditorial : photo pleine largeur + titre serif + un seul appel à l'action. -->
<section class="hero" style="background-image:linear-gradient(180deg,rgba(20,30,26,.28),rgba(20,30,26,.55)),url('/assets/images/hero-spa.jpg')">
    <div class="hero-inner">
        <p class="hero-kicker">Institut de bien-être · Bordeaux</p>
        <h1>Offrez-vous une parenthèse de bien-être</h1>
        <p class="hero-lede">Massages, soins du visage et accès spa, dans un cadre apaisant.
           Prenez le temps qu'il vous faut.</p>
        <a href="/prestations" class="btn btn-primary">Découvrir nos prestations</a>
    </div>
</section>

<section class="section">
    <div class="media-row">
        <img src="/assets/images/equipe.jpg" alt="Praticienne de l'institut ZenSpace en plein soin" loading="lazy">
        <div class="media-text">
            <p class="eyebrow">Notre maison</p>
            <h2>Une équipe à votre écoute</h2>
            <p>
                Des praticiens diplômés et passionnés vous accueillent dans un cadre apaisant.
                Chaque soin est personnalisé pour répondre à vos besoins, dans le respect et l'écoute.
            </p>
        </div>
    </div>
</section>

<section class="section">
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
                        <div class="card-media">
                            <img src="/assets/images/<?= e($s['image']) ?>" alt="" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="tag"><?= e($s['category_label']) ?></span>
                        <h3><?= e($s['title']) ?></h3>
                        <p class="meta"><?= (int) $s['duration_min'] ?> min · <span class="price"><?= price($s['price']) ?></span></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($reviews)): ?>
    <section class="section">
        <h2>Ils nous ont fait confiance</h2>
        <div class="grid">
            <?php foreach ($reviews as $r): ?>
                <?php $stars = max(0, min(5, (int) $r['rating'])); ?>
                <figure class="review">
                    <p class="stars" aria-label="Note : <?= $stars ?> sur 5"><?= str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) ?></p>
                    <blockquote><?= e($r['comment']) ?></blockquote>
                    <figcaption class="muted"><strong><?= e($r['first_name']) ?></strong> — <?= e($r['service_title']) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
