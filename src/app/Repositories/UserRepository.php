<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Accès aux données de la table `user` (et jointure sur les rôles).
 * Toutes les requêtes sont préparées : protection contre les injections SQL.
 */
final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Recherche un utilisateur par e-mail (avec le libellé de son rôle). */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.label AS role
             FROM user u
             JOIN role r ON r.id = u.role_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.label AS role
             FROM user u
             JOIN role r ON r.id = u.role_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM user WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    /** Crée un utilisateur et renvoie son identifiant. */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO user (role_id, first_name, last_name, email, password_hash, phone, address, rgpd_consent)
             VALUES (:role_id, :first_name, :last_name, :email, :password_hash, :phone, :address, :rgpd_consent)'
        );
        $stmt->execute([
            'role_id'       => $data['role_id'],
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'phone'         => $data['phone'] ?? null,
            'address'       => $data['address'] ?? null,
            'rgpd_consent'  => $data['rgpd_consent'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = $this->db->prepare('UPDATE user SET password_hash = :h WHERE id = :id');
        $stmt->execute(['h' => $passwordHash, 'id' => $userId]);
    }

    /** Liste les employés (rôle « employe »). */
    public function findEmployees(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.is_active, u.created_at
             FROM user u
             JOIN role r ON r.id = u.role_id
             WHERE r.label = 'employe'
             ORDER BY u.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function setActive(int $userId, bool $active): void
    {
        $stmt = $this->db->prepare('UPDATE user SET is_active = :a WHERE id = :id');
        $stmt->execute(['a' => $active ? 1 : 0, 'id' => $userId]);
    }
}
