<article class="form-card form-wide">
    <div class="page-head">
        <h1>Messagerie temps réel</h1>
        <span class="badge badge-pending">Démonstration</span>
    </div>

    <p class="muted">
        Cette page est un <strong>scaffold</strong> : elle décrit l'architecture
        temps réel envisagée. Aucune connexion n'est ouverte ici, la page ne
        provoque donc aucune erreur.
    </p>

    <h2>Principe — Mercure &amp; Server-Sent Events (SSE)</h2>
    <ul>
        <li>Le serveur publie des messages sur un <strong>hub Mercure</strong>.</li>
        <li>Le navigateur s'abonne à un <em>topic</em> via l'API <code>EventSource</code> (SSE).</li>
        <li>Chaque nouveau message est poussé du serveur vers le client, sans rechargement ni sondage.</li>
        <li>Les SSE sont unidirectionnels (serveur → client) ; l'envoi de messages se fait par un POST classique.</li>
    </ul>

    <h2>Extrait du code client (JavaScript)</h2>
    <pre class="code-block"><code><?= e(
'// Abonnement au flux temps réel via Server-Sent Events
const url = new URL(\'https://hub.exemple/.well-known/mercure\');
url.searchParams.append(\'topic\', \'https://zenspace.fr/chat/{conversationId}\');

const source = new EventSource(url, { withCredentials: true });

source.onmessage = (event) => {
    const message = JSON.parse(event.data);
    afficherMessage(message); // ajoute le message à la conversation
};

source.onerror = () => {
    // Reconnexion automatique gérée par le navigateur.
};'
) ?></code></pre>

    <p class="muted">
        Pour activer : déployer un hub Mercure, configurer son URL et sa clé JWT
        dans <code>.env</code>, puis publier les messages depuis le serveur.
    </p>
</article>
