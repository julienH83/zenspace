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
            throw new HttpException(500, "Vue introuvable : {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layout.php';
    }

    /**
     * Rend une vue SANS layout et renvoie le HTML produit (chaîne).
     * Utilisé pour les e-mails et les fragments.
     *
     * @param array<string, mixed> $data
     */
    public static function renderToString(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new HttpException(500, "Vue introuvable : {$view}");
        }
        ob_start();
        require $viewFile;
        return (string) ob_get_clean();
    }

    /**
     * Rend une page d'erreur stylisée (errors/<code>.php), avec repli sur une
     * page générique si le gabarit précis n'existe pas. Tolérant aux pannes :
     * en dernier recours, affiche un message minimal sans dépendre de la session.
     */
    public static function renderError(int $status): void
    {
        $titles = [
            400 => 'Requête invalide',
            403 => 'Accès refusé',
            404 => 'Page introuvable',
            405 => 'Méthode non autorisée',
            419 => 'Session expirée',
            429 => 'Trop de requêtes',
            500 => 'Erreur interne',
        ];
        $title = $titles[$status] ?? 'Erreur';

        try {
            $specific = dirname(__DIR__) . '/Views/errors/' . $status . '.php';
            $view = is_file($specific) ? 'errors/' . $status : 'errors/generic';
            self::render($view, ['title' => $title, 'status' => $status]);
        } catch (\Throwable $e) {
            // Repli ultime : pas de layout, pas de session.
            echo '<!doctype html><html lang="fr"><meta charset="utf-8">'
                . '<title>' . self::e($title) . '</title>'
                . '<h1>' . self::e($title) . '</h1>'
                . '<p>Une erreur ' . $status . ' est survenue.</p>'
                . '<p><a href="/">Retour à l\'accueil</a></p></html>';
        }
    }

    /**
     * Échappe une chaîne pour l'affichage HTML (protection contre les failles XSS).
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
