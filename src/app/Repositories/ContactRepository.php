<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Accès aux données des messages de contact (table `contact_message`).
 */
final class ContactRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_message (name, email, subject, message)
             VALUES (:name, :email, :subject, :message)'
        );
        $stmt->execute([
            'name'    => $d['name'],
            'email'   => $d['email'],
            'subject' => $d['subject'],
            'message' => $d['message'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}
