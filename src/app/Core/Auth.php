<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gestion de l'authentification via la session PHP.
 *
 * Le mot de passe est vérifié avec password_verify() (bcrypt) : on ne compare
 * jamais de mot de passe en clair.
 */
final class Auth
{
    /** Enregistre l'utilisateur en session après une connexion réussie. */
    public static function login(array $user): void
    {
        // Régénère l'identifiant de session pour éviter la fixation de session.
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => (int) $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'role'       => $user['role'], // 'client' | 'employe' | 'admin'
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
    }

    /** @return array<string,mixed>|null L'utilisateur connecté, ou null. */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    /** Hache un mot de passe avant stockage (bcrypt, coût 12). */
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
