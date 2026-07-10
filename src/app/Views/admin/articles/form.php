<?php
/** @var array $article @var array $errors */
$article = $article ?? [];
$errors  = $errors ?? [];
$isEdit  = !empty($article['id']);
$action  = $isEdit ? '/admin/magazine/' . (int) $article['id'] . '/editer' : '/admin/magazine/nouveau';
$isPublished = !empty($article['published_at']);
?>
<div class="page-head"><h1><?= $isEdit ? 'Modifier l\'article' : 'Nouvel article' ?></h1></div>

<form class="form-card form-wide" action="<?= $action ?>" method="post">
    <?php if ($errors): ?>
        <div class="errors-box"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?= csrf() ?>

    <div class="field">
        <label for="title">Titre</label>
        <input type="text" id="title" name="title" value="<?= e($article['title'] ?? '') ?>" required>
    </div>

    <div class="field">
        <label for="excerpt">Chapô (résumé court, optionnel)</label>
        <input type="text" id="excerpt" name="excerpt" maxlength="300" value="<?= e($article['excerpt'] ?? '') ?>">
    </div>

    <div class="field">
        <label for="cover_image">Image de couverture (nom de fichier dans /assets/images, optionnel)</label>
        <input type="text" id="cover_image" name="cover_image" value="<?= e($article['cover_image'] ?? '') ?>"
               placeholder="ex. spa-hammam.jpg">
    </div>

    <div class="field">
        <label for="body">Contenu</label>
        <textarea id="body" name="body" rows="12" required><?= e($article['body'] ?? '') ?></textarea>
    </div>

    <div class="field checkbox-field">
        <input type="checkbox" id="published" name="published" value="1" <?= $isPublished ? 'checked' : '' ?>>
        <label for="published">Publier (visible dans le magazine ; décoché = brouillon)</label>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a class="btn btn-ghost" href="/admin/magazine">Annuler</a>
    </div>
</form>
