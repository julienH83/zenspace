<?php /** @var array $article */ ?>
<article class="form-card form-wide article">
    <p style="margin-bottom:6px"><a href="/magazine">← Retour au magazine</a></p>

    <div class="page-head">
        <h1><?= e($article['title']) ?></h1>
    </div>
    <p class="muted">Publié le <?= e(date('d/m/Y', strtotime((string) $article['published_at']))) ?></p>

    <?php if (!empty($article['cover_image'])): ?>
        <div class="card-media" style="margin:16px 0">
            <img src="/assets/images/<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" loading="lazy">
        </div>
    <?php endif; ?>

    <?php
    /*
     * SÉCURITÉ — choix d'affichage du corps de l'article :
     * Le corps est considéré comme du TEXTE saisi par un rédacteur. On l'échappe
     * systématiquement avec e() (protection XSS) puis nl2br() convertit les sauts
     * de ligne en <br> pour préserver la mise en forme. On n'injecte donc JAMAIS
     * de HTML brut dans la page, même si la valeur stockée en contient.
     */
    ?>
    <div class="article-body">
        <?= nl2br(e((string) $article['body'])) ?>
    </div>
</article>
