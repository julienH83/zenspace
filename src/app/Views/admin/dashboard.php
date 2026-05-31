<?php
/** @var array $user @var int $pendingBookings @var int $pendingReviews @var int $totalServices */
?>
<div class="page-head"><h1>Tableau de bord</h1></div>
<p class="muted">Bonjour <?= e($user['first_name']) ?>, voici un aperçu de l'activité.</p>

<div class="stat-cards">
    <div class="stat-card">
        <div class="big"><?= (int) $pendingBookings ?></div>
        <div class="muted">Réservations en attente</div>
        <a href="/admin/reservations?status=pending">Voir →</a>
    </div>
    <div class="stat-card">
        <div class="big"><?= (int) $pendingReviews ?></div>
        <div class="muted">Avis à modérer</div>
        <a href="/admin/avis">Voir →</a>
    </div>
    <div class="stat-card">
        <div class="big"><?= (int) $totalServices ?></div>
        <div class="muted">Prestations au catalogue</div>
        <a href="/admin/prestations">Gérer →</a>
    </div>
</div>
