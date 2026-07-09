<?php /** @var array $errors @var array $old */ $errors = $errors ?? []; $old = $old ?? []; ?>
<form class="form-card form-wide" action="/contact" method="post">
    <h1>Nous contacter</h1>
    <p class="muted">Une question ? Écrivez-nous, nous vous répondrons rapidement.</p>
    <?= csrf() ?>
    <div class="field">
        <label for="name">Nom</label>
        <input type="text" id="name" name="name" value="<?= old($old, 'name') ?>" required
               <?php if (isset($errors['name'])): ?>aria-invalid="true" aria-describedby="name-err"<?php endif; ?>>
        <?php if (isset($errors['name'])): ?><p id="name-err" class="field-error" role="alert"><?= e($errors['name']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old($old, 'email') ?>" required
               <?php if (isset($errors['email'])): ?>aria-invalid="true" aria-describedby="email-err"<?php endif; ?>>
        <?php if (isset($errors['email'])): ?><p id="email-err" class="field-error" role="alert"><?= e($errors['email']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="subject">Sujet</label>
        <input type="text" id="subject" name="subject" value="<?= old($old, 'subject') ?>" required
               <?php if (isset($errors['subject'])): ?>aria-invalid="true" aria-describedby="subject-err"<?php endif; ?>>
        <?php if (isset($errors['subject'])): ?><p id="subject-err" class="field-error" role="alert"><?= e($errors['subject']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="message">Message</label>
        <textarea id="message" name="message" required
                  <?php if (isset($errors['message'])): ?>aria-invalid="true" aria-describedby="message-err"<?php endif; ?>><?= old($old, 'message') ?></textarea>
        <?php if (isset($errors['message'])): ?><p id="message-err" class="field-error" role="alert"><?= e($errors['message']) ?></p><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Envoyer</button>
</form>
