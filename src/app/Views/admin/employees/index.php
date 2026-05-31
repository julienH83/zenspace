<?php /** @var array $employees */ ?>
<div class="page-head">
    <h1>Employés</h1>
    <a class="btn btn-primary" href="/admin/employes/nouveau">+ Nouvel employé</a>
</div>

<?php if (empty($employees)): ?>
    <p class="muted">Aucun employé enregistré.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr><th>Nom</th><th>E-mail</th><th>Actif</th><th>Créé le</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                    <td><?= e($emp['email']) ?></td>
                    <td><?= ((int) $emp['is_active']) ? 'Oui' : 'Non' ?></td>
                    <td><?= e(date('d/m/Y', strtotime($emp['created_at']))) ?></td>
                    <td>
                        <?php if ((int) $emp['is_active']): ?>
                            <form action="/admin/employes/<?= (int) $emp['id'] ?>/desactiver" method="post" class="inline-form"
                                  onsubmit="return confirm('Désactiver ce compte employé ?');">
                                <?= csrf() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Désactiver</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Désactivé</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
