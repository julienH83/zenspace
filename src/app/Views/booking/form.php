<?php
/** @var array $service @var array $slots @var array $errors */
$errors = $errors ?? [];
// Pré-remplissage sûr (valeurs scalaires uniquement) : POST prioritaire, sinon
// les paramètres GET issus d'un clic sur un créneau du planning.
$g = static fn(string $k): string => is_string($_GET[$k] ?? null) ? $_GET[$k] : '';
$prefillDate = is_string($_POST['booking_date'] ?? null) ? $_POST['booking_date'] : $g('date');
$prefillSlot = is_string($_POST['time_slot'] ?? null) ? $_POST['time_slot'] : $g('slot');
?>
<form class="form-card form-wide" action="/reserver/<?= (int) $service['id'] ?>" method="post">
    <h1>Réserver — <?= e($service['title']) ?></h1>
    <p class="muted"><?= (int) $service['duration_min'] ?> min — <span class="price"><?= price($service['price']) ?></span></p>

    <?php if ($errors): ?>
        <div class="errors-box" role="alert">
            <strong>Veuillez corriger :</strong>
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <?= csrf() ?>
    <div class="field">
        <label for="booking_date">Date souhaitée</label>
        <input type="date" id="booking_date" name="booking_date" min="<?= date('Y-m-d') ?>"
               value="<?= e($prefillDate) ?>" required
               <?php if (isset($errors['booking_date'])): ?>aria-invalid="true" aria-describedby="booking_date-err"<?php endif; ?>>
        <?php if (isset($errors['booking_date'])): ?><p id="booking_date-err" class="field-error" role="alert"><?= e($errors['booking_date']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="time_slot">Créneau horaire</label>
        <select id="time_slot" name="time_slot" required
                <?php if (isset($errors['time_slot'])): ?>aria-invalid="true" aria-describedby="time_slot-err"<?php endif; ?>>
            <option value="">— Choisir —</option>
            <?php foreach ($slots as $slot): ?>
                <option value="<?= e($slot) ?>" <?= ($prefillSlot === $slot) ? 'selected' : '' ?>>
                    <?= e($slot) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['time_slot'])): ?><p id="time_slot-err" class="field-error" role="alert"><?= e($errors['time_slot']) ?></p><?php endif; ?>
    </div>
    <div class="actions">
        <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
        <a class="btn btn-ghost" href="/prestation/<?= e($service['slug']) ?>">Annuler</a>
    </div>
</form>
