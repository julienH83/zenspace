<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Journal d'audit : trace les actions sensibles de l'espace de gestion
 * (suppression d'une prestation, désactivation d'un employé…).
 *
 * Les entrées sont stockées dans la collection MongoDB « audit_log ». L'écriture
 * est BEST-EFFORT : si Mongo est indisponible, l'action métier ne doit jamais
 * échouer à cause du journal (try/catch englobant + Mongo::insert déjà tolérant).
 */
final class AuditLog
{
    private function __construct()
    {
    }

    /**
     * Enregistre une action dans le journal d'audit.
     *
     * @param string $action Identifiant de l'action (ex. « service.delete »).
     * @param mixed  $target Cible concernée (id, slug, tableau de contexte…).
     */
    public static function record(string $action, mixed $target = null): void
    {
        try {
            Mongo::insert('audit_log', [
                'actor_id' => Auth::id(),
                'action'   => $action,
                'target'   => $target,
                'ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
                'at'       => date('c'),
            ]);
        } catch (\Throwable $e) {
            // Best-effort : on ne propage jamais l'erreur. On la journalise au
            // niveau PHP pour conserver une trace technique.
            error_log('[AuditLog] enregistrement échoué : ' . $e->getMessage());
        }
    }
}
