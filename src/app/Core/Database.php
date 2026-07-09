<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Connexion à la base de données relationnelle (MySQL) via PDO.
 *
 * On utilise le patron « singleton » : une seule connexion est créée et
 * partagée dans toute l'application (inutile de se reconnecter à chaque requête).
 *
 * PDO + requêtes préparées = protection contre les injections SQL.
 */
final class Database
{
    private static ?PDO $instance = null;

    /** Empêche l'instanciation directe (on passe par getConnection()). */
    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Hôte/port/nom : valeurs par défaut non sensibles acceptables.
            $host = env('DB_HOST', 'mysql');
            $port = env('DB_PORT', '3306');
            $name = env('DB_NAME', 'zenspace');
            // Identifiants : JAMAIS de fallback « root » en dur. On exige les
            // variables d'environnement (principe du moindre privilège).
            $user = env_required('DB_USER');
            $pass = env_required('DB_PASS');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,   // vraies requêtes préparées (sécurité)
                ]);
            } catch (PDOException $e) {
                error_log('[Database] ' . $e->getMessage());
                // En production on n'expose jamais le détail ; en dev on aide au débogage.
                $detail = (env('APP_ENV') === 'dev') ? ' (' . $e->getMessage() . ')' : '';
                throw new \App\Core\HttpException(500, 'Erreur de connexion à la base de données.' . $detail);
            }
        }

        return self::$instance;
    }
}
