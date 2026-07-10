<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Seo;
use App\Repositories\ServiceRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ArticleRepository;

/**
 * Page d'accueil : présentation de l'institut, prestations phares, avis validés.
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        $services = new ServiceRepository();
        $reviews  = new ReviewRepository();
        $articles = new ArticleRepository();

        $this->render('home/index', [
            'title'    => 'Accueil',
            'services' => array_slice($services->findActive(), 0, 3),
            'reviews'  => $reviews->findValidated(3),
            'articles' => $articles->findLatestPublished(3),
            'seo'      => [
                'title'       => 'ZenSpace — Institut de bien-être à Bordeaux',
                'description' => 'Massages, soins du visage, spa et méditation guidée. Réservez votre parenthèse de sérénité en ligne, en quelques clics.',
                'jsonld'      => Seo::organization(),
            ],
        ]);
    }
}
