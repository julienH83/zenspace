<?php
/** @var array $services @var array $reviews */
?>
<section class="hero hero-3d">
    <!-- Toile (canvas) sur laquelle Three.js dessine l'animation 3D en fond -->
    <canvas id="hero-canvas" aria-hidden="true"></canvas>
    <div class="hero-content">
        <h1>Offrez-vous une parenthèse de bien-être</h1>
        <p>Massages, soins du visage et accès spa au cœur de Bordeaux.
           Réservez votre prestation en quelques clics.</p>
        <a href="/prestations" class="btn btn-accent">Découvrir nos prestations</a>
    </div>
</section>

<!-- Three.js (bibliothèque 3D) chargée depuis un CDN, puis notre scène -->
<script type="importmap">
{ "imports": { "three": "https://unpkg.com/three@0.160.0/build/three.module.js" } }
</script>
<script type="module" src="/assets/js/hero3d.js"></script>

<section class="section">
    <div class="media-row">
        <img src="/assets/images/equipe.jpg" alt="Praticienne de l'institut ZenSpace en plein soin">
        <div class="media-text">
            <h2>Notre équipe</h2>
            <p class="muted">
                Une équipe de praticiens diplômés et passionnés vous accueille dans un
                cadre apaisant. Chaque soin est personnalisé pour répondre à vos besoins,
                dans le respect et l'écoute.
            </p>
        </div>
    </div>
</section>

<section class="section">
    <h2>Prestations phares</h2>
    <?php if (empty($services)): ?>
        <p class="muted">Aucune prestation disponible pour le moment.</p>
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
</section>

<section class="section">
    <h2>Ils nous ont fait confiance</h2>
    <?php if (empty($reviews)): ?>
        <p class="muted">Soyez le premier à laisser un avis !</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($reviews as $r): ?>
                <div class="review">
                    <p class="stars"><?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?></p>
                    <p><?= e($r['comment']) ?></p>
                    <p class="muted"><strong><?= e($r['first_name']) ?></strong> — <?= e($r['service_title']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
