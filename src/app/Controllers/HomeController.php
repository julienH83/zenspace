<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ServiceRepository;
use App\Repositories\ReviewRepository;

/**
 * Page d'accueil : présentation de l'institut, prestations phares, avis validés.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $services = new ServiceRepository();
        $reviews  = new ReviewRepository();

        $this->render('home/index', [
            'title'    => 'Accueil',
            'services' => array_slice($services->findActive(), 0, 3),
            'reviews'  => $reviews->findValidated(3),
        ]);
    }
}
