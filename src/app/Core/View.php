<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Moteur de rendu des vues (gabarits PHP).
 *
 * Une vue est un fichier dans app/Views/. Elle est encadrée par un layout
 * commun (en-tête + pied de page) sauf indication contraire.
 */
final class View
{
    /**
     * Affiche une vue dans le layout principal.
     *
     * @param array<string, mixed> $data Variables transmises à la vue.
     */
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            exit("Vue introuvable : {$view}");
        }

        // On capture le contenu de la vue dans une variable...
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // ...puis on l'injecte dans le layout commun.
        require dirname(__DIR__) . '/Views/layout.php';
    }

    /**
     * Échappe une chaîne pour l'affichage HTML (protection contre les failles XSS).
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
