<?php
/** @var array $service @var array $slots @var array $errors */
$errors = $errors ?? [];
?>
<form class="form-card form-wide" action="/reserver/<?= (int) $service['id'] ?>" method="post">
    <h1>Réserver — <?= e($service['title']) ?></h1>
    <p class="muted"><?= (int) $service['duration_min'] ?> min — <span class="price"><?= price($service['price']) ?></span></p>

    <?php if ($errors): ?>
        <div class="errors-box">
            <strong>Veuillez corriger :</strong>
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <?= csrf() ?>
    <div class="field">
        <label for="booking_date">Date souhaitée</label>
        <input type="date" id="booking_date" name="booking_date" min="<?= date('Y-m-d') ?>"
               value="<?= e($_POST['booking_date'] ?? '') ?>" required>
    </div>
    <div class="field">
        <label for="time_slot">Créneau horaire</label>
        <select id="time_slot" name="time_slot" required>
            <option value="">— Choisir —</option>
            <?php foreach ($slots as $slot): ?>
                <option value="<?= e($slot) ?>" <?= (($_POST['time_slot'] ?? '') === $slot) ? 'selected' : '' ?>>
                    <?= e($slot) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="actions">
        <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
        <a class="btn btn-ghost" href="/prestation/<?= e($service['slug']) ?>">Annuler</a>
    </div>
</form>
