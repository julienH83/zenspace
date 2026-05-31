<?php
/**
 * @var array $byService @var float $totalRevenue @var int $totalCount
 * @var string $from @var string $to
 */
$maxRevenue = 0;
foreach ($byService as $row) { $maxRevenue = max($maxRevenue, $row['revenue']); }
$maxRevenue = $maxRevenue ?: 1;
?>
<div class="page-head"><h1>Statistiques</h1></div>
<p class="muted">Données issues de la base <strong>NoSQL (MongoDB)</strong> : chaque prestation
   terminée y enregistre son chiffre d'affaires.</p>

<form method="get" class="filters">
    <div class="field" style="margin:0">
        <label for="from">Du</label>
        <input type="date" id="from" name="from" value="<?= e($from) ?>">
    </div>
    <div class="field" style="margin:0">
        <label for="to">Au</label>
        <input type="date" id="to" name="to" value="<?= e($to) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Filtrer</button>
</form>

<div class="stat-cards">
    <div class="stat-card">
        <div class="big"><?= price($totalRevenue) ?></div>
        <div class="muted">Chiffre d'affaires total</div>
    </div>
    <div class="stat-card">
        <div class="big"><?= (int) $totalCount ?></div>
        <div class="muted">Prestations réalisées</div>
    </div>
</div>

<h2>Chiffre d'affaires par prestation</h2>
<?php if (empty($byService)): ?>
    <p class="muted">Aucune donnée pour le moment. Marquez des réservations comme « Terminée »
       pour alimenter les statistiques.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>Prestation</th><th>Nombre</th><th>CA</th><th>Répartition</th></tr></thead>
            <tbody>
            <?php foreach ($byService as $title => $row): ?>
                <tr>
                    <td><?= e((string) $title) ?></td>
                    <td><?= (int) $row['count'] ?></td>
                    <td><?= price($row['revenue']) ?></td>
                    <td><div class="bar" style="width: <?= (int) round(100 * $row['revenue'] / $maxRevenue) ?>%"></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
