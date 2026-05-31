<?php
/** @var array $service @var array $categories @var array $errors */
$service = $service ?? [];
$errors  = $errors ?? [];
$isEdit  = !empty($service['id']);
$action  = $isEdit ? '/admin/prestations/' . (int) $service['id'] . '/editer' : '/admin/prestations/nouvelle';
?>
<div class="page-head"><h1><?= $isEdit ? 'Modifier la prestation' : 'Nouvelle prestation' ?></h1></div>

<form class="form-card form-wide" action="<?= $action ?>" method="post">
    <?php if ($errors): ?>
        <div class="errors-box"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?= csrf() ?>

    <div class="field">
        <label for="title">Titre</label>
        <input type="text" id="title" name="title" value="<?= e($service['title'] ?? '') ?>" required>
    </div>
    <div class="field">
        <label for="category_id">Catégorie</label>
        <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (($service['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                    <?= e($c['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= e($service['description'] ?? '') ?></textarea>
    </div>
    <div class="field">
        <label for="duration_min">Durée (minutes)</label>
        <input type="number" id="duration_min" name="duration_min" min="1" value="<?= e((string)($service['duration_min'] ?? '')) ?>" required>
    </div>
    <div class="field">
        <label for="price">Prix (€)</label>
        <input type="number" id="price" name="price" min="0" step="0.01" value="<?= e((string)($service['price'] ?? '')) ?>" required>
    </div>
    <div class="field checkbox-field">
        <input type="checkbox" id="is_active" name="is_active" value="1" <?= (!$isEdit || (int)($service['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
        <label for="is_active">Prestation visible dans le catalogue</label>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a class="btn btn-ghost" href="/admin/prestations">Annuler</a>
    </div>
</form>
