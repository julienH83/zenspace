<?php /** @var string $token @var array $errors */ $errors = $errors ?? []; ?>
<form class="form-card" action="/reinitialiser/<?= e($token) ?>" method="post">
    <h1>Nouveau mot de passe</h1>
    <?= csrf() ?>
    <div class="field">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" id="password" name="password" required autofocus
               <?php if (isset($errors['password'])): ?>aria-invalid="true" aria-describedby="password-err password-help"<?php else: ?>aria-describedby="password-help"<?php endif; ?>>
        <small id="password-help" class="muted">10 caractères min., 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
        <?php if (isset($errors['password'])): ?><p id="password-err" class="field-error" role="alert"><?= e($errors['password']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="password_confirm">Confirmer</label>
        <input type="password" id="password_confirm" name="password_confirm" required
               <?php if (isset($errors['password_confirm'])): ?>aria-invalid="true" aria-describedby="password_confirm-err"<?php endif; ?>>
        <?php if (isset($errors['password_confirm'])): ?><p id="password_confirm-err" class="field-error" role="alert"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Réinitialiser</button>
</form>
