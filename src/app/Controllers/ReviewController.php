<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Repositories\BookingRepository;
use App\Repositories\ReviewRepository;

/**
 * Dépôt d'un avis par un client, après une prestation terminée.
 */
final class ReviewController extends Controller
{
    public function create(): void
    {
        $user = $this->requireLogin();
        Csrf::validate();

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $rating    = (int) ($_POST['rating'] ?? 0);
        $comment   = trim($_POST['comment'] ?? '');

        $bookings = new BookingRepository();
        $booking  = $bookings->findById($bookingId);

        // Contrôles : la réservation appartient au client, est terminée, sans avis existant.
        if ($booking === null || (int) $booking['user_id'] !== (int) $user['id']) {
            Flash::error('Réservation introuvable.');
            $this->redirect('/mon-compte');
            return;
        }
        if ($booking['status'] !== 'completed') {
            Flash::error('Vous ne pouvez laisser un avis qu\'après une prestation terminée.');
            $this->redirect('/reservation/' . $bookingId);
            return;
        }
        if ($rating < 1 || $rating > 5) {
            Flash::error('La note doit être comprise entre 1 et 5.');
            $this->redirect('/reservation/' . $bookingId);
            return;
        }

        $reviews = new ReviewRepository();
        if ($reviews->existsForBooking($bookingId)) {
            Flash::error('Un avis a déjà été déposé pour cette réservation.');
            $this->redirect('/reservation/' . $bookingId);
            return;
        }

        $reviews->create([
            'user_id'    => $user['id'],
            'service_id' => $booking['service_id'],
            'booking_id' => $bookingId,
            'rating'     => $rating,
            'comment'    => $comment,
        ]);

        Flash::success('Merci ! Votre avis sera publié après validation par notre équipe.');
        $this->redirect('/reservation/' . $bookingId);
    }
}
