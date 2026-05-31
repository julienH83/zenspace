<?php

declare(strict_types=1);

/**
 * Fonctions d'aide globales, utilisables directement dans les vues
 * (pour alléger l'écriture des gabarits).
 */

use App\Core\View;
use App\Core\Csrf;

if (!function_exists('e')) {
    /** Échappe une valeur pour l'affichage HTML (protection XSS). */
    function e(?string $value): string
    {
        return View::e($value);
    }
}

if (!function_exists('csrf')) {
    /** Champ caché contenant le jeton CSRF. */
    function csrf(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('old')) {
    /** Récupère une valeur précédemment saisie (réaffichage après erreur). */
    function old(array $source, string $key, string $default = ''): string
    {
        return View::e((string) ($source[$key] ?? $default));
    }
}

if (!function_exists('price')) {
    /** Formate un prix en euros. */
    function price(float|string $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ') . ' €';
    }
}

if (!function_exists('status_label')) {
    /** Libellé français d'un statut de réservation. */
    function status_label(string $status): string
    {
        return [
            'pending'   => 'En attente',
            'confirmed' => 'Confirmée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ][$status] ?? $status;
    }
}
