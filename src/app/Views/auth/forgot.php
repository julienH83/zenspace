<form class="form-card" action="/mot-de-passe-oublie" method="post">
    <h1>Mot de passe oublié</h1>
    <p class="muted">Saisissez votre e-mail : nous vous enverrons un lien de réinitialisation.</p>
    <?= csrf() ?>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Envoyer le lien</button>
    <p class="muted" style="margin-top:14px"><a href="/connexion">Retour à la connexion</a></p>
</form>
