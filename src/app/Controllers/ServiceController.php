<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Seo;
use App\Repositories\ServiceRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\BookingRepository;

/**
 * Catalogue des prestations : liste, recherche filtrée (JSON) et page détail.
 */
final class ServiceController extends Controller
{
    public function index(): void
    {
        $repo = new ServiceRepository();

        // Filtrage CÔTÉ SERVEUR : la page fonctionne sans JavaScript (le
        // formulaire est un vrai GET). Le JS n'est qu'une amélioration.
        $filters = $this->readFilters();

        $this->render('service/index', [
            'title'      => 'Nos prestations',
            'services'   => $repo->search($filters),
            'categories' => $repo->categories(),
            'filters'    => $filters,
            'seo'        => [
                'title'       => 'Nos prestations bien-être — ' . env('APP_NAME', 'ZenSpace'),
                'description' => 'Massages, soins du visage, spa et rituels. Filtrez par catégorie, prix et durée, puis réservez en ligne.',
            ],
        ]);
    }

    /**
     * Point d'API appelé en JavaScript (fetch) pour filtrer SANS recharger la page.
     * Renvoie la liste des prestations correspondant aux filtres, en JSON.
     */
    public function apiList(): void
    {
        $repo = new ServiceRepository();
        $this->json(['services' => $repo->search($this->readFilters())]);
    }

    public function show(string $slug): void
    {
        $repo = new ServiceRepository();
        $service = $repo->findBySlug($slug);

        if ($service === null || (int) $service['is_active'] === 0) {
            $this->notFound('Prestation introuvable.');
        }

        // Avis validés de la prestation + résumé de note (corrige l'import mort).
        $reviewRepo = new ReviewRepository();
        $reviews = $reviewRepo->findValidatedByService((int) $service['id']);
        $summary = $reviewRepo->ratingSummary((int) $service['id']);

        $this->render('service/show', [
            'title'        => $service['title'],
            'service'      => $service,
            'reviews'      => $reviews,
            'rating'       => $summary,
            'availability' => $this->buildAvailability((int) $service['id']),
            'seo'     => [
                'title'       => $service['title'] . ' — ' . env('APP_NAME', 'ZenSpace'),
                'description' => mb_substr(strip_tags((string) $service['description']), 0, 155),
                'image'       => !empty($service['image'])
                    ? rtrim((string) env('APP_URL', ''), '/') . '/assets/images/' . $service['image']
                    : null,
                'canonical'   => rtrim((string) env('APP_URL', ''), '/') . '/prestation/' . $service['slug'],
                'type'        => 'product',
                'jsonld'      => Seo::service($service, $summary['avg'] ?? null, $summary['count'] ?? 0),
            ],
        ]);
    }

    /** Créneaux horaires de l'institut. */
    private const SLOTS = ['09:00', '10:30', '12:00', '14:00', '15:30', '17:00'];
    /** Le samedi, ouverture le matin uniquement. */
    private const SATURDAY_SLOTS = ['09:00', '10:30', '12:00'];

    /**
     * Construit les disponibilités des 7 prochains jours d'ouverture pour une
     * prestation : pour chaque jour, la liste des créneaux et leur état
     * (libre / réservé / passé). Respecte les horaires (dimanche fermé,
     * samedi matin seulement).
     *
     * @return array<int, array{date:string,label:string,slots:array<int,array{time:string,taken:bool,past:bool}>}>
     */
    private function buildAvailability(int $serviceId): array
    {
        $today = date('Y-m-d');
        $now   = date('H:i');
        $taken = (new BookingRepository())->takenSlots(
            $serviceId,
            $today,
            date('Y-m-d', strtotime('+14 days'))
        );

        $jours = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];
        $mois  = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

        $days = [];
        $cursor = new \DateTimeImmutable('today');
        $guard = 0;
        while (count($days) < 7 && $guard < 21) {
            $guard++;
            $w = (int) $cursor->format('w');           // 0 = dimanche
            if ($w === 0) { $cursor = $cursor->modify('+1 day'); continue; } // fermé

            $date  = $cursor->format('Y-m-d');
            $list  = ($w === 6) ? self::SATURDAY_SLOTS : self::SLOTS;
            $slots = [];
            foreach ($list as $t) {
                $slots[] = [
                    'time'  => $t,
                    'taken' => isset($taken[$date . ' ' . $t]),
                    'past'  => ($date === $today && $t <= $now),
                ];
            }
            $days[] = [
                'date'  => $date,
                'label' => $jours[$w] . ' ' . ((int) $cursor->format('j')) . ' ' . $mois[(int) $cursor->format('n') - 1],
                'slots' => $slots,
            ];
            $cursor = $cursor->modify('+1 day');
        }
        return $days;
    }

    /**
     * Lit et normalise les filtres depuis $_GET (valeurs scalaires garanties).
     *
     * @return array{category:string, max_price:string, max_duration:string}
     */
    private function readFilters(): array
    {
        return [
            'category'     => is_string($_GET['category']     ?? null) ? $_GET['category']     : '',
            'max_price'    => is_string($_GET['max_price']    ?? null) ? $_GET['max_price']    : '',
            'max_duration' => is_string($_GET['max_duration'] ?? null) ? $_GET['max_duration'] : '',
        ];
    }
}
