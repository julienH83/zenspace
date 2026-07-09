<?php /** @var array $articles */ ?>
<div class="page-head">
    <h1>Magazine bien-être</h1>
</div>
<p class="muted">Nos conseils pour prendre soin de vous au quotidien : massage, sommeil, respiration.</p>

<?php if (empty($articles)): ?>
    <p class="muted">Aucun article publié pour le moment. Revenez bientôt !</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($articles as $a): ?>
            <article class="card">
                <?php if (!empty($a['cover_image'])): ?>
                    <div class="card-media">
                        <img src="/assets/images/<?= e($a['cover_image']) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <p class="meta"><?= e(date('d/m/Y', strtotime((string) $a['published_at']))) ?></p>
                    <h3><?= e($a['title']) ?></h3>
                    <?php if (!empty($a['excerpt'])): ?>
                        <p><?= e($a['excerpt']) ?></p>
                    <?php endif; ?>
                    <a class="btn btn-primary btn-block" href="/magazine/<?= e($a['slug']) ?>">Lire l'article</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
