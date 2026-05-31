<?php /** @var array $errors @var array $old */ $errors = $errors ?? []; $old = $old ?? []; ?>
<div class="page-head"><h1>Nouvel employé</h1></div>

<form class="form-card form-wide" action="/admin/employes/nouveau" method="post">
    <?php if ($errors): ?>
        <div class="errors-box"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?= csrf() ?>

    <div class="field">
        <label for="first_name">Prénom</label>
        <input type="text" id="first_name" name="first_name" value="<?= old($old, 'first_name') ?>" required>
    </div>
    <div class="field">
        <label for="last_name">Nom</label>
        <input type="text" id="last_name" name="last_name" value="<?= old($old, 'last_name') ?>" required>
    </div>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old($old, 'email') ?>" required>
    </div>
    <div class="field">
        <label for="password">Mot de passe initial</label>
        <input type="password" id="password" name="password" required>
        <small class="muted">10 caractères min., 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary">Créer le compte</button>
        <a class="btn btn-ghost" href="/admin/employes">Annuler</a>
    </div>
</form>
