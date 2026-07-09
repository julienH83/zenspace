<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Messagerie en temps réel — SCAFFOLD (démonstration).
 *
 * La page explique l'architecture envisagée : un hub Mercure diffuse des
 * mises à jour via Server-Sent Events (SSE), que le navigateur reçoit avec
 * l'API EventSource. Aucune dépendance n'étant requise pour la démonstration,
 * la page ne provoque jamais d'erreur.
 */
final class ChatController extends Controller
{
    public function page(): void
    {
        $this->render('chat/demo', [
            'title' => 'Messagerie temps réel (démonstration)',
        ]);
    }
}
