<?php /** @var array $reviews */ ?>
<div class="page-head"><h1>Modérer les avis</h1></div>

<?php if (empty($reviews)): ?>
    <p class="muted">Aucun avis en attente de modération.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($reviews as $r): ?>
            <?php $stars = max(0, min(5, (int) $r['rating'])); ?>
            <div class="review">
                <p class="stars" aria-label="Note : <?= $stars ?> sur 5"><?= str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) ?></p>
                <p><?= e($r['comment'] ?: '(Pas de commentaire)') ?></p>
                <p class="muted">
                    <strong><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong>
                    — <?= e($r['service_title']) ?>
                </p>
                <div class="actions">
                    <form action="/admin/avis/<?= (int) $r['id'] ?>/valider" method="post" class="inline-form">
                        <?= csrf() ?>
                        <button type="submit" class="btn btn-primary btn-sm">Valider</button>
                    </form>
                    <form action="/admin/avis/<?= (int) $r['id'] ?>/refuser" method="post" class="inline-form">
                        <?= csrf() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
