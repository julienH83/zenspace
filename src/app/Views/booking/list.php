<?php /** @var array $bookings */ ?>
<div class="page-head"><h1>Mes réservations</h1></div>

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
