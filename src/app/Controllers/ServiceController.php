<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ServiceRepository;
use App\Repositories\ReviewRepository;

/**
 * Catalogue des prestations : liste, recherche filtrée (JSON) et page détail.
 */
final class ServiceController extends Controller
{
    public function index(): void
    {
        $repo = new ServiceRepository();
        $this->render('service/index', [
            'title'      => 'Nos prestations',
            'services'   => $repo->findActive(),
            'categories' => $repo->categories(),
        ]);
    }

    /**
     * Point d'API appelé en JavaScript (fetch) pour filtrer SANS recharger la page.
     * Renvoie la liste des prestations correspondant aux filtres, en JSON.
     */
    public function apiList(): void
    {
        $repo = new ServiceRepository();
        $filters = [
            'category'     => $_GET['category']     ?? '',
            'max_price'    => $_GET['max_price']    ?? '',
            'max_duration' => $_GET['max_duration'] ?? '',
        ];
        $this->json(['services' => $repo->search($filters)]);
    }

    public function show(string $slug): void
    {
        $repo = new ServiceRepository();
        $service = $repo->findBySlug($slug);

        if ($service === null || (int) $service['is_active'] === 0) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Prestation introuvable']);
            return;
        }

        $this->render('service/show', [
            'title'   => $service['title'],
            'service' => $service,
        ]);
    }
}
