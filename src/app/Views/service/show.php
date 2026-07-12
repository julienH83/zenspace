<?php
/**
 * @var array      $service Prestation affichée.
 * @var array      $reviews Avis validés : rating, comment, created_at, first_name.
 * @var array      $rating  Résumé : ['avg' => ?float, 'count' => int].
 */
use App\Core\Auth;

// Valeurs par défaut robustes si le contrôleur ne fournit pas tout.
$reviews = $reviews ?? [];
$rating  = $rating  ?? ['avg' => null, 'count' => 0];
$ratingCount = (int) ($rating['count'] ?? 0);
$ratingAvg   = $rating['avg'] !== null ? (float) $rating['avg'] : null;
// Note moyenne arrondie pour l'affichage en étoiles pleines / vides.
$avgRounded  = $ratingAvg !== null ? (int) round($ratingAvg) : 0;

// Catégorie : détermine la scène 3D ET le déroulé du soin affiché.
$catSlug = $service['category_slug'] ?? '';

// « Comment ça se passe » : déroulé propre à chaque type de soin.
$deroules = [
    'massage' => [
        'Accueil & échange sur vos zones de tension et vos préférences (pression, huile).',
        'Installation confortable sur la table chauffée, en toute intimité.',
        'Massage du corps aux huiles tièdes, selon le protocole choisi.',
        'Temps de repos et verre de tisane pour prolonger la détente.',
    ],
    'soin-visage' => [
        'Diagnostic de peau et démaquillage en douceur.',
        'Gommage puis extraction selon les besoins de votre peau.',
        'Application d\'un masque adapté + modelage du visage.',
        'Sérum et crème de protection ; conseils de routine personnalisés.',
    ],
    'spa' => [
        'Accès au hammam pour ouvrir les pores et détendre les muscles.',
        'Passage au sauna puis douche fraîche tonifiante.',
        'Bassin de détente et espace de relaxation.',
        'Tisanerie et cocon de repos pour clôturer le moment.',
    ],
];
$deroule = $deroules[$catSlug] ?? null;

$availability = $availability ?? [];
$serviceId    = (int) $service['id'];
$loggedIn     = Auth::check();
?>
<article class="form-card form-wide">
    <?php if (!empty($service['image'])): ?>
        <img class="detail-banner" src="/assets/images/<?= e($service['image']) ?>" alt="<?= e($service['title']) ?>">
    <?php endif; ?>
    <span class="tag"><?= e($service['category_label']) ?></span>
    <h1><?= e($service['title']) ?></h1>
    <p class="muted"><?= (int) $service['duration_min'] ?> minutes — <span class="price"><?= price($service['price']) ?></span></p>

    <h2>Description</h2>
    <p><?= nl2br(e($service['description'])) ?></p>

    <?php if ($deroule !== null): ?>
        <h2>Comment se déroule le soin&nbsp;?</h2>
        <ol class="deroule">
            <?php foreach ($deroule as $etape): ?>
                <li><?= e($etape) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <div class="actions">
        <?php if ($loggedIn): ?>
            <a class="btn btn-primary" href="/reserver/<?= $serviceId ?>">Réserver cette prestation</a>
        <?php else: ?>
            <a class="btn btn-primary" href="/connexion">Se connecter pour réserver</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="/prestations">Retour au catalogue</a>
    </div>

    <!-- =====================================================================
         Planning de disponibilités : pour chaque prochain jour d'ouverture,
         les créneaux libres sont CLIQUABLES et pré-remplissent la réservation.
         100 % côté serveur : fonctionne sans JavaScript. Les créneaux pris ou
         passés sont désactivés.
         ===================================================================== -->
    <section class="availability" aria-labelledby="dispo-title">
        <h2 id="dispo-title">Prochaines disponibilités</h2>
        <p class="muted">
            <?php if ($loggedIn): ?>
                Cliquez sur un créneau libre pour le réserver immédiatement.
            <?php else: ?>
                Connectez-vous pour réserver l'un des créneaux libres ci-dessous.
            <?php endif; ?>
        </p>

        <?php if (empty($availability)): ?>
            <p class="muted">Aucune disponibilité à afficher pour le moment.</p>
        <?php else: ?>
            <div class="dispo-grid">
                <?php foreach ($availability as $day): ?>
                    <div class="dispo-day">
                        <h3><?= e($day['label']) ?></h3>
                        <ul>
                            <?php foreach ($day['slots'] as $s): ?>
                                <li>
                                    <?php if ($s['taken'] || $s['past']): ?>
                                        <span class="slot slot--off"
                                              aria-label="<?= e($s['time']) ?> — <?= $s['taken'] ? 'déjà réservé' : 'créneau passé' ?>">
                                            <?= e($s['time']) ?>
                                        </span>
                                    <?php elseif ($loggedIn): ?>
                                        <a class="slot slot--free"
                                           href="/reserver/<?= $serviceId ?>?date=<?= e($day['date']) ?>&amp;slot=<?= e($s['time']) ?>"
                                           aria-label="Réserver le <?= e($day['label']) ?> à <?= e($s['time']) ?>">
                                            <?= e($s['time']) ?>
                                        </a>
                                    <?php else: ?>
                                        <a class="slot slot--free" href="/connexion"
                                           aria-label="Se connecter pour réserver le <?= e($day['label']) ?> à <?= e($s['time']) ?>">
                                            <?= e($s['time']) ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="dispo-legend">
                <span><i class="dispo-dot dispo-dot--free" aria-hidden="true"></i> Libre</span>
                <span><i class="dispo-dot dispo-dot--off" aria-hidden="true"></i> Réservé / passé</span>
            </p>
        <?php endif; ?>
    </section>
</article>

<!-- =====================================================================
     Avis clients : note moyenne en étoiles + nombre d'avis, puis la liste.
     Toutes les données serveur sont échappées avec e().
     ===================================================================== -->
<section class="section reviews-section" aria-labelledby="reviews-title">
    <h2 id="reviews-title">Avis clients</h2>

    <?php if ($ratingCount > 0): ?>
        <p class="rating-summary">
            <span class="stars" aria-hidden="true"><?= str_repeat('★', $avgRounded) . str_repeat('☆', 5 - $avgRounded) ?></span>
            <strong><?= e(number_format($ratingAvg, 1, ',', ' ')) ?></strong> / 5
            <span class="muted">(<?= $ratingCount ?> avis)</span>
        </p>

        <div class="grid">
            <?php foreach ($reviews as $r): ?>
                <?php $stars = max(0, min(5, (int) $r['rating'])); ?>
                <div class="review">
                    <p class="stars" aria-label="Note : <?= $stars ?> sur 5"><?= str_repeat('★', $stars) . str_repeat('☆', 5 - $stars) ?></p>
                    <?php if (!empty($r['comment'])): ?>
                        <p><?= nl2br(e($r['comment'])) ?></p>
                    <?php endif; ?>
                    <p class="muted">
                        <strong><?= e($r['first_name']) ?></strong>
                        <?php if (!empty($r['created_at'])): ?>
                            — <?= e(date('d/m/Y', strtotime((string) $r['created_at']))) ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="muted">Aucun avis pour le moment.</p>
    <?php endif; ?>
</section>
