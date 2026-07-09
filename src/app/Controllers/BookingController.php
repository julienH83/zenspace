<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Repositories\ServiceRepository;
use App\Repositories\BookingRepository;

/**
 * Réservations côté client : formulaire, création, liste, détail, annulation.
 */
final class BookingController extends Controller
{
    /** Créneaux horaires proposés (institut ouvert 9h–18h). */
    private const SLOTS = ['09:00', '10:30', '12:00', '14:00', '15:30', '17:00'];

    public function form(string $id): void
    {
        $this->requireLogin();
        $service = (new ServiceRepository())->findById((int) $id);
        if ($service === null) {
            $this->notFound('Prestation introuvable.');
        }

        $this->render('booking/form', [
            'title'   => 'Réserver — ' . $service['title'],
            'service' => $service,
            'slots'   => self::SLOTS,
        ]);
    }

    public function create(string $id): void
    {
        $user = $this->requireLogin();
        Csrf::validate();

        $serviceRepo = new ServiceRepository();
        $service = $serviceRepo->findById((int) $id);
        if ($service === null) {
            $this->notFound('Prestation introuvable.');
        }

        $date = $_POST['booking_date'] ?? '';
        $slot = $_POST['time_slot'] ?? '';

        // Validations métier.
        $errors = [];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) < strtotime(date('Y-m-d'))) {
            $errors[] = 'Veuillez choisir une date valide (aujourd\'hui ou plus tard).';
        }
        if (!in_array($slot, self::SLOTS, true)) {
            $errors[] = 'Veuillez choisir un créneau horaire valide.';
        }

        $bookings = new BookingRepository();
        if ($errors === [] && $bookings->isSlotTaken((int) $id, $date, $slot)) {
            $errors[] = 'Ce créneau est déjà réservé. Merci d\'en choisir un autre.';
        }

        if ($errors !== []) {
            $this->render('booking/form', [
                'title'   => 'Réserver — ' . $service['title'],
                'service' => $service,
                'slots'   => self::SLOTS,
                'errors'  => $errors,
            ]);
            return;
        }

        try {
            $bookings->create([
                'user_id'      => $user['id'],
                'service_id'   => (int) $id,
                'booking_date' => $date,
                'time_slot'    => $slot . ':00',
                'total_price'  => $service['price'],
            ]);
        } catch (\PDOException $e) {
            // 23000 = violation de contrainte d'intégrité (créneau déjà pris,
            // y compris course entre la vérification et l'insertion). On affiche
            // un message clair au lieu d'une erreur fatale.
            if ($e->getCode() === '23000') {
                $this->render('booking/form', [
                    'title'   => 'Réserver — ' . $service['title'],
                    'service' => $service,
                    'slots'   => self::SLOTS,
                    'errors'  => ['Ce créneau vient d\'être réservé. Merci d\'en choisir un autre.'],
                ]);
                return;
            }
            throw $e;   // toute autre erreur remonte au handler global
        }

        Flash::success('Votre réservation a bien été enregistrée. Elle est en attente de confirmation.');
        $this->redirect('/mon-compte');
    }

    public function myBookings(): void
    {
        $user = $this->requireLogin();
        $repo = new BookingRepository();
        $this->render('booking/list', [
            'title'    => 'Mes réservations',
            'bookings' => $repo->findByUser((int) $user['id']),
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->requireLogin();
        $repo = new BookingRepository();
        $booking = $repo->findById((int) $id);

        // Un client ne peut voir que SES réservations.
        if ($booking === null || (int) $booking['user_id'] !== (int) $user['id']) {
            $this->notFound('Réservation introuvable.');
        }

        $this->render('booking/show', [
            'title'    => 'Réservation #' . $booking['id'],
            'booking'  => $booking,
            'history'  => $repo->statusHistory((int) $id),
        ]);
    }

    public function cancel(string $id): void
    {
        $user = $this->requireLogin();
        Csrf::validate();

        $repo = new BookingRepository();
        $booking = $repo->findById((int) $id);

        if ($booking === null || (int) $booking['user_id'] !== (int) $user['id']) {
            $this->notFound('Réservation introuvable.');
        }

        // Annulation possible uniquement tant que ce n'est pas confirmé/terminé.
        if ($booking['status'] !== 'pending') {
            Flash::error('Cette réservation ne peut plus être annulée.');
            $this->redirect('/reservation/' . $id);
            return;
        }

        $repo->updateStatus((int) $id, 'cancelled');
        Flash::success('Réservation annulée.');
        $this->redirect('/mon-compte');
    }
}
