<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Programme de fidélité : grand livre de points (table `loyalty_ledger`).
 *
 * Le solde n'est jamais stocké : il se déduit toujours de la somme des
 * mouvements (delta positifs gagnés / négatifs dépensés). C'est plus fiable
 * (aucune désynchronisation possible) et auditable. Hérite de BaseRepository.
 */
final class LoyaltyRepository extends BaseRepository
{
    /** Solde courant d'un client (somme des mouvements, 0 si aucun). */
    public function balance(int $userId): int
    {
        return $this->count(
            'SELECT COALESCE(SUM(delta), 0) FROM loyalty_ledger WHERE user_id = :uid',
            ['uid' => $userId]
        );
    }

    /**
     * Historique des mouvements de points (du plus récent au plus ancien).
     *
     * @return array<int, array<string, mixed>>
     */
    public function history(int $userId): array
    {
        return $this->fetchRows(
            'SELECT id, delta, reason, booking_id, created_at
             FROM loyalty_ledger
             WHERE user_id = :uid
             ORDER BY created_at DESC, id DESC',
            ['uid' => $userId]
        );
    }

    /**
     * Crédite (ou débite) des points à un client en consignant le motif.
     * Requête préparée ; $delta peut être négatif pour une dépense de points.
     */
    public function award(int $userId, int $delta, string $reason, ?int $bookingId = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO loyalty_ledger (user_id, delta, reason, booking_id)
             VALUES (:uid, :delta, :reason, :booking_id)'
        );
        $stmt->execute([
            'uid'        => $userId,
            'delta'      => $delta,
            'reason'     => $reason,
            'booking_id' => $bookingId,
        ]);
    }
}
