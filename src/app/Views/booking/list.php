<?php /** @var array $bookings @var int $loyaltyPoints @var array $loyaltyHistory */ ?>
<div class="page-head"><h1>Mes réservations</h1></div>

<?php // --- Programme de fidélité : solde + derniers mouvements de points --- ?>
<section class="loyalty-panel" aria-labelledby="loyalty-title">
    <div class="loyalty-panel-head">
        <div>
            <p class="eyebrow">Programme de fidélité</p>
            <h2 id="loyalty-title" class="loyalty-heading">Vos points ZenSpace</h2>
            <p class="muted">Vous gagnez 1 point par euro dès qu'une prestation est terminée.</p>
        </div>
        <?php $points = (int) ($loyaltyPoints ?? 0); include __DIR__ . '/../partials/loyalty_badge.php'; ?>
    </div>

    <?php if (!empty($loyaltyHistory)): ?>
        <ul class="loyalty-history">
            <?php foreach (array_slice($loyaltyHistory, 0, 4) as $mv): ?>
                <li>
                    <span class="loyalty-delta <?= (int) $mv['delta'] >= 0 ? 'is-gain' : 'is-spend' ?>">
                        <?= (int) $mv['delta'] >= 0 ? '+' : '' ?><?= (int) $mv['delta'] ?>
                    </span>
                    <span class="loyalty-reason"><?= e($mv['reason']) ?></span>
                    <span class="muted loyalty-date"><?= e(date('d/m/Y', strtotime($mv['created_at']))) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">Aucun point pour le moment — votre première prestation terminée vous en rapportera.</p>
    <?php endif; ?>
</section>

<?php if (empty($bookings)): ?>
    <p class="muted">Vous n'avez aucune réservation. <a href="/prestations">Découvrir nos prestations</a></p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr><th>Prestation</th><th>Date</th><th>Créneau</th><th>Prix</th><th>Statut</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= e($b['service_title']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($b['booking_date']))) ?></td>
                    <td><?= e(substr($b['time_slot'], 0, 5)) ?></td>
                    <td><?= price($b['total_price']) ?></td>
                    <td><span class="badge badge-<?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span></td>
                    <td><a href="/reservation/<?= (int) $b['id'] ?>">Détail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
