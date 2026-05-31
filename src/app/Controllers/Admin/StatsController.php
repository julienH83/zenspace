<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Mongo;

/**
 * Statistiques — RÉSERVÉ à l'administrateur.
 *
 * Les données proviennent de la base NON relationnelle (MongoDB) : chaque
 * prestation terminée y a déposé un document {service, montant, date}.
 * On agrège ici le chiffre d'affaires et le nombre de réservations par prestation.
 */
final class StatsController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['admin']);

        // Filtre optionnel par période (date de début / fin).
        $filter = [];
        if (!empty($_GET['from'])) {
            $filter['date']['$gte'] = $_GET['from'];
        }
        if (!empty($_GET['to'])) {
            $filter['date']['$lte'] = $_GET['to'];
        }

        $documents = Mongo::findAll('revenue', $filter);

        // Agrégation côté PHP : CA + nombre par prestation.
        $byService = [];
        $totalRevenue = 0.0;
        foreach ($documents as $doc) {
            $title = $doc->service_title ?? 'Inconnu';
            $amount = (float) ($doc->amount ?? 0);
            $totalRevenue += $amount;
            if (!isset($byService[$title])) {
                $byService[$title] = ['count' => 0, 'revenue' => 0.0];
            }
            $byService[$title]['count']++;
            $byService[$title]['revenue'] += $amount;
        }
        // Tri décroissant par chiffre d'affaires (prestation la plus rentable en tête).
        uasort($byService, static fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        $this->render('admin/stats/index', [
            'title'        => 'Statistiques',
            'byService'    => $byService,
            'totalRevenue' => $totalRevenue,
            'totalCount'   => count($documents),
            'from'         => $_GET['from'] ?? '',
            'to'           => $_GET['to'] ?? '',
            'layout_admin' => true,
        ]);
    }
}
