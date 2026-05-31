<?php
/** @var array $errors @var array $old */
$errors = $errors ?? [];
$old = $old ?? [];
?>
<form class="form-card" action="/inscription" method="post">
    <h1>Créer un compte</h1>
    <?= csrf() ?>

    <div class="field">
        <label for="first_name">Prénom</label>
        <input type="text" id="first_name" name="first_name" value="<?= old($old, 'first_name') ?>" required>
        <?php if (isset($errors['first_name'])): ?><p class="error-text"><?= e($errors['first_name']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="last_name">Nom</label>
        <input type="text" id="last_name" name="last_name" value="<?= old($old, 'last_name') ?>" required>
        <?php if (isset($errors['last_name'])): ?><p class="error-text"><?= e($errors['last_name']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old($old, 'email') ?>" required>
        <?php if (isset($errors['email'])): ?><p class="error-text"><?= e($errors['email']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="phone">Téléphone</label>
        <input type="tel" id="phone" name="phone" value="<?= old($old, 'phone') ?>" required>
        <?php if (isset($errors['phone'])): ?><p class="error-text"><?= e($errors['phone']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="address">Adresse postale</label>
        <input type="text" id="address" name="address" value="<?= old($old, 'address') ?>">
    </div>
    <div class="field">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
        <small class="muted">10 caractères min., 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
        <?php if (isset($errors['password'])): ?><p class="error-text"><?= e($errors['password']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="password_confirm">Confirmer le mot de passe</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <?php if (isset($errors['password_confirm'])): ?><p class="error-text"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
    </div>
    <div class="field checkbox-field">
        <input type="checkbox" id="rgpd" name="rgpd" value="1" required>
        <label for="rgpd">J'accepte que mes données soient utilisées pour gérer mon compte et mes
            réservations, conformément à la politique de confidentialité (RGPD).</label>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>
    <p class="muted" style="margin-top:14px">Déjà inscrit ? <a href="/connexion">Se connecter</a></p>
</form>
