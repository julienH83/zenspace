<?php
/**
 * @var array $byService @var float $totalRevenue @var int $totalCount
 * @var string $from @var string $to
 */
$maxRevenue = 0;
foreach ($byService as $row) { $maxRevenue = max($maxRevenue, $row['revenue']); }
$maxRevenue = $maxRevenue ?: 1;

// Données du graphique préparées CÔTÉ PHP puis sérialisées en JSON. Les libellés
// (titres de prestations) sont des chaînes : json_encode les échappe pour un
// contexte <script> (JSON_HEX_TAG/APOS/QUOT/AMP empêchent toute injection).
$chartLabels = array_map('strval', array_keys($byService));
$chartValues = array_map(static fn(array $r): float => round((float) $r['revenue'], 2), array_values($byService));
$jsonFlags   = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

// Résumé textuel pour l'aria-label du canvas (accessibilité : décrit les données
// que le lecteur d'écran ne « voit » pas dans le graphique).
$ariaParts = [];
foreach ($byService as $title => $row) {
    $ariaParts[] = $title . ' : ' . number_format((float) $row['revenue'], 2, ',', ' ') . ' euros';
}
$chartAria = 'Chiffre d\'affaires par prestation. ' . implode(' ; ', $ariaParts);
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
    <!-- Graphique en barres (Chart.js via unpkg, autorisé par la CSP).
         Le canvas porte un aria-label décrivant les données ; le tableau
         ci-dessous reste le repli accessible et fonctionne sans JavaScript. -->
    <div class="chart-wrap" style="max-width:760px;margin:0 auto 24px">
        <canvas id="revenueChart" role="img" aria-label="<?= e($chartAria) ?>"></canvas>
    </div>

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

    <script src="https://unpkg.com/chart.js" defer></script>
    <script>
        // Données injectées depuis PHP (déjà échappées pour le contexte script).
        const labels = <?= json_encode($chartLabels, $jsonFlags) ?>;
        const values = <?= json_encode($chartValues, $jsonFlags) ?>;

        // Respect de prefers-reduced-motion : on désactive les animations Chart.js
        // pour les utilisateurs qui préfèrent un affichage sans mouvement.
        const reduceMotion = window.matchMedia
            && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Chart.js est chargé en différé (defer) : on attend qu'il soit prêt.
        window.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('revenueChart');
            if (!canvas || typeof Chart === 'undefined') {
                return; // dégradation propre : le tableau reste affiché
            }
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Chiffre d\'affaires (€)',
                        data: values,
                        backgroundColor: '#6b8e7f'
                    }]
                },
                options: {
                    responsive: true,
                    animation: reduceMotion ? false : undefined,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        });
    </script>
<?php endif; ?>
