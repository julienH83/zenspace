<?php /** @var string $token @var array $errors */ $errors = $errors ?? []; ?>
<form class="form-card" action="/reinitialiser/<?= e($token) ?>" method="post">
    <h1>Nouveau mot de passe</h1>
    <?= csrf() ?>
    <div class="field">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" id="password" name="password" required autofocus>
        <small class="muted">10 caractères min., 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
        <?php if (isset($errors['password'])): ?><p class="error-text"><?= e($errors['password']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="password_confirm">Confirmer</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <?php if (isset($errors['password_confirm'])): ?><p class="error-text"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Réinitialiser</button>
</form>
