<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Accès aux données des avis (table `review`).
 */
final class ReviewRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO review (user_id, service_id, booking_id, rating, comment)
             VALUES (:user_id, :service_id, :booking_id, :rating, :comment)'
        );
        $stmt->execute([
            'user_id'    => $d['user_id'],
            'service_id' => $d['service_id'],
            'booking_id' => $d['booking_id'],
            'rating'     => $d['rating'],
            'comment'    => $d['comment'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Avis validés (affichés publiquement). */
    public function findValidated(int $limit = 6): array
    {
        // LIMIT injecté en entier maîtrisé (jamais une saisie utilisateur brute).
        $limit = max(1, min(50, $limit));
        $stmt = $this->db->query(
            "SELECT r.rating, r.comment, r.created_at,
                    u.first_name, s.title AS service_title
             FROM review r
             JOIN user u    ON u.id = r.user_id
             JOIN service s ON s.id = r.service_id
             WHERE r.is_validated = 1
             ORDER BY r.created_at DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    /** Avis validés d'une prestation donnée (affichés sur sa fiche). */
    public function findValidatedByService(int $serviceId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $stmt = $this->db->prepare(
            "SELECT r.rating, r.comment, r.created_at, u.first_name
             FROM review r
             JOIN user u ON u.id = r.user_id
             WHERE r.service_id = :sid AND r.is_validated = 1
             ORDER BY r.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute(['sid' => $serviceId]);
        return $stmt->fetchAll();
    }

    /**
     * Résumé de note d'une prestation : moyenne + nombre d'avis validés.
     *
     * @return array{avg: ?float, count: int}
     */
    public function ratingSummary(int $serviceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT AVG(rating) AS avg, COUNT(*) AS count
             FROM review WHERE service_id = :sid AND is_validated = 1'
        );
        $stmt->execute(['sid' => $serviceId]);
        $row = $stmt->fetch() ?: ['avg' => null, 'count' => 0];
        return [
            'avg'   => $row['avg'] !== null ? (float) $row['avg'] : null,
            'count' => (int) $row['count'],
        ];
    }

    /** Avis en attente de modération. */
    public function findPending(): array
    {
        $stmt = $this->db->query(
            'SELECT r.*, u.first_name, u.last_name, s.title AS service_title
             FROM review r
             JOIN user u    ON u.id = r.user_id
             JOIN service s ON s.id = r.service_id
             WHERE r.is_validated = 0
             ORDER BY r.created_at ASC'
        );
        return $stmt->fetchAll();
    }

    /** Nombre d'avis en attente de modération (COUNT(*)). */
    public function countPending(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM review WHERE is_validated = 0')->fetchColumn();
    }

    public function existsForBooking(int $bookingId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM review WHERE booking_id = :id LIMIT 1');
        $stmt->execute(['id' => $bookingId]);
        return (bool) $stmt->fetchColumn();
    }

    public function validate(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE review SET is_validated = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM review WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
