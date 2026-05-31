<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Messages « flash » : messages affichés une seule fois (après une action),
 * par exemple « Réservation confirmée ✓ ». Stockés en session puis consommés.
 */
final class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /**
     * Récupère et vide les messages flash.
     *
     * @return array<int, array{type:string, message:string}>
     */
    public static function pull(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}
