<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Mongo;
use App\Repositories\BookingRepository;
use App\Repositories\LoyaltyRepository;

/**
 * Gestion des réservations par les employés/admins : liste filtrable + suivi de
 * statut. Quand une réservation passe à « terminée », on alimente la base NoSQL
 * (statistiques de chiffre d'affaires).
 */
final class AdminBookingController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

    public function index(): void
    {
        $this->requireRole(['employe', 'admin']);
        $status = $_GET['status'] ?? '';
        $repo = new BookingRepository();

        $this->render('admin/bookings/index', [
            'title'         => 'Gérer les réservations',
            'bookings'      => $repo->findAll($status),
            'currentStatus' => $status,
            'statuses'      => self::STATUSES,
            'layout_admin'  => true,
        ]);
    }

    public function updateStatus(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();

        $status = $_POST['status'] ?? '';
        if (!in_array($status, self::STATUSES, true)) {
            Flash::error('Statut invalide.');
            $this->redirect('/admin/reservations');
            return;
        }

        $repo = new BookingRepository();
        $booking = $repo->findById((int) $id);
        if ($booking === null) {
            $this->notFound('Réservation introuvable.');
        }

        $previousStatus = $booking['status'];
        $repo->updateStatus((int) $id, $status);

        // --- Effets métier d'une VRAIE transition vers « terminée » ---
        // Garde `previousStatus !== 'completed'` : repasser une réservation déjà
        // terminée à « terminée » ne réécrit rien (ni CA en double, ni points en double).
        if ($status === 'completed' && $previousStatus !== 'completed') {
            // 1) Statistiques de chiffre d'affaires (base NoSQL, upsert idempotent).
            Mongo::upsert('revenue', ['booking_id' => (int) $booking['id']], [
                'booking_id'    => (int) $booking['id'],
                'service_id'    => (int) $booking['service_id'],
                'service_title' => $booking['service_title'],
                'amount'        => (float) $booking['total_price'],
                'date'          => date('Y-m-d'),
            ]);

            // 2) Programme de fidélité : 1 point par euro dépensé, crédité au client.
            //    Le grand-livre (loyalty_ledger) conserve chaque mouvement avec son
            //    motif et la réservation d'origine (traçabilité + solde recalculable).
            $points = (int) round((float) $booking['total_price']);
            if ($points > 0) {
                (new LoyaltyRepository())->award(
                    (int) $booking['user_id'],
                    $points,
                    'Réservation terminée',
                    (int) $booking['id']
                );
            }
        }

        Flash::success('Statut mis à jour.');

        // Conserve le filtre courant transmis par le formulaire (champ caché),
        // au lieu de lire $_GET sur une requête POST (qui n'en a pas).
        $filter = $_POST['current_filter'] ?? '';
        $this->redirect('/admin/reservations' . ($filter !== '' ? '?status=' . urlencode($filter) : ''));
    }
}
