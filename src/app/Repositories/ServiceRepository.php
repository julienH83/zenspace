<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Accès aux données des prestations (table `service`) et de leurs catégories.
 */
final class ServiceRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Toutes les prestations actives (avec le libellé de catégorie). */
    public function findActive(): array
    {
        $stmt = $this->db->query(
            'SELECT s.*, c.label AS category_label, c.slug AS category_slug
             FROM service s
             JOIN category c ON c.id = s.category_id
             WHERE s.is_active = 1
             ORDER BY s.title'
        );
        return $stmt->fetchAll();
    }

    /**
     * Recherche filtrée (catégorie, prix max, durée max).
     * Construit la requête dynamiquement mais TOUJOURS avec des paramètres liés.
     *
     * @param array{category?:string, max_price?:string, max_duration?:string} $filters
     */
    public function search(array $filters): array
    {
        $sql = 'SELECT s.*, c.label AS category_label, c.slug AS category_slug
                FROM service s
                JOIN category c ON c.id = s.category_id
                WHERE s.is_active = 1';
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= ' AND c.slug = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= ' AND s.price <= :max_price';
            $params['max_price'] = (float) $filters['max_price'];
        }
        if (!empty($filters['max_duration'])) {
            $sql .= ' AND s.duration_min <= :max_duration';
            $params['max_duration'] = (int) $filters['max_duration'];
        }

        $sql .= ' ORDER BY s.price ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, c.label AS category_label
             FROM service s
             JOIN category c ON c.id = s.category_id
             WHERE s.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, c.label AS category_label
             FROM service s
             JOIN category c ON c.id = s.category_id
             WHERE s.slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Toutes les prestations (y compris inactives) pour l'espace de gestion. */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT s.*, c.label AS category_label
             FROM service s
             JOIN category c ON c.id = s.category_id
             ORDER BY s.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function categories(): array
    {
        return $this->db->query('SELECT * FROM category ORDER BY label')->fetchAll();
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO service (category_id, title, slug, description, duration_min, price, image, is_active)
             VALUES (:category_id, :title, :slug, :description, :duration_min, :price, :image, :is_active)'
        );
        $stmt->execute([
            'category_id'  => $d['category_id'],
            'title'        => $d['title'],
            'slug'         => $d['slug'],
            'description'  => $d['description'],
            'duration_min' => $d['duration_min'],
            'price'        => $d['price'],
            'image'        => $d['image'] ?? null,
            'is_active'    => $d['is_active'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE service
             SET category_id = :category_id, title = :title, slug = :slug,
                 description = :description, duration_min = :duration_min,
                 price = :price, is_active = :is_active
             WHERE id = :id'
        );
        $stmt->execute([
            'id'           => $id,
            'category_id'  => $d['category_id'],
            'title'        => $d['title'],
            'slug'         => $d['slug'],
            'description'  => $d['description'],
            'duration_min' => $d['duration_min'],
            'price'        => $d['price'],
            'is_active'    => $d['is_active'] ?? 1,
        ]);
    }

    public function delete(int $id): void
    {
        // Suppression « douce » : on désactive plutôt que de supprimer
        // (préserve l'historique des réservations liées).
        $stmt = $this->db->prepare('UPDATE service SET is_active = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
