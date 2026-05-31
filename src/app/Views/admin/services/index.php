<?php /** @var array $services */ ?>
<div class="page-head">
    <h1>Prestations</h1>
    <a class="btn btn-primary" href="/admin/prestations/nouvelle">+ Nouvelle prestation</a>
</div>

<div class="table-wrap">
    <table class="data">
        <thead>
        <tr><th>Titre</th><th>Catégorie</th><th>Durée</th><th>Prix</th><th>Active</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td><?= e($s['title']) ?></td>
                <td><?= e($s['category_label']) ?></td>
                <td><?= (int) $s['duration_min'] ?> min</td>
                <td><?= price($s['price']) ?></td>
                <td><?= ((int) $s['is_active']) ? 'Oui' : 'Non' ?></td>
                <td class="actions">
                    <a class="btn btn-ghost btn-sm" href="/admin/prestations/<?= (int) $s['id'] ?>/editer">Modifier</a>
                    <form action="/admin/prestations/<?= (int) $s['id'] ?>/supprimer" method="post" class="inline-form"
                          onsubmit="return confirm('Désactiver cette prestation ?');">
                        <?= csrf() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Désactiver</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
