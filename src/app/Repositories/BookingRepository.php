<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Accès aux données des réservations (table `booking`) + historique des statuts.
 */
final class BookingRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Crée une réservation ET sa première ligne d'historique dans une
     * transaction (les deux écritures réussissent ou échouent ensemble).
     */
    public function create(array $d): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO booking (user_id, service_id, booking_date, time_slot, status, total_price)
                 VALUES (:user_id, :service_id, :booking_date, :time_slot, :status, :total_price)'
            );
            $stmt->execute([
                'user_id'      => $d['user_id'],
                'service_id'   => $d['service_id'],
                'booking_date' => $d['booking_date'],
                'time_slot'    => $d['time_slot'],
                'status'       => 'pending',
                'total_price'  => $d['total_price'],
            ]);
            $bookingId = (int) $this->db->lastInsertId();

            $hist = $this->db->prepare(
                'INSERT INTO booking_status_history (booking_id, status) VALUES (:id, :status)'
            );
            $hist->execute(['id' => $bookingId, 'status' => 'pending']);

            $this->db->commit();
            return $bookingId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Vérifie qu'un créneau est libre pour une prestation. */
    public function isSlotTaken(int $serviceId, string $date, string $slot): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM booking
             WHERE service_id = :sid AND booking_date = :d AND time_slot = :t
               AND status <> 'cancelled'
             LIMIT 1"
        );
        $stmt->execute(['sid' => $serviceId, 'd' => $date, 't' => $slot]);
        return (bool) $stmt->fetchColumn();
    }

    /** Réservations d'un client. */
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, s.title AS service_title, s.slug AS service_slug
             FROM booking b
             JOIN service s ON s.id = b.service_id
             WHERE b.user_id = :uid
             ORDER BY b.booking_date DESC, b.time_slot DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, s.title AS service_title, s.slug AS service_slug,
                    u.first_name, u.last_name, u.email
             FROM booking b
             JOIN service s ON s.id = b.service_id
             JOIN user u    ON u.id = b.user_id
             WHERE b.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Toutes les réservations (espace employé/admin), avec filtre statut. */
    public function findAll(?string $status = null): array
    {
        $sql = 'SELECT b.*, s.title AS service_title, u.first_name, u.last_name, u.email
                FROM booking b
                JOIN service s ON s.id = b.service_id
                JOIN user u    ON u.id = b.user_id';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE b.status = :status';
            $params['status'] = $status;
        }
        $sql .= ' ORDER BY b.booking_date DESC, b.time_slot DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Créneaux déjà réservés (non annulés) pour une prestation, sur une période.
     * Retourne un ensemble dont les clés sont "Y-m-d H:i" (test d'existence rapide).
     *
     * @return array<string, true>
     */
    public function takenSlots(int $serviceId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT booking_date, time_slot FROM booking
             WHERE service_id = :sid AND status <> 'cancelled'
               AND booking_date BETWEEN :from AND :to"
        );
        $stmt->execute(['sid' => $serviceId, 'from' => $from, 'to' => $to]);
        $set = [];
        foreach ($stmt->fetchAll() as $row) {
            $set[$row['booking_date'] . ' ' . substr((string) $row['time_slot'], 0, 5)] = true;
        }
        return $set;
    }

    /** Compte les réservations par statut (COUNT(*) — pas de chargement complet). */
    public function countByStatus(?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM booking';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' WHERE status = :status';
            $params['status'] = $status;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function statusHistory(int $bookingId): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, changed_at FROM booking_status_history
             WHERE booking_id = :id ORDER BY changed_at ASC'
        );
        $stmt->execute(['id' => $bookingId]);
        return $stmt->fetchAll();
    }

    /** Change le statut d'une réservation (+ trace dans l'historique). */
    public function updateStatus(int $bookingId, string $status): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE booking SET status = :s WHERE id = :id');
            $stmt->execute(['s' => $status, 'id' => $bookingId]);

            $hist = $this->db->prepare(
                'INSERT INTO booking_status_history (booking_id, status) VALUES (:id, :s)'
            );
            $hist->execute(['id' => $bookingId, 's' => $status]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
