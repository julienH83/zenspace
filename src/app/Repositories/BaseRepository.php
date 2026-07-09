<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Classe de base des repositories : factorise l'obtention de la connexion PDO
 * et quelques helpers communs (compte, requêtes préparées). Évite de répéter le
 * boilerplate `private PDO $db; __construct(){ ... }` dans chaque repository.
 */
abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Compte les lignes d'une requête COUNT(*) paramétrée. */
    protected function count(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Exécute une requête préparée et renvoie toutes les lignes. */
    protected function fetchRows(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Exécute une requête préparée et renvoie une ligne (ou null). */
    protected function fetchRow(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
