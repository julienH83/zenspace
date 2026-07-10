<?php /** @var array $articles */ ?>
<div class="page-head">
    <h1>Magazine</h1>
    <a class="btn btn-primary" href="/admin/magazine/nouveau">+ Nouvel article</a>
</div>

<?php if (empty($articles)): ?>
    <p class="muted">Aucun article pour le moment. <a href="/admin/magazine/nouveau">Rédiger le premier</a>.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr><th>Titre</th><th>Statut</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($articles as $a): ?>
                <?php $isPublished = !empty($a['published_at']); ?>
                <tr>
                    <td><?= e($a['title']) ?></td>
                    <td>
                        <span class="badge <?= $isPublished ? 'badge-completed' : 'badge-pending' ?>">
                            <?= $isPublished ? 'Publié' : 'Brouillon' ?>
                        </span>
                    </td>
                    <td><?= e(date('d/m/Y', strtotime($a['published_at'] ?? $a['created_at']))) ?></td>
                    <td class="actions">
                        <a class="btn btn-ghost btn-sm" href="/admin/magazine/<?= (int) $a['id'] ?>/editer">Modifier</a>
                        <form action="/admin/magazine/<?= (int) $a['id'] ?>/supprimer" method="post" class="inline-form"
                              onsubmit="return confirm('Supprimer définitivement cet article ?');">
                            <?= csrf() ?>
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
