<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Protection CSRF (Cross-Site Request Forgery).
 *
 * Chaque formulaire inclut un jeton secret. À la soumission, on vérifie que le
 * jeton reçu correspond à celui en session : un site tiers ne peut donc pas
 * forger une requête valide au nom de l'utilisateur.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Champ caché à insérer dans les formulaires. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    /** Vérifie le jeton reçu (comparaison à temps constant). */
    public static function check(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /** Bloque la requête si le jeton est invalide. */
    public static function validate(): void
    {
        if (!self::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Jeton de sécurité invalide ou expiré. Veuillez réessayer.');
        }
    }
}
