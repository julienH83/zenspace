<?php /** @var string $email */ ?>
<form class="form-card" action="/connexion" method="post">
    <h1>Connexion</h1>
    <?= csrf() ?>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= e($email ?? '') ?>" required autofocus>
    </div>
    <div class="field">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    <p class="muted" style="margin-top:14px">
        <a href="/mot-de-passe-oublie">Mot de passe oublié ?</a><br>
        Pas encore de compte ? <a href="/inscription">Créer un compte</a>
    </p>
</form>
