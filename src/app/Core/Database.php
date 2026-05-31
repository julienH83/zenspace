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
            $host = $_ENV['DB_HOST'] ?? 'mysql';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $name = $_ENV['DB_NAME'] ?? 'zenspace';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? 'root';

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    // Lève une exception en cas d'erreur SQL (plus facile à déboguer).
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // Récupère les résultats sous forme de tableaux associatifs.
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Vraies requêtes préparées côté serveur (sécurité).
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // En production on n'expose jamais le détail de l'erreur.
                http_response_code(500);
                exit('Erreur de connexion à la base de données.');
            }
        }

        return self::$instance;
    }
}
