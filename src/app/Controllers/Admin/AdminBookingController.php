<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Mongo;
use App\Repositories\BookingRepository;

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

        // --- Alimentation de la base NoSQL pour les statistiques ---
        // Seulement lors d'une VRAIE transition vers « terminée », et via upsert
        // (clé booking_id) : repasser à « completed » ne double plus le CA.
        if ($status === 'completed' && $previousStatus !== 'completed') {
            Mongo::upsert('revenue', ['booking_id' => (int) $booking['id']], [
                'booking_id'    => (int) $booking['id'],
                'service_id'    => (int) $booking['service_id'],
                'service_title' => $booking['service_title'],
                'amount'        => (float) $booking['total_price'],
                'date'          => date('Y-m-d'),
            ]);
        }

        Flash::success('Statut mis à jour.');

        // Conserve le filtre courant transmis par le formulaire (champ caché),
        // au lieu de lire $_GET sur une requête POST (qui n'en a pas).
        $filter = $_POST['current_filter'] ?? '';
        $this->redirect('/admin/reservations' . ($filter !== '' ? '?status=' . urlencode($filter) : ''));
    }
}
