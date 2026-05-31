<?php
/** @var array $bookings @var string $currentStatus @var array $statuses */
?>
<div class="page-head"><h1>Réservations</h1></div>

<!-- Filtre par statut -->
<form method="get" class="filters">
    <div class="field" style="margin:0">
        <label for="status">Filtrer par statut</label>
        <select id="status" name="status" onchange="this.form.submit()">
            <option value="">Tous</option>
            <?php foreach ($statuses as $st): ?>
                <option value="<?= e($st) ?>" <?= ($currentStatus === $st) ? 'selected' : '' ?>>
                    <?= e(status_label($st)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<?php if (empty($bookings)): ?>
    <p class="muted">Aucune réservation pour ce filtre.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr><th>#</th><th>Client</th><th>Prestation</th><th>Date</th><th>Statut</th><th>Changer le statut</th></tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= (int) $b['id'] ?></td>
                    <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                    <td><?= e($b['service_title']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($b['booking_date']))) ?> <?= e(substr($b['time_slot'], 0, 5)) ?></td>
                    <td><span class="badge badge-<?= e($b['status']) ?>"><?= e(status_label($b['status'])) ?></span></td>
                    <td>
                        <form action="/admin/reservations/<?= (int) $b['id'] ?>/statut" method="post" class="inline-form">
                            <?= csrf() ?>
                            <select name="status" onchange="this.form.submit()">
                                <?php foreach ($statuses as $st): ?>
                                    <option value="<?= e($st) ?>" <?= ($b['status'] === $st) ? 'selected' : '' ?>>
                                        <?= e(status_label($st)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
