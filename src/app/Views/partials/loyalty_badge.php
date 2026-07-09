<?php
/**
 * Partial réutilisable : badge de fidélité (solde de points + palier).
 *
 * Attend une variable $points (int) dans le scope de la vue appelante.
 * Le palier est calculé ici à partir du solde, ce qui évite de dupliquer la
 * logique côté contrôleur.
 *
 * APPEL DEPUIS UNE VUE (ex. booking/list.php), après avoir récupéré le solde
 * dans le contrôleur via LoyaltyRepository::balance() :
 *
 *   <?php
 *     $points = $loyaltyPoints ?? 0;          // valeur transmise par le contrôleur
 *     include __DIR__ . '/../partials/loyalty_badge.php';
 *   ?>
 *
 * @var int $points Solde de points de fidélité du client.
 */

$points = (int) ($points ?? 0);

// Paliers : déterminés par le solde courant. Le seuil le plus élevé atteint gagne.
$tiers = [
    1000 => ['label' => 'Or',     'class' => 'gold'],
    500  => ['label' => 'Argent', 'class' => 'silver'],
    0    => ['label' => 'Bronze', 'class' => 'bronze'],
];

$tierLabel = 'Bronze';
$tierClass = 'bronze';
foreach ($tiers as $threshold => $tier) {
    if ($points >= $threshold) {
        $tierLabel = $tier['label'];
        $tierClass = $tier['class'];
        break;
    }
}
?>
<div class="loyalty-badge loyalty-<?= e($tierClass) ?>" role="status">
    <span class="loyalty-points"><strong><?= number_format($points, 0, ',', ' ') ?></strong> points</span>
    <span class="loyalty-tier">Palier <?= e($tierLabel) ?></span>
</div>
