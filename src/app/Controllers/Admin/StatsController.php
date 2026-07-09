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

        // Filtre optionnel par période. Les dates sont STRICTEMENT validées au
        // format Y-m-d : si l'on reçoit autre chose (ex. ?from[$gt]= → tableau),
        // on l'ignore. Corrige l'injection NoSQL : aucune valeur non scalaire
        // n'atteint le filtre Mongo.
        $from = $this->validDate($_GET['from'] ?? null);
        $to   = $this->validDate($_GET['to'] ?? null);

        $match = [];
        if ($from !== null) {
            $match['date']['$gte'] = $from;
        }
        if ($to !== null) {
            $match['date']['$lte'] = $to;
        }

        // Agrégation NATIVE MongoDB ($group + $sum) plutôt qu'en PHP : plus
        // performant et démonstratif de la maîtrise NoSQL.
        $pipeline = [];
        if ($match !== []) {
            $pipeline[] = ['$match' => $match];
        }
        $pipeline[] = ['$group' => [
            '_id'     => '$service_title',
            'revenue' => ['$sum' => '$amount'],
            'count'   => ['$sum' => 1],
        ]];
        $pipeline[] = ['$sort' => ['revenue' => -1]];

        $rows = Mongo::aggregate('revenue', $pipeline);

        $byService = [];
        $totalRevenue = 0.0;
        $totalCount = 0;
        foreach ($rows as $row) {
            $title = (string) ($row->_id ?? 'Inconnu');
            $revenue = (float) ($row->revenue ?? 0);
            $count = (int) ($row->count ?? 0);
            $byService[$title] = ['count' => $count, 'revenue' => $revenue];
            $totalRevenue += $revenue;
            $totalCount += $count;
        }

        $this->render('admin/stats/index', [
            'title'        => 'Statistiques',
            'byService'    => $byService,
            'totalRevenue' => $totalRevenue,
            'totalCount'   => $totalCount,
            'from'         => $from ?? '',
            'to'           => $to ?? '',
            'layout_admin' => true,
        ]);
    }

    /** Valide une date au format Y-m-d ; renvoie null si invalide ou non scalaire. */
    private function validDate(mixed $raw): ?string
    {
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $raw);
        return ($d && $d->format('Y-m-d') === $raw) ? $raw : null;
    }
}
