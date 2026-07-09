<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Repositories\BookingRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;

/**
 * Tableau de bord de l'espace employé / administrateur.
 * Accessible aux rôles « employe » et « admin ».
 */
final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireRole(['employe', 'admin']);

        $bookings = new BookingRepository();
        $reviews  = new ReviewRepository();
        $services = new ServiceRepository();

        $this->render('admin/dashboard', [
            'title'           => 'Espace de gestion',
            'user'            => $user,
            'pendingBookings' => $bookings->countByStatus('pending'),
            'pendingReviews'  => $reviews->countPending(),
            'totalServices'   => $services->countActive(),
            'layout_admin'    => true,
        ]);
    }
}
