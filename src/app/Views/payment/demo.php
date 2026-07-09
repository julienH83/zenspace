<?php /** @var int $booking_id */ ?>
<article class="form-card form-wide">
    <div class="page-head">
        <h1>Paiement en ligne</h1>
        <span class="badge badge-pending">Démonstration</span>
    </div>

    <p class="muted">
        Cette page est un <strong>scaffold</strong> : la clé <code>STRIPE_SECRET</code>
        n'est pas configurée, le paiement réel est donc désactivé. Voici comment
        l'intégration fonctionnerait pour la réservation
        <strong>#<?= (int) $booking_id ?></strong>.
    </p>

    <h2>Principe — Stripe Checkout</h2>
    <ol>
        <li>Le serveur crée une <em>session de paiement</em> Stripe avec le montant de la réservation.</li>
        <li>Le client est redirigé vers la page de paiement sécurisée hébergée par Stripe.</li>
        <li>Après paiement, Stripe redirige vers une page de succès et notifie le serveur via un <em>webhook</em>.</li>
        <li>Le webhook confirme la réservation et peut créditer des points de fidélité.</li>
    </ol>

    <h2>Extrait du code serveur (PHP)</h2>
    <pre class="code-block"><code><?= e(
'\Stripe\Stripe::setApiKey(env(\'STRIPE_SECRET\'));

$session = \Stripe\Checkout\Session::create([
    \'mode\'        => \'payment\',
    \'line_items\'  => [[
        \'price_data\' => [
            \'currency\'     => \'eur\',
            \'unit_amount\'  => $montantEnCentimes,
            \'product_data\' => [\'name\' => $titrePrestation],
        ],
        \'quantity\' => 1,
    ]],
    \'metadata\'    => [\'booking_id\' => $bookingId],
    \'success_url\' => $appUrl . \'/reservation/\' . $bookingId . \'?paiement=ok\',
    \'cancel_url\'  => $appUrl . \'/reservation/\' . $bookingId . \'?paiement=annule\',
]);

header(\'Location: \' . $session->url); // redirection vers Stripe'
) ?></code></pre>

    <p class="muted">
        Pour activer : <code>composer require stripe/stripe-php</code> puis renseigner
        <code>STRIPE_SECRET</code> et <code>STRIPE_WEBHOOK_SECRET</code> dans le fichier
        <code>.env</code>.
    </p>

    <p style="margin-top:18px"><a href="/reservation/<?= (int) $booking_id ?>">← Retour à la réservation</a></p>
</article>
