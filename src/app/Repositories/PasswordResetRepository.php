<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Accès aux jetons de réinitialisation de mot de passe (table `password_reset`).
 * On ne stocke jamais le jeton en clair : seulement son hash SHA-256.
 */
final class PasswordResetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO password_reset (user_id, token_hash, expires_at)
             VALUES (:uid, :hash, :exp)'
        );
        $stmt->execute(['uid' => $userId, 'hash' => $tokenHash, 'exp' => $expiresAt]);
    }

    /** Récupère un jeton valide (non utilisé et non expiré). */
    public function findValid(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_reset
             WHERE token_hash = :hash AND used = 0 AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['hash' => $tokenHash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE password_reset SET used = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
