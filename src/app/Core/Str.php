<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Petites fonctions utilitaires sur les chaînes de caractères.
 */
final class Str
{
    /**
     * Transforme un titre en « slug » utilisable dans une URL.
     * Ex : "Massage relaxant californien" -> "massage-relaxant-californien".
     */
    public static function slug(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        return trim($text, '-');
    }
}
