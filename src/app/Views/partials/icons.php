<?php
/**
 * Jeu d'icônes SVG « inline » (aucune dépendance externe, compatible CSP).
 * Les tracés utilisent `currentColor` : l'icône hérite de la couleur du texte
 * du conteneur (ex. .feature-icon). Trait fin et arrondi, cohérent avec la DA.
 *
 * Utilisation dans une vue :
 *   <?= icon('leaf', 'icon icon-lg') ?>
 */
if (!function_exists('icon')) {
    function icon(string $name, string $class = 'icon'): string
    {
        $paths = [
            // Détente / nature
            'leaf'     => '<path d="M11 21c-4 0-8-3-8-8 0-6 6-10 17-10 0 11-4 17-9 18Z"/><path d="M11 21c0-6 3-11 8-14"/>',
            // Soin / mains expertes
            'hands'    => '<path d="M8 13V5.5a1.5 1.5 0 0 1 3 0V12"/><path d="M11 12V4.5a1.5 1.5 0 0 1 3 0V12"/><path d="M14 12.5V6.5a1.5 1.5 0 0 1 3 0V14a6 6 0 0 1-6 6H9a5 5 0 0 1-4-2l-2.5-3a1.6 1.6 0 0 1 2.5-2l1.5 1.5"/>',
            // Réservation en ligne
            'calendar' => '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v3M16 3v3"/><path d="m9 14 2 2 4-4"/>',
            // Qualité / éclat
            'sparkle'  => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8Z"/><path d="M19 15l.6 1.8L21.5 17l-1.9.6L19 19l-.6-1.4L16.5 17l1.9-.2Z"/>',
            // Bien-être / cœur
            'heart'    => '<path d="M12 20s-7-4.5-9.2-9A4.8 4.8 0 0 1 12 6a4.8 4.8 0 0 1 9.2 5C19 15.5 12 20 12 20Z"/>',
            // Horaires souples
            'clock'    => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
            // Contact téléphonique
            'phone'    => '<path d="M4.5 4.5A2 2 0 0 1 6.5 2.5h1.8a1.5 1.5 0 0 1 1.45 1.11l.8 3a1.5 1.5 0 0 1-.42 1.5L9 9.2a12 12 0 0 0 5.8 5.8l1.09-1.12a1.5 1.5 0 0 1 1.5-.42l3 .8A1.5 1.5 0 0 1 21.5 15.7v1.8a2 2 0 0 1-2 2A16 16 0 0 1 4.5 4.5Z"/>',
            // Localisation / adresse
            'pin'      => '<path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.7"/>',
        ];
        $inner = $paths[$name] ?? $paths['sparkle'];
        return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            . 'stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . $inner . '</svg>';
    }
}
