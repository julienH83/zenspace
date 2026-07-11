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
use App\Core\Seo;
use App\Core\View;

$user      = Auth::user();
$isAdminUi = $layout_admin ?? false;
$appName   = $_ENV['APP_NAME'] ?? 'ZenSpace';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$navCurrent  = static fn(string $path): string => $path === $currentPath ? ' aria-current="page"' : '';
$navSection  = static fn(string $path): string => ($path === $currentPath || str_starts_with((string) $currentPath, $path . '/'))
    ? ' aria-current="page"' : '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Seo::tags($seo ?? []) ?>
    <!-- Police : Inter (une seule famille, du texte aux titres) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php $cssV = @filemtime(($_SERVER['DOCUMENT_ROOT'] ?? '') . '/assets/css/app.css'); ?>
    <link rel="stylesheet" href="/assets/css/app.css<?= $cssV ? '?v=' . $cssV : '' ?>">
</head>
<body>
<a class="skip-link" href="#main">Aller au contenu</a>

<!-- Barre utilitaire : coordonnées et horaires, comme sur un site d'entreprise. -->
<div class="topbar">
    <div class="container topbar-inner">
        <a href="tel:+33556000000" class="topbar-item">☎ 05 56 00 00 00</a>
        <span class="topbar-item">Lun–Ven 9h–18h · Sam 9h–13h</span>
        <span class="topbar-item topbar-addr">1 rue du Spa, 33000 Bordeaux</span>
    </div>
</div>

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="brand"><span class="brand-mark" aria-hidden="true">Z</span><?= View::e($appName) ?></a>

        <?php // Bloc replié en menu hamburger sur mobile (voir app.js / .nav-collapse). ?>
        <div class="nav-collapse" id="primary-nav">
            <nav class="main-nav" aria-label="Navigation principale">
                <a href="/"<?= $navCurrent('/') ?>>Accueil</a>
                <a href="/prestations"<?= $navCurrent('/prestations') ?>>Prestations</a>
                <a href="/contact"<?= $navCurrent('/contact') ?>>Contact</a>
                <?php if ($user && in_array($user['role'], ['employe', 'admin'], true)): ?>
                    <a href="/admin"<?= $navCurrent('/admin') ?>>Espace gestion</a>
                <?php endif; ?>
            </nav>
            <div class="auth-links">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'client'): ?>
                        <a href="/mon-compte" class="nav-link-plain">Mon compte</a>
                    <?php endif; ?>
                    <form action="/deconnexion" method="post" class="inline-form">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-ghost btn-sm">Déconnexion</button>
                    </form>
                <?php else: ?>
                    <a href="/connexion" class="nav-link-plain">Connexion</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="header-actions">
            <a href="/prestations" class="btn btn-primary btn-sm">Réserver</a>
            <button type="button" class="nav-toggle" id="nav-toggle"
                    aria-controls="primary-nav" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="nav-toggle-box" aria-hidden="true"><span class="nav-toggle-bar"></span></span>
            </button>
        </div>
    </div>
</header>

<?php $messages = Flash::pull(); ?>
<?php if ($messages): ?>
    <div class="container flash-zone">
        <?php // Messages non bloquants (succès / info) : annoncés poliment. ?>
        <div class="flash-zone" role="status" aria-live="polite" aria-atomic="true">
            <?php foreach ($messages as $m): ?>
                <?php if (($m['type'] ?? '') !== 'error'): ?>
                    <div class="flash flash-<?= View::e($m['type']) ?>"><?= View::e($m['message']) ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php // Erreurs : annoncées immédiatement (role="alert"). ?>
        <?php foreach ($messages as $m): ?>
            <?php if (($m['type'] ?? '') === 'error'): ?>
                <div class="flash flash-error" role="alert"><?= View::e($m['message']) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<main id="main" class="container main-content">
    <?php if ($isAdminUi): ?>
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <h2 class="sidebar-title">Gestion</h2>
                <nav aria-label="Navigation gestion">
                    <a href="/admin"<?= $navCurrent('/admin') ?>>Tableau de bord</a>
                    <a href="/admin/prestations"<?= $navSection('/admin/prestations') ?>>Prestations</a>
                    <a href="/admin/reservations"<?= $navSection('/admin/reservations') ?>>Réservations</a>
                    <a href="/admin/avis"<?= $navSection('/admin/avis') ?>>Avis</a>
                    <a href="/admin/magazine"<?= $navSection('/admin/magazine') ?>>Magazine</a>
                    <?php if ($user && $user['role'] === 'admin'): ?>
                        <a href="/admin/employes"<?= $navSection('/admin/employes') ?>>Employés</a>
                        <a href="/admin/statistiques"<?= $navSection('/admin/statistiques') ?>>Statistiques</a>
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
        <div class="footer-brand">
            <span class="brand brand-footer"><span class="brand-mark" aria-hidden="true">Z</span><?= View::e($appName) ?></span>
            <p>Institut de bien-être à Bordeaux. Massages, soins du visage, spa &amp; hammam, sur réservation.</p>
            <a href="/prestations" class="btn btn-primary btn-sm">Prendre rendez-vous</a>
        </div>
        <div>
            <h2>Coordonnées</h2>
            <ul class="footer-links">
                <li>1 rue du Spa, 33000 Bordeaux</li>
                <li><a href="tel:+33556000000">05 56 00 00 00</a></li>
                <li><a href="/contact">Formulaire de contact</a></li>
            </ul>
        </div>
        <div>
            <h2>Horaires</h2>
            <ul class="hours">
                <li>Lundi – Vendredi : 9h – 18h</li>
                <li>Samedi : 9h – 13h</li>
                <li>Dimanche : fermé</li>
            </ul>
        </div>
        <nav aria-label="Liens de bas de page">
            <h2>Explorer</h2>
            <ul class="footer-links">
                <li><a href="/prestations">Nos prestations</a></li>
                <li><a href="/magazine">Magazine bien-être</a></li>
                <li><a href="/contact">Nous contacter</a></li>
            </ul>
        </nav>
    </div>
    <div class="footer-bottom">
        <small>© <?= date('Y') ?> <?= View::e($appName) ?> — Tous droits réservés. Projet DWWM.</small>
    </div>
</footer>

<?php $jsV = @filemtime(($_SERVER['DOCUMENT_ROOT'] ?? '') . '/assets/js/app.js'); ?>
<script src="/assets/js/app.js<?= $jsV ? '?v=' . $jsV : '' ?>" defer></script>
</body>
</html>
