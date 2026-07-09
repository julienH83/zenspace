<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BookingRepository;

/**
 * QR code de réservation — SCAFFOLD.
 *
 * Génère une charge utile SIGNÉE (HMAC-SHA256) pour une réservation, que le
 * client peut présenter à l'accueil. Le QR est dessiné CÔTÉ CLIENT via la
 * librairie « qrcode » chargée depuis unpkg (autorisée par la CSP). La
 * signature permet à l'institut de vérifier l'authenticité du code sans base
 * de données partagée.
 */
final class QrController extends Controller
{
    public function booking(string $id): void
    {
        $user      = $this->requireLogin();
        $bookingId = (int) $id;

        // Vérification d'appartenance : un client ne peut générer le QR que pour
        // SES propres réservations. Les employés/admin peuvent voir toutes.
        $booking = (new BookingRepository())->findById($bookingId);
        $isStaff = in_array($user['role'], ['employe', 'admin'], true);
        if ($booking === null || (!$isStaff && (int) $booking['user_id'] !== (int) $user['id'])) {
            $this->notFound('Réservation introuvable.');
        }

        // Charge utile signée : la signature HMAC lie l'identifiant de réservation
        // au secret applicatif. APP_SECRET doit être défini (repli neutre sinon
        // pour ne jamais lever d'erreur en démonstration).
        $secret    = env('APP_SECRET', '') ?? '';
        $signature = hash_hmac('sha256', (string) $bookingId, $secret);
        // Format compact « id.signature » : facile à vérifier côté serveur.
        $payload   = $bookingId . '.' . $signature;

        $this->render('qr/show', [
            'title'      => 'QR de réservation',
            'booking'    => $booking,
            'payload'    => $payload,
            'booking_id' => $bookingId,
        ]);
    }
}
