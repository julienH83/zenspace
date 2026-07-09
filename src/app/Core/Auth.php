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
        $_SESSION['id_created'] = time();
        // Régénère aussi le jeton CSRF (anti-rejeu d'un ancien jeton).
        unset($_SESSION['csrf_token']);
        $_SESSION['user'] = [
            'id'         => (int) $user['id'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'role'       => $user['role'], // 'client' | 'employe' | 'admin'
        ];
    }

    /** Déconnexion COMPLÈTE : vide la session, supprime le cookie, détruit la session. */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $p['path'],
                    'domain'   => $p['domain'],
                    'secure'   => $p['secure'],
                    'httponly' => $p['httponly'],
                    'samesite' => $p['samesite'] ?: 'Lax',
                ]
            );
        }
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
