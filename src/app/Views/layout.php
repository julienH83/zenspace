<?php
/**
 * Gabarit principal : en-tête + navigation + messages flash + pied de page.
 * La variable $content (HTML de la vue) est injectée au milieu.
 *
 * @var string $content
 * @var string $title
 */
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\View;

$user      = Auth::user();
$isAdminUi = $layout_admin ?? false;
$appName   = $_ENV['APP_NAME'] ?? 'ZenSpace';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title ?? '') ?> — <?= View::e($appName) ?></title>
    <meta name="description" content="Institut de bien-être : massages, soins du visage, spa. Réservez en ligne.">
    <!-- Polices Google : Cormorant Garamond (titres, esprit spa premium) + Nunito Sans (texte) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu</a>

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="brand"><?= View::e($appName) ?></a>

        <nav class="main-nav" aria-label="Navigation principale">
            <a href="/">Accueil</a>
            <a href="/prestations">Prestations</a>
            <a href="/contact">Contact</a>
            <?php if ($user && in_array($user['role'], ['employe', 'admin'], true)): ?>
                <a href="/admin">Espace gestion</a>
            <?php endif; ?>
        </nav>

        <div class="auth-nav">
            <?php if ($user): ?>
                <?php if ($user['role'] === 'client'): ?>
                    <a href="/mon-compte">Mon compte</a>
                <?php endif; ?>
                <span class="user-hello">Bonjour, <?= View::e($user['first_name']) ?></span>
                <form action="/deconnexion" method="post" class="inline-form">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-ghost btn-sm">Déconnexion</button>
                </form>
            <?php else: ?>
                <a href="/connexion" class="btn btn-ghost btn-sm">Connexion</a>
                <a href="/inscription" class="btn btn-primary btn-sm">Créer un compte</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php $messages = Flash::pull(); ?>
<?php if ($messages): ?>
    <div class="container flash-zone">
        <?php foreach ($messages as $m): ?>
            <div class="flash flash-<?= View::e($m['type']) ?>"><?= View::e($m['message']) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main" class="container main-content">
    <?php if ($isAdminUi): ?>
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <h2 class="sidebar-title">Gestion</h2>
                <nav aria-label="Navigation gestion">
                    <a href="/admin">Tableau de bord</a>
                    <a href="/admin/prestations">Prestations</a>
                    <a href="/admin/reservations">Réservations</a>
                    <a href="/admin/avis">Avis</a>
                    <?php if ($user && $user['role'] === 'admin'): ?>
                        <a href="/admin/employes">Employés</a>
                        <a href="/admin/statistiques">Statistiques</a>
                    <?php endif; ?>
                </nav>
            </aside>
            <section class="admin-main">
                <?= $content ?>
            </section>
        </div>
    <?php else: ?>
        <?= $content ?>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <strong><?= View::e($appName) ?></strong>
            <p>Institut de bien-être — 1 rue du Spa, 33000 Bordeaux</p>
        </div>
        <div>
            <h3>Horaires</h3>
            <ul class="hours">
                <li>Lundi – Vendredi : 9h – 18h</li>
                <li>Samedi : 9h – 13h</li>
                <li>Dimanche : fermé</li>
            </ul>
        </div>
        <div>
            <h3>Informations</h3>
            <a href="/contact">Nous contacter</a>
        </div>
    </div>
    <div class="footer-bottom">
        <small>© <?= date('Y') ?> <?= View::e($appName) ?> — Projet DWWM.</small>
    </div>
</footer>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
