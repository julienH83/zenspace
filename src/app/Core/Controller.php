<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Contrôleur de base dont héritent tous les contrôleurs.
 * Regroupe les méthodes utilitaires communes (rendu, redirection, JSON…).
 */
class Controller
{
    /**
     * Affiche une vue HTML.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /** Renvoie une réponse JSON (utilisée par les appels dynamiques en fetch). */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /** Redirige vers une autre page. */
    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    /** Récupère le corps JSON d'une requête (pour les API). */
    protected function jsonInput(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Vérifie que l'utilisateur est connecté ; sinon redirige vers la connexion.
     */
    protected function requireLogin(): array
    {
        $user = Auth::user();
        if ($user === null) {
            $this->redirect('/connexion');
        }
        return $user;
    }

    /**
     * Vérifie que l'utilisateur connecté possède l'un des rôles autorisés.
     *
     * @param string[] $roles
     */
    protected function requireRole(array $roles): array
    {
        $user = $this->requireLogin();
        if (!in_array($user['role'], $roles, true)) {
            throw new HttpException(403, 'Accès refusé.');
        }
        return $user;
    }

    /** Lève une 404 propre (gabarit stylisé via le handler global). */
    protected function notFound(string $message = 'Page introuvable.'): never
    {
        throw new HttpException(404, $message);
    }
}
