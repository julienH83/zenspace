<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Exception HTTP : permet à n'importe quelle couche de l'application de demander
 * une réponse d'erreur précise (404, 403, 405, 500…) qui sera rendue par le
 * handler global défini dans le front controller (index.php).
 */
final class HttpException extends \RuntimeException
{
    /** @param array<string,string> $headers En-têtes à émettre (ex. Allow pour un 405). */
    public function __construct(
        private int $status = 500,
        string $message = '',
        private array $headers = []
    ) {
        parent::__construct($message !== '' ? $message : self::defaultMessage($status), $status);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /** @return array<string,string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    private static function defaultMessage(int $status): string
    {
        return [
            400 => 'Requête invalide.',
            403 => 'Accès refusé.',
            404 => 'Page introuvable.',
            405 => 'Méthode non autorisée.',
            419 => 'Jeton de sécurité invalide ou expiré.',
            429 => 'Trop de requêtes.',
            500 => 'Erreur interne du serveur.',
        ][$status] ?? 'Erreur.';
    }
}
