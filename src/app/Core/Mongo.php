<?php

declare(strict_types=1);

namespace App\Core;

use MongoDB\Driver\Manager;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception as MongoException;

/**
 * Accès à la base de données NON relationnelle (MongoDB).
 *
 * Utilisée pour les STATISTIQUES : à chaque réservation terminée, on enregistre
 * un document {service_id, service_title, price, date} dans une collection.
 * L'administrateur peut ensuite agréger ces documents (chiffre d'affaires par
 * prestation, nombre de réservations…) sans surcharger la base relationnelle.
 *
 * On s'appuie sur le driver MongoDB natif de PHP (extension « mongodb »),
 * donc aucune dépendance Composer n'est nécessaire pour faire fonctionner ceci.
 */
final class Mongo
{
    private static ?Manager $manager = null;
    private static string $db = 'zenspace_stats';

    private function __construct()
    {
    }

    public static function getManager(): Manager
    {
        if (self::$manager === null) {
            $uri      = $_ENV['MONGO_URI'] ?? 'mongodb://mongo:27017';
            self::$db = $_ENV['MONGO_DB'] ?? 'zenspace_stats';
            self::$manager = new Manager($uri);
        }

        return self::$manager;
    }

    /**
     * Insère un document dans une collection.
     * Best-effort : si Mongo est indisponible, on n'interrompt pas l'utilisateur.
     */
    public static function insert(string $collection, array $document): void
    {
        try {
            $bulk = new BulkWrite();
            $bulk->insert($document);
            self::getManager()->executeBulkWrite(self::$db . '.' . $collection, $bulk);
        } catch (MongoException $e) {
            // On ignore silencieusement : les statistiques ne doivent jamais
            // bloquer une réservation.
            error_log('[Mongo] insertion échouée : ' . $e->getMessage());
        }
    }

    /**
     * Insère OU met à jour un document (upsert) selon un filtre. Idempotent :
     * appelé plusieurs fois avec le même filtre, il ne crée qu'un seul document.
     * Utilisé pour les statistiques de CA (évite le double comptage).
     */
    public static function upsert(string $collection, array $filter, array $set): void
    {
        try {
            $bulk = new BulkWrite();
            $bulk->update($filter, ['$set' => $set], ['upsert' => true]);
            self::getManager()->executeBulkWrite(self::$db . '.' . $collection, $bulk);
        } catch (MongoException $e) {
            error_log('[Mongo] upsert échoué : ' . $e->getMessage());
        }
    }

    /**
     * Récupère tous les documents d'une collection (optionnellement filtrés).
     *
     * @return array<int, object>
     */
    public static function findAll(string $collection, array $filter = []): array
    {
        try {
            $query  = new Query($filter);
            $cursor = self::getManager()->executeQuery(self::$db . '.' . $collection, $query);
            return $cursor->toArray();
        } catch (MongoException $e) {
            error_log('[Mongo] lecture échouée : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Exécute un pipeline d'agrégation natif (ex. $group + $sum pour le CA).
     *
     * @param array<int,array> $pipeline
     * @return array<int, object>
     */
    public static function aggregate(string $collection, array $pipeline): array
    {
        try {
            $command = new Command([
                'aggregate' => $collection,
                'pipeline'  => $pipeline,
                'cursor'    => new \stdClass(),
            ]);
            $cursor = self::getManager()->executeCommand(self::$db, $command);
            return $cursor->toArray();
        } catch (MongoException $e) {
            error_log('[Mongo] agrégation échouée : ' . $e->getMessage());
            return [];
        }
    }
}
