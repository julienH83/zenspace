<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Paiement en ligne — SCAFFOLD Stripe Checkout.
 *
 * Tant que la clé STRIPE_SECRET n'est pas renseignée dans .env (et que la
 * librairie « stripe/stripe-php » n'est pas installée via Composer), la page
 * affiche une démonstration documentée plutôt que de provoquer une erreur.
 * Le code réel d'intégration figure en commentaire détaillé ci-dessous.
 */
final class PaymentController extends Controller
{
    /**
     * Démarre une session de paiement pour une réservation.
     * En l'absence de configuration Stripe, rend la page de démonstration.
     */
    public function checkout(string $id): void
    {
        $user      = $this->requireLogin();
        $bookingId = (int) $id;

        $stripeSecret = env('STRIPE_SECRET');

        if ($stripeSecret === null || $stripeSecret === '') {
            // Mode DÉMO : aucune dépendance externe, aucune erreur possible.
            $this->render('payment/demo', [
                'title'      => 'Paiement (démonstration)',
                'booking_id' => $bookingId,
            ]);
            return;
        }

        /*
         * INTÉGRATION RÉELLE (à activer une fois `composer require stripe/stripe-php`
         * effectué et STRIPE_SECRET renseigné). Laissée en commentaire car la
         * librairie n'est pas présente dans ce projet de démonstration.
         *
         * \Stripe\Stripe::setApiKey($stripeSecret);
         *
         * // On récupérerait ici la réservation pour connaître le montant et le libellé.
         * // $booking = (new \App\Repositories\BookingRepository())->findById($bookingId);
         * // if ($booking === null || (int) $booking['user_id'] !== (int) $user['id']) {
         * //     $this->notFound('Réservation introuvable.');
         * // }
         *
         * $appUrl = rtrim((string) env('APP_URL', ''), '/');
         * $session = \Stripe\Checkout\Session::create([
         *     'mode'        => 'payment',
         *     'line_items'  => [[
         *         'price_data' => [
         *             'currency'     => 'eur',
         *             'unit_amount'  => (int) round(((float) $booking['total_price']) * 100), // en centimes
         *             'product_data' => ['name' => $booking['service_title']],
         *         ],
         *         'quantity' => 1,
         *     ]],
         *     'metadata'    => ['booking_id' => $bookingId],
         *     'success_url' => $appUrl . '/reservation/' . $bookingId . '?paiement=ok',
         *     'cancel_url'  => $appUrl . '/reservation/' . $bookingId . '?paiement=annule',
         * ]);
         *
         * // Redirection du navigateur vers la page de paiement hébergée par Stripe.
         * $this->redirect($session->url);
         */
    }

    /**
     * Webhook Stripe (notification serveur-à-serveur des paiements).
     *
     * Route suggérée : POST /webhooks/stripe (sans CSRF : appel externe signé).
     * Implémentation réelle (commentée — nécessite la librairie Stripe) :
     *
     *   $payload   = file_get_contents('php://input');
     *   $sig       = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
     *   $secret    = env('STRIPE_WEBHOOK_SECRET');
     *   try {
     *       $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
     *   } catch (\Throwable $e) {
     *       $this->json(['error' => 'signature invalide'], 400);
     *       return;
     *   }
     *   if ($event->type === 'checkout.session.completed') {
     *       $bookingId = (int) ($event->data->object->metadata->booking_id ?? 0);
     *       // (new BookingRepository())->updateStatus($bookingId, 'confirmed');
     *       // (new LoyaltyRepository())->award($userId, $points, 'paiement', $bookingId);
     *   }
     *   $this->json(['received' => true]);
     */
    public function webhook(): void
    {
        // Scaffold : on accuse simplement réception pour ne jamais renvoyer d'erreur.
        $this->json(['received' => true]);
    }
}
