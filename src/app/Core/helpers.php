<?php

declare(strict_types=1);

/**
 * Fonctions d'aide globales, utilisables directement dans les vues
 * (pour alléger l'écriture des gabarits).
 */

use App\Core\View;
use App\Core\Csrf;

if (!function_exists('env')) {
    /**
     * Lit une variable d'environnement (.env chargé dans $_ENV, ou getenv()).
     * Retourne $default si absente.
     */
    function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value === false || $value === null || $value === '') ? $default : $value;
    }
}

if (!function_exists('env_required')) {
    /** Comme env() mais lève une exception si la variable est absente (secrets prod). */
    function env_required(string $key): string
    {
        $value = env($key);
        if ($value === null) {
            throw new \RuntimeException("Variable d'environnement obligatoire manquante : {$key}");
        }
        return $value;
    }
}

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

if (!function_exists('picture')) {
    /**
     * Balise <picture> servant une image WebP (plus légère) avec repli sur le
     * fichier d'origine (JPEG/PNG). La source WebP n'est ajoutée que si le
     * fichier .webp existe réellement dans /assets/images.
     *
     * @param string $file    Nom de fichier dans /assets/images (ex. « hero-spa.jpg »)
     * @param string $alt     Texte alternatif (vide = image décorative)
     * @param string $loading « lazy » (défaut) ou « eager » pour les images critiques
     */
    function picture(string $file, string $alt = '', string $loading = 'lazy', ?int $width = null, ?int $height = null): string
    {
        $src  = '/assets/images/' . $file;
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $src);
        $docroot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $hasWebp = $webp !== null && $webp !== $src && is_file($docroot . $webp);

        $dim = ($width && $height) ? ' width="' . (int) $width . '" height="' . (int) $height . '"' : '';
        $img = '<img src="' . e($src) . '" alt="' . e($alt) . '" loading="' . e($loading) . '" decoding="async"' . $dim . '>';

        return $hasWebp
            ? '<picture><source srcset="' . e($webp) . '" type="image/webp">' . $img . '</picture>'
            : $img;
    }
}

if (!function_exists('stars')) {
    /**
     * Rendu HTML d'une note en étoiles pour une carte de prestation.
     * $avg (0–5) et $count proviennent des colonnes maintenues par TRIGGER SQL.
     * Renvoie une chaîne vide s'il n'y a aucun avis (rien à afficher).
     */
    function stars(float|string $avg, int|string $count): string
    {
        $count = max(0, (int) $count);
        if ($count === 0) {
            return '';
        }
        $avg     = max(0.0, min(5.0, (float) $avg));
        $filled  = (int) round($avg);
        $glyphs  = str_repeat('★', $filled) . str_repeat('☆', 5 - $filled);
        $avgTxt  = number_format($avg, 1, ',', '');
        $label   = 'Note : ' . $avgTxt . ' sur 5, ' . $count . ' avis';
        return '<span class="card-rating"><span class="stars" aria-label="' . e($label) . '">'
            . $glyphs . '</span> <span class="rating-count">' . $avgTxt . ' · ' . $count . '</span></span>';
    }
}
