<?php
/** @var array $booking @var array $history */
?>
<article class="form-card form-wide">
    <div class="page-head">
        <h1>Réservation #<?= (int) $booking['id'] ?></h1>
        <span class="badge badge-<?= e($booking['status']) ?>"><?= e(status_label($booking['status'])) ?></span>
    </div>

    <p><strong>Prestation :</strong> <?= e($booking['service_title']) ?></p>
    <p><strong>Date :</strong> <?= e(date('d/m/Y', strtotime($booking['booking_date']))) ?>
       à <?= e(substr($booking['time_slot'], 0, 5)) ?></p>
    <p><strong>Prix :</strong> <?= price($booking['total_price']) ?></p>

    <h2>Suivi</h2>
    <ul>
        <?php foreach ($history as $h): ?>
            <li><?= e(status_label($h['status'])) ?>
                — <span class="muted"><?= e(date('d/m/Y H:i', strtotime($h['changed_at']))) ?></span></li>
        <?php endforeach; ?>
    </ul>

    <?php if ($booking['status'] === 'pending'): ?>
        <form action="/reservation/<?= (int) $booking['id'] ?>/annuler" method="post" class="actions">
            <?= csrf() ?>
            <button type="submit" class="btn btn-danger">Annuler la réservation</button>
        </form>
    <?php endif; ?>

    <?php if ($booking['status'] === 'completed'): ?>
        <h2>Laisser un avis</h2>
        <form action="/avis" method="post">
            <?= csrf() ?>
            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
            <div class="field">
                <label for="rating">Note</label>
                <select id="rating" name="rating" required>
                    <option value="5">★★★★★ — Excellent</option>
                    <option value="4">★★★★ — Très bien</option>
                    <option value="3">★★★ — Bien</option>
                    <option value="2">★★ — Moyen</option>
                    <option value="1">★ — Décevant</option>
                </select>
            </div>
            <div class="field">
                <label for="comment">Commentaire</label>
                <textarea id="comment" name="comment" placeholder="Partagez votre expérience…"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
        </form>
    <?php endif; ?>

    <p style="margin-top:18px"><a href="/mon-compte">← Retour à mes réservations</a></p>
</article>
