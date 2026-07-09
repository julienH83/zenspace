# 🌿 ZenSpace 2.0 — Plan de Refonte Radicale

> Document de référence unique. Plan d'action exécutable par une équipe de développement.
> De la correction des failles à une expérience numérique immersive de référence dans le secteur du bien-être.

**Auteur :** Architecture & Direction technique
**Stack cible :** PHP 8.2 (MVC maison durci) · MySQL 8 · MongoDB 7 · Redis 7 · Three.js · Mercure · Docker
**Légende priorités :** 🔴 Critique · 🟠 Haute · 🟡 Moyenne · 🟢 Basse

---

## Sommaire

0. [Vision & direction](#0-vision--direction-produit)
1. [Sécurité & correction des bugs](#1-sécurité--correction-des-bugs)
2. [UI/UX & identité visuelle](#2-uiux--identité-visuelle)
3. [Animations & immersion 3D](#3-animations--immersion-3d)
4. [Nouvelles fonctionnalités](#4-nouvelles-fonctionnalités-incroyables)
5. [Accessibilité RGAA AA](#5-accessibilité--rgaa-niveau-aa)
6. [SEO & performance](#6-seo--performance)
7. [Architecture & qualité de code](#7-architecture--qualité-de-code)
8. [Roadmap, matrice & KPIs](#8-roadmap-matrice-effortimpact--kpis)

---

## 0. Vision & direction produit

### 0.1 Le concept : « Sérénité numérique »

ZenSpace ne doit pas seulement *vendre* du bien-être, il doit le *faire ressentir* dès la première seconde. La refonte repose sur trois piliers expérientiels :

| Pilier | Traduction concrète |
|--------|---------------------|
| **Calme** | Rythme lent des animations (300–600 ms, easing `cubic-bezier(.4,0,.2,1)`), espace blanc généreux, sons binauraux optionnels, dégradés organiques. |
| **Clarté** | Hiérarchie typographique nette, contrastes AA, parcours de réservation en 3 étapes max, zéro friction. |
| **Confiance** | Sécurité visible (paiement Stripe, badges, avis vérifiés), accessibilité totale, transparence RGPD. |

### 0.2 Stack : avant → après

```
                AVANT                          APRÈS (2.0)
  ┌────────────────────────────┐   ┌──────────────────────────────────┐
  │ PHP MVC maison              │   │ PHP MVC maison + conteneur DI     │
  │ MySQL                       │   │ MySQL (relationnel/transactionnel)│
  │ MongoDB (stats)             │   │ MongoDB (stats + logs d'audit)    │
  │ —                           │   │ Redis (sessions, cache, rate-limit│
  │ Three.js (déco only)        │   │ Three.js (hero immersif, splitté) │
  │ —                           │   │ Mercure (chat & notifs temps réel)│
  │ —                           │   │ Stripe (paiement + factures PDF)  │
  │ Filtres JS-only             │   │ Filtres SSR + amélioration JS     │
  │ Pas de tests / CI           │   │ PHPUnit + Cypress + GitHub Actions│
  └────────────────────────────┘   └──────────────────────────────────┘
```

> **Principe directeur :** *progressive enhancement* partout. Le site fonctionne sans JS (SSR), puis s'enrichit. Aucune fonctionnalité critique ne dépend du client.

### 0.3 Roadmap en 5 phases

```
Phase 1  ████████  Sécurité & dette (S1-S2)        ← bloquant prod
Phase 2     ██████  Design system + RGAA (S2-S4)
Phase 3        ████████  3D, réservation calendrier (S4-S7)
Phase 4            ██████  Paiement, chat, fidélité (S7-S10)
Phase 5               ████████  Communauté, blog, méditation, polish (S10-S14)
```

---

## 1. SÉCURITÉ & CORRECTION DES BUGS

### 1.1 🔴 Reset de mot de passe envoyé par e-mail (pas en flash)

**Problème** — Le lien contenant le token est affiché en message flash ([AuthController.php:147](../src/app/Controllers/AuthController.php)). N'importe qui peut déclencher un reset et lire le lien → prise de contrôle de compte triviale.

**Solution** — Envoyer le lien par e-mail via un service `Mailer` (SMTP en dev avec Mailpit, transactionnel en prod). Ne jamais renvoyer le token dans la réponse HTTP.

```php
// src/app/Core/Mailer.php — abstraction d'envoi (PHPMailer ou Symfony Mailer)
final class Mailer
{
    public function send(string $to, string $subject, string $htmlView, array $data): void
    {
        $html = View::renderToString("emails/$htmlView", $data); // gabarit dédié
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST');      // mailpit en dev, ex: smtp.eu.mailgun.org en prod
        $mail->Port       = (int) env('MAIL_PORT', '1025');
        $mail->SMTPAuth   = (bool) env('MAIL_AUTH', '0');
        $mail->Username   = env('MAIL_USER', '');
        $mail->Password   = env('MAIL_PASS', '');
        $mail->setFrom(env('MAIL_FROM', 'no-reply@zenspace.fr'), 'ZenSpace');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->send();
    }
}
```

```php
// AuthController::forgot() — extrait corrigé
$token     = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);
$this->resets->create($user['id'], $tokenHash, (new \DateTime('+1 hour'))->format('Y-m-d H:i:s'));

$link = env('APP_URL') . '/reinitialiser/' . $token;        // token EN CLAIR seulement dans l'e-mail
$this->mailer->send($user['email'], 'Réinitialisation de votre mot de passe', 'reset', [
    'firstName' => $user['first_name'],
    'link'      => $link,
]);

// Réponse TOUJOURS identique (anti-énumération), aucun token exposé
Flash::set('info', "Si un compte existe pour cette adresse, un e-mail vient d'être envoyé.");
return $this->redirect('/connexion');
```

> **Impact :** ferme une faille critique de compromission de compte. **Sécurité +++**

---

### 1.2 🔴 Injection NoSQL dans StatsController

**Problème** — `$_GET['from']`/`$_GET['to']` injectés bruts dans le filtre Mongo ([StatsController.php:25](../src/app/Controllers/Admin/StatsController.php)). PHP parse `?from[$gt]=` en tableau → injection d'opérateurs Mongo (`$ne`, `$where`, `$gt`).

**Solution** — Caster en `string` et valider le format `Y-m-d` avant usage. Refuser tout ce qui n'est pas une date.

```php
private function dateParam(string $key): ?string
{
    $raw = $_GET[$key] ?? null;
    if (!is_string($raw) || $raw === '') {
        return null;                                   // tableau → rejeté
    }
    $d = \DateTime::createFromFormat('Y-m-d', $raw);
    return ($d && $d->format('Y-m-d') === $raw) ? $raw : null;
}

public function index(): void
{
    $this->requireRole(['admin']);
    $from = $this->dateParam('from');
    $to   = $this->dateParam('to');

    $filter = [];
    if ($from) { $filter['date']['$gte'] = $from; }   // valeurs garanties scalaires
    if ($to)   { $filter['date']['$lte'] = $to; }
    // ...
}
```

> **Impact :** neutralise l'injection NoSQL (exfiltration/contournement de filtre). **Sécurité ++**

---

### 1.3 🔴 Réservation d'un créneau annulé → erreur fatale

**Problème** — La contrainte `uq_slot (service_id, booking_date, time_slot)` couvre **toutes** les réservations, mais `isSlotTaken()` exclut les `cancelled` ([BookingRepository.php:63](../src/app/Repositories/BookingRepository.php)). Réserver un créneau précédemment annulé → violation d'unicité → fatal car `create()` n'attrape rien.

**Solution (recommandée) — libérer réellement le créneau à l'annulation.** Puisque MySQL ne supporte pas les index uniques partiels, on neutralise la ligne annulée en renseignant une colonne `cancelled_at`, et on rend l'index unique « partiel » via une colonne générée :

```sql
ALTER TABLE booking
  ADD COLUMN cancelled_at DATETIME NULL DEFAULT NULL,
  -- clé d'unicité qui devient NULL quand la résa est annulée (NULL n'entre pas dans un index UNIQUE)
  ADD COLUMN slot_key VARCHAR(64)
    GENERATED ALWAYS AS (
      IF(status = 'cancelled', NULL, CONCAT_WS('|', service_id, booking_date, time_slot))
    ) STORED;

ALTER TABLE booking DROP INDEX uq_slot;
ALTER TABLE booking ADD UNIQUE KEY uq_slot_active (slot_key);
```

Et on protège toujours côté applicatif avec un `try/catch` propre (défense en profondeur, gère le TOCTOU entre `isSlotTaken` et `create`) :

```php
public function create(): void
{
    // ... validations ...
    try {
        $id = $this->bookings->create($data);          // transaction interne
    } catch (\PDOException $e) {
        if ($e->getCode() === '23000') {                // violation de contrainte d'intégrité
            Flash::set('error', 'Ce créneau vient d\'être réservé. Choisissez-en un autre.');
            return $this->redirect("/reserver/{$serviceId}");
        }
        throw $e;                                        // remonte au handler global (cf. §7.2)
    }
    Flash::set('success', 'Réservation confirmée 🌿');
    return $this->redirect("/reservation/$id");
}
```

> **Impact :** plus de crash, message clair, intégrité garantie même en concurrence. **Fiabilité +++**

---

### 1.4 🔴 Double comptage du CA (MongoDB)

**Problème** — Repasser une réservation `completed → completed` réinsère un document `revenue` ([AdminBookingController.php:61](../src/app/Controllers/Admin/AdminBookingController.php)) → CA gonflé.

**Solution** — N'insérer **que sur transition réelle** vers `completed`, et rendre l'écriture **idempotente** via un `upsert` clé sur `booking_id`.

```php
$previous = $booking['status'];
$this->bookings->updateStatus($id, $newStatus);

if ($newStatus === 'completed' && $previous !== 'completed') {
    Mongo::upsert('revenue', ['booking_id' => (int) $id], [
        '$set' => [
            'booking_id'    => (int) $id,
            'service_id'    => (int) $booking['service_id'],
            'service_title' => $booking['service_title'],
            'amount'        => (float) $booking['total_price'],
            'date'          => $booking['booking_date'],
        ],
    ]);
}
```

```php
// src/app/Core/Mongo.php — ajout
public static function upsert(string $collection, array $filter, array $update): void
{
    try {
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->update($filter, $update, ['upsert' => true]);
        self::manager()->executeBulkWrite(self::db() . ".$collection", $bulk);
    } catch (\Throwable $e) {
        error_log('[Mongo upsert] ' . $e->getMessage());  // best-effort, ne bloque pas l'UX
    }
}
```

> **Impact :** statistiques fiables, réversibles, sans doublon. **Intégrité données ++**

---

### 1.5 🟠 Filtres catalogue : rendu serveur + amélioration progressive

**Problème** — Le filtrage n'existe que dans `apiList()` (JSON). Sans JS, le `<noscript>` soumet vers `/prestations` qui ignore `$_GET` ([ServiceController.php:16](../src/app/Controllers/ServiceController.php)).

**Solution** — `index()` lit les mêmes filtres et appelle `search()`. Le formulaire est un vrai `<form method="get">`. Le JS intercepte et fait du fetch ; sinon le serveur rend la page filtrée.

```php
public function index(): void
{
    $filters = [
        'category'     => $_GET['category']     ?? null,
        'max_price'    => isset($_GET['max_price'])    ? (float) $_GET['max_price']    : null,
        'max_duration' => isset($_GET['max_duration']) ? (int)   $_GET['max_duration'] : null,
    ];
    $services   = $this->services->search($filters);   // même méthode que l'API
    $categories = $this->services->categories();

    $this->view('service/index', compact('services', 'categories', 'filters'));
}
```

```html
<!-- service/index.php : vrai formulaire GET, pré-rempli côté serveur -->
<form id="filters" method="get" action="/prestations" aria-label="Filtrer les prestations">
  <label for="category">Catégorie</label>
  <select id="category" name="category">
    <option value="">Toutes</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= e($c['slug']) ?>" <?= ($filters['category'] === $c['slug']) ? 'selected' : '' ?>>
        <?= e($c['label']) ?>
      </option>
    <?php endforeach; ?>
  </label>
  <noscript><button type="submit">Filtrer</button></noscript>
</form>
```

```js
// app.js : amélioration progressive — on remplace la soumission par du fetch
const form = document.getElementById('filters');
if (form) {
  form.addEventListener('input', debounce(async () => {
    const params = new URLSearchParams(new FormData(form));
    history.replaceState(null, '', '/prestations?' + params);   // URL partageable
    await refreshResults(params);
  }, 250));
}
```

> **Impact :** fonctionne sans JS, URLs filtrées partageables/indexables. **Accessibilité + SEO + UX ++**

---

### 1.6 🔴 Durcissement production : headers, php.ini, cookies

**Problème** — `display_errors=On` en prod, aucun header de sécurité, cookie sans `Secure`.

**Solution — `php.ini` de production séparé :**

```ini
; docker/php/prod.ini  (copié SEULEMENT dans l'image de prod)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log
expose_php = Off
session.cookie_httponly = 1
session.cookie_secure   = 1
session.cookie_samesite = Lax
session.use_strict_mode = 1
opcache.enable = 1
opcache.validate_timestamps = 0   ; prod : code figé, perf max
```

**Headers de sécurité — middleware émis dans le front controller :**

```php
// src/app/Core/SecurityHeaders.php
final class SecurityHeaders
{
    public static function send(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(self), camera=(self)'); // mic/cam pour témoignage vidéo
        header("Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' https://js.stripe.com 'nonce-" . Csp::nonce() . "'; "
            . "style-src 'self' 'unsafe-inline'; "
            . "img-src 'self' data: blob:; "
            . "frame-src https://js.stripe.com; "
            . "connect-src 'self' https://mercure.zenspace.fr; "
            . "object-src 'none'; base-uri 'self'; frame-ancestors 'none'");
        if (env('APP_ENV') === 'prod') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
}
```

**Côté Apache (vhost) en complément :**

```apache
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header unset X-Powered-By
    Header always edit Set-Cookie ^(.*)$ "$1; HttpOnly; Secure; SameSite=Lax"
</IfModule>
```

> **Impact :** protège contre clickjacking, MIME-sniffing, vol de cookie, fuite d'erreurs. **Sécurité +++**

---

### 1.7 🔴 Secrets : suppression du root/root en dur

**Problème** — Fallbacks `root/root` ([docker-compose.yml](../docker-compose.yml), [Database.php:30](../src/app/Core/Database.php)).

**Solution** — Utilisateur MySQL dédié à privilèges minimaux, variables d'env obligatoires (échec explicite si absentes), secrets Docker.

```yaml
# docker-compose.yml (extrait durci)
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/mysql_root
      MYSQL_DATABASE: zenspace
      MYSQL_USER: zenspace_app
      MYSQL_PASSWORD_FILE: /run/secrets/mysql_app
    secrets: [mysql_root, mysql_app]
secrets:
  mysql_root: { file: ./secrets/mysql_root.txt }   # hors git
  mysql_app:  { file: ./secrets/mysql_app.txt }
```

```php
// Database.php — plus aucun fallback root ; on échoue fort si la config manque
private static function env(string $k): string
{
    $v = getenv($k) ?: ($_ENV[$k] ?? null);
    if ($v === null || $v === '') {
        throw new \RuntimeException("Variable d'environnement manquante : $k");
    }
    return $v;
}
```

```sql
-- privilèges minimaux pour l'app (pas de DROP/ALTER/GRANT)
CREATE USER 'zenspace_app'@'%' IDENTIFIED BY '...';
GRANT SELECT, INSERT, UPDATE, DELETE ON zenspace.* TO 'zenspace_app'@'%';
```

> **Impact :** principe du moindre privilège, secrets hors dépôt. **Sécurité +++**

---

### 1.8 🟠 Sessions, déconnexion, rate-limiting (Redis)

**Déconnexion complète & expiration d'inactivité :**

```php
// Auth::logout()
public static function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// App::boot() — timeout d'inactivité (20 min) + rotation périodique
$now = time();
if (isset($_SESSION['last_activity']) && $now - $_SESSION['last_activity'] > 1200) {
    Auth::logout();
    Flash::set('info', 'Session expirée, reconnectez-vous.');
}
$_SESSION['last_activity'] = $now;
if (!isset($_SESSION['created'])) { $_SESSION['created'] = $now; }
elseif ($now - $_SESSION['created'] > 1800) {        // re-génère l'ID toutes les 30 min
    session_regenerate_id(true);
    $_SESSION['created'] = $now;
}
```

**Sessions et cache dans Redis (scalable, expiration native) :**

```ini
; php.ini
session.save_handler = redis
session.save_path    = "tcp://redis:6379?auth=...&database=0"
```

**Rate-limiting par IP *et* par compte (algorithme token-bucket simplifié) :**

```php
// src/app/Core/RateLimiter.php
final class RateLimiter
{
    public function __construct(private \Redis $redis) {}

    /** @return bool true si autorisé, false si quota dépassé */
    public function attempt(string $key, int $max, int $windowSec): bool
    {
        $k     = "rl:$key";
        $count = $this->redis->incr($k);
        if ($count === 1) { $this->redis->expire($k, $windowSec); }
        return $count <= $max;
    }
}

// AuthController::login() — double clé
$ip = $_SERVER['REMOTE_ADDR'];
if (!$this->rl->attempt("login:ip:$ip", 20, 600)            // 20 essais / 10 min / IP
 || !$this->rl->attempt("login:mail:" . sha1($email), 5, 600)) { // 5 essais / 10 min / compte
    http_response_code(429);
    Flash::set('error', 'Trop de tentatives. Réessayez dans quelques minutes.');
    return $this->redirect('/connexion');
}
```

**Invalidation globale après reset réussi :**

```php
// AuthController::reset() — après mise à jour du mot de passe
$this->resets->invalidateAllForUser($user['id']);   // DELETE tous les tokens restants
SessionStore::destroyAllForUser($user['id']);        // purge des sessions Redis de ce user
Flash::set('success', 'Mot de passe modifié. Toutes vos sessions ont été déconnectées.');
```

> **Impact :** anti-bruteforce, sessions maîtrisées, fin du « token zombie ». **Sécurité +++**

---

### 1.9 🟡 Correctifs qualité divers

| Réf. | Problème | Correction |
|------|----------|-----------|
| 405 routeur | Mauvaise méthode → 404 trompeur | Distinguer « chemin connu / méthode interdite » → `405 Method Not Allowed` + header `Allow` (cf. §7.1) |
| Contrainte param URL | `/reserver/abc` → `(int)0` | Regex de route `{id:\d+}` (cf. §7.1) |
| `ServiceRepository::update()` | Colonne `image` jamais mise à jour | Ajouter `image = :image` au `SET` |
| Filtre admin statut | Utilise `$_GET` au lieu de `$_POST` | Champ caché `current_filter` renvoyé dans le formulaire |
| Avis non chargés sur fiche | Import `ReviewRepository` mort | Charger `findValidatedByService($id)` et afficher la section avis |
| Dashboard `findAll()` | Charge toutes les lignes pour compter | `SELECT COUNT(*) ... WHERE status = ?` (`countByStatus`) |
| Hash seed `$2b$` | Générés hors PHP | Régénérer via `password_hash(PASSWORD_BCRYPT, ['cost'=>12])` (préfixe `$2y$`) |

---

## 2. UI/UX & IDENTITÉ VISUELLE

### 2.1 Direction artistique — « Onsen Glass »

Un mélange de **glassmorphism doux** (verre dépoli, profondeur) et de matières organiques (dégradés végétaux, grain subtil). Inspiration : sources thermales japonaises, brume, jade, terre cuite.

### 2.2 Palette (toutes validées WCAG AA ≥ 4.5:1 sur leurs usages texte)

```
  TOKEN              HEX        USAGE                         CONTRASTE
  --sage-900       #1B2D26    Texte principal / titres       15.8:1 sur crème ✅
  --sage-700       #2F4F43    Texte secondaire               9.2:1  ✅
  --jade-500       #2E7D5B    Liens, accents interactifs     4.7:1  sur crème ✅
  --jade-600       #246A4C    Hover liens                    5.9:1  ✅
  --clay-600       #9A5B3B    CTA (terracotta foncé)         4.8:1  blanc dessus ✅
  --cream-50       #FBF8F3    Fond clair                     —
  --mist-100       #EFEAE2    Surfaces / cartes              —
  --gold-500       #B8893A    Étoiles avis (sur foncé)       —
  Dark mode:
  --ink-950        #0E1714    Fond sombre
  --ink-850        #16221E    Surface sombre
  --jade-300       #7FD1A9    Accent sur fond sombre         8.1:1  ✅
```

> Correction directe de l'audit : l'ancien `--accent #c08552` (2.9:1) est remplacé par `--clay-600 #9A5B3B` (4.8:1 avec texte blanc).

### 2.3 Typographie

```css
/* Titres : élégance humaniste — Corps : lisibilité maximale */
--font-display: "Fraunces", "Playfair Display", Georgia, serif;   /* titres, ports optiques */
--font-body:    "Inter", "Segoe UI", system-ui, sans-serif;       /* texte courant */
/* Échelle modulaire 1.250 (Major Third) */
--step--1: clamp(.83rem, .79rem + .2vw, .94rem);
--step-0:  clamp(1rem,   .95rem + .25vw, 1.13rem);
--step-1:  clamp(1.25rem, 1.16rem + .45vw, 1.5rem);
--step-3:  clamp(2.44rem, 2.1rem + 1.7vw, 3.5rem);   /* hero */
```

### 2.4 Design tokens & thème sombre

```css
:root {
  --bg: var(--cream-50); --surface: var(--mist-100); --text: var(--sage-900);
  --link: var(--jade-500); --cta-bg: var(--clay-600); --cta-text: #fff;
  --radius: 16px; --shadow-sm: 0 1px 3px rgba(27,45,38,.08);
  --shadow-glass: 0 8px 32px rgba(27,45,38,.12);
  --glass-bg: rgba(255,255,255,.55); --glass-border: rgba(255,255,255,.6);
  --ease: cubic-bezier(.4,0,.2,1);
}
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    --bg: var(--ink-950); --surface: var(--ink-850); --text: #E8F0EC;
    --link: var(--jade-300); --glass-bg: rgba(22,34,30,.55);
    --glass-border: rgba(127,209,169,.18);
  }
}
[data-theme="dark"] { /* override manuel via bouton, mémorisé en localStorage */ }
```

```js
// bascule de thème, respecte la préférence système puis le choix utilisateur
const saved = localStorage.getItem('theme');
if (saved) document.documentElement.dataset.theme = saved;
toggle.addEventListener('click', () => {
  const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  document.documentElement.dataset.theme = next;
  localStorage.setItem('theme', next);
});
```

### 2.5 Guide de composants (extraits)

**Carte verre (glass card) :**
```css
.card {
  background: var(--glass-bg);
  backdrop-filter: blur(12px) saturate(120%);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-glass);
  transition: transform .35s var(--ease), box-shadow .35s var(--ease);
}
.card:hover { transform: translateY(-4px); box-shadow: 0 16px 48px rgba(27,45,38,.18); }
@media (prefers-reduced-motion: reduce) { .card { transition: none; } .card:hover { transform: none; } }
```

**Bouton (avec focus AA) :**
```css
.btn { font: 600 var(--step-0)/1 var(--font-body); padding: .8em 1.4em;
       border-radius: 999px; transition: background .25s var(--ease), transform .15s var(--ease); }
.btn-cta { background: var(--cta-bg); color: var(--cta-text); }
.btn-cta:hover { background: #864E32; }
.btn:active { transform: scale(.97); }
/* Focus visible global — corrige l'audit (manquait sur liens/boutons) */
a:focus-visible, .btn:focus-visible, [tabindex]:focus-visible, input:focus-visible {
  outline: 3px solid var(--jade-600); outline-offset: 2px; border-radius: 6px;
}
a { text-decoration: underline; text-underline-offset: .15em; }  /* lien distinguable sans couleur */
```

**Modale accessible (focus trap, `aria-modal`) :** dialogue natif `<dialog>` + `inert` sur le fond.

### 2.6 Navigation & transitions de page (Swup)

Sticky header en verre qui se condense au scroll. Transitions douces entre pages sans rechargement complet, avec fallback SSR intégral.

```js
import Swup from 'swup';
const swup = new Swup({
  containers: ['#main'],
  animationSelector: '[class*="transition-"]',
});
// fondu + léger glissement, désactivé si reduced-motion
```
```css
.transition-fade { transition: opacity .4s var(--ease), transform .4s var(--ease); }
html.is-animating .transition-fade { opacity: 0; transform: translateY(8px); }
@media (prefers-reduced-motion: reduce) { .transition-fade { transition: none; } }
```

### 2.7 Micro-interactions & icônes animées (Lottie)

- Icône **feuille qui se déplie** au survol des catégories.
- **Coche d'eau** (ripple) à la confirmation de réservation.
- Lottie chargé en lazy, et **remplacé par une image statique** si `prefers-reduced-motion`.

```js
import lottie from 'lottie-web/build/player/lottie_light';   // version légère
if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
  lottie.loadAnimation({ container: el, path: '/assets/lottie/leaf.json', loop: false, autoplay: false });
}
```

> **Impact :** identité premium, cohérence, sensation de calme, conversion. **UX +++ / Image de marque +++**

---

## 3. ANIMATIONS & IMMERSION 3D

### 3.1 Hero immersif — « Le Jardin de particules »

Une scène Three.js en fond de page d'accueil : un nuage de **3000 particules** formant une silhouette organique (feuille / onde), qui ondule lentement et **réagit à la souris** (parallaxe + répulsion douce autour du curseur).

**Pseudo-code narratif :**
```
INIT
  scène, caméra perspective, renderer WebGL (alpha, antialias)
  géométrie : BufferGeometry de N points sur une surface paramétrique (bruit de Perlin)
  matériau : PointsMaterial, couleur jade→crème en dégradé, taille atténuée par distance
  shader léger : déplacement vertical = sin(temps + position.x) * amplitude

BOUCLE (si !reduced-motion)
  temps += delta
  pour chaque particule :
      offset = bruitSimplex(x*0.3, y*0.3, temps*0.2)
      position.z = offset * amplitude
      vecteurSouris = position - sourisMonde
      si |vecteurSouris| < rayon : position += normalize(vecteurSouris) * forceRépulsion
  caméra.lookAt(centre) ; parallaxe douce vers (souris.x, souris.y) * 0.05
  renderer.render()

INTERACTION
  pointermove → met à jour sourisMonde (raycaster sur plan z=0)
  resize → ajuste aspect + pixelRatio plafonné à 2 (perf mobile)
```

```js
// extrait clé : répulsion au curseur
const dir = new THREE.Vector3().subVectors(p, mouseWorld);
const d = dir.length();
if (d < REPULSE_RADIUS) {
  p.addScaledVector(dir.normalize(), (REPULSE_RADIUS - d) * 0.04);
}
```

### 3.2 Chargement intelligent (corrige l'audit : Three.js même en reduced-motion)

```js
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const heroCanvas = document.getElementById('hero-canvas');
if (heroCanvas && !reduce && 'IntersectionObserver' in window) {
  new IntersectionObserver(async ([e], obs) => {
    if (!e.isIntersecting) return;
    obs.disconnect();
    const { initHero } = await import('./hero3d.js');   // code-splitting : Three.js chargé à la demande
    try { initHero(heroCanvas); } catch { heroCanvas.classList.add('hero--fallback'); }
  }).observe(heroCanvas);
}
// reduced-motion OU pas de WebGL → fond dégradé CSS animé très lentement (ci-dessous)
```
```css
.hero--fallback, .hero[data-reduced] {
  background: radial-gradient(120% 120% at 30% 20%, var(--jade-300) 0%, var(--sage-700) 55%, var(--ink-950) 100%);
}
```

### 3.3 Transition entre prestations — morph de couleur 3D

Au clic sur une carte prestation, un **plan shader** prend la couleur dominante de la prestation et « inonde » l'écran (transition de type *liquid wipe*) avant d'afficher la fiche, synchronisé avec Swup.

```glsl
// fragment shader (idée) : vague de couleur pilotée par uProgress (0→1)
float wave = smoothstep(uProgress - 0.1, uProgress + 0.1, vUv.y + sin(vUv.x*6.0)*0.05);
gl_FragColor = mix(uColorFrom, uColorTo, wave);
```

### 3.4 🟢 Mock-up « Réalité augmentée » sur fiche prestation

Bouton « Voir dans votre espace (AR) » → ouvre un overlay. Sur mobile compatible, lance **AR.js / model-viewer** avec un modèle 3D d'objet d'ambiance (bougie, futon). Sinon, affiche une vidéo 360°.

```html
<!-- <model-viewer> = web component, AR natif iOS/Android, fallback 3D sinon -->
<model-viewer src="/assets/3d/zen-room.glb" ios-src="/assets/3d/zen-room.usdz"
              ar ar-modes="webxr scene-viewer quick-look" camera-controls
              alt="Aperçu 3D de l'espace de la prestation" poster="/assets/img/ar-poster.webp">
  <button slot="ar-button" class="btn btn-cta">📱 Activer la réalité augmentée</button>
</model-viewer>
```

> **Impact :** effet « waouh », différenciation forte, temps passé sur page. **Engagement +++**

---

## 4. NOUVELLES FONCTIONNALITÉS INCROYABLES

### 4.1 🟠 Réservation immersive — calendrier interactif (FullCalendar)

Vue **semaine / mois / liste**, créneaux disponibles en vert jade, drag pour sélectionner, validation des conflits en temps réel via Ajax (la vérité reste serveur).

```js
import { Calendar } from '@fullcalendar/core';
const calendar = new Calendar(el, {
  initialView: 'timeGridWeek',
  selectable: true, locale: 'fr',
  events: '/api/disponibilites?service=' + serviceId,
  select: async (info) => {
    const r = await fetch('/api/verifier-creneau', {
      method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-Token': CSRF},
      body: JSON.stringify({ service: serviceId, start: info.startStr }),
    });
    const { available, reason } = await r.json();
    if (!available) { toast.warn(reason); calendar.unselect(); }
    else openBookingDrawer(info.startStr);     // tiroir de confirmation
  },
});
```
```php
// endpoint serveur — source de vérité (ne jamais faire confiance au client)
public function checkSlot(): void
{
    Csrf::validateHeader();
    $data = json_decode(file_get_contents('php://input'), true);
    $available = !$this->bookings->isSlotTaken((int)$data['service'], $data['start']);
    $this->json(['available' => $available, 'reason' => $available ? null : 'Créneau déjà réservé']);
}
```

### 4.2 🟡 Chat temps réel client ↔ praticien (Mercure)

Mercure (protocole SSE, plus simple et robuste que des WebSockets bruts en PHP) pour poser une question avant réservation. Notifications toast.

```
Client ───POST /chat/message──▶ PHP ──publish──▶ Mercure Hub ──SSE──▶ Praticien (abonné au topic)
                                                          └──SSE──▶ Client (accusé/réponse)
```
```js
const es = new EventSource('https://mercure.zenspace.fr/.well-known/mercure?topic=' +
  encodeURIComponent('/chat/' + conversationId), { withCredentials: true });
es.onmessage = (e) => appendMessage(JSON.parse(e.data));
```

### 4.3 🟡 QR code par réservation

Généré à la confirmation, affiché dans l'espace client et l'e-mail, scanné à l'entrée du studio (check-in).

```php
use Endroid\QrCode\QrCode;
$payload = json_encode(['b' => $bookingId, 'sig' => hash_hmac('sha256', (string)$bookingId, env('APP_SECRET'))]);
$qr = QrCode::create($payload);                      // signature HMAC anti-falsification
// rendu PNG inline ou data-URI dans la vue / l'e-mail
```
```php
// check-in : on vérifie la signature avant de marquer présent
$ok = hash_equals(hash_hmac('sha256', (string)$b, env('APP_SECRET')), $sig);
```

### 4.4 🟠 Paiement en ligne — Stripe Checkout + factures PDF

```php
\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
$session = \Stripe\Checkout\Session::create([
  'mode' => 'payment',
  'line_items' => [[
    'price_data' => [
      'currency' => 'eur',
      'product_data' => ['name' => $service['title']],
      'unit_amount' => (int) round($service['price'] * 100),   // centimes, prix serveur
    ], 'quantity' => 1,
  ]],
  'success_url' => env('APP_URL').'/reservation/success?session_id={CHECKOUT_SESSION_ID}',
  'cancel_url'  => env('APP_URL').'/reserver/'.$service['id'],
  'metadata'    => ['booking_id' => $bookingId],
]);
return $this->redirect($session->url, 303);
```
```php
// Webhook Stripe — confirme le paiement de façon fiable (vérif. signature)
$event = \Stripe\Webhook::constructEvent($payload, $sigHeader, env('STRIPE_WEBHOOK_SECRET'));
if ($event->type === 'checkout.session.completed') {
    $this->bookings->markPaid((int)$event->data->object->metadata->booking_id);
    $this->invoices->generatePdf($bookingId);    // facture PDF (Dompdf) + e-mail
}
```
Remboursement (annulation éligible) : `\Stripe\Refund::create([...])` + maj statut. Factures PDF via **Dompdf** depuis un gabarit HTML.

### 4.5 🟡 Système de fidélité

`+10 points` par réservation honorée, paliers et badges animés (Lottie). Stockage relationnel.

```sql
CREATE TABLE loyalty_ledger (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  delta INT NOT NULL,              -- + gain / - dépense
  reason VARCHAR(120) NOT NULL,
  booking_id INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_loy_user FOREIGN KEY (user_id) REFERENCES user(id),
  INDEX idx_loy_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- solde = SELECT COALESCE(SUM(delta),0) FROM loyalty_ledger WHERE user_id = ?
```
Récompenses : code promo à -10 % à 100 points, badge « Habitué·e » à 5 séances, etc.

### 4.6 🟡 Espace communautaire — mur d'avis enrichi

Avis avec **photos** et **témoignage vidéo** (upload sécurisé OU enregistrement via `MediaRecorder`), modéré avant publication. Stockage hors webroot, validation MIME stricte.

```js
// enregistrement vidéo navigateur (consentement explicite)
const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
const rec = new MediaRecorder(stream, { mimeType: 'video/webm' });
```
```php
// upload durci : type réel vérifié, nom aléatoire, hors /public
$mime = mime_content_type($tmp);
if (!in_array($mime, ['image/jpeg','image/png','image/webp','video/webm','video/mp4'], true)) {
    throw new ValidationException('Format non autorisé');
}
$name = bin2hex(random_bytes(16)) . '.' . MimeMap::ext($mime);
move_uploaded_file($tmp, STORAGE_PATH . "/media/$name");  // servi via contrôleur, jamais en direct
```

### 4.7 🟢 Blog / Magazine bien-être

Articles (éditeur enrichi côté admin), catégories, commentaires modérés, partage social, JSON-LD `Article`. Boost SEO majeur (longue traîne : « bienfaits du massage californien »…).

### 4.8 🟠 Tableau de bord admin avancé

- **Chart.js** : courbe de revenus, barres par prestation, donut par catégorie.
- **Heatmap** des réservations (jour × heure) pour optimiser les plannings.
- **Export CSV** (revenus, réservations, clients).
- **Journal d'audit** des actions admin → stocké dans MongoDB (`audit_log`), immuable.

```php
// journal d'audit append-only
Mongo::insert('audit_log', [
  'actor_id' => $admin['id'], 'action' => 'service.delete',
  'target' => $serviceId, 'ip' => $_SERVER['REMOTE_ADDR'], 'at' => date('c'),
]);
```
```php
// agrégation Mongo native (corrige l'audit : agrégation PHP → pipeline $group)
$pipeline = [
  ['$match' => $filter],
  ['$group' => ['_id' => '$service_title', 'total' => ['$sum' => '$amount'], 'count' => ['$sum' => 1]]],
  ['$sort'  => ['total' => -1]],
];
```

### 4.9 🟢 IDÉE SIGNATURE — Mode « Méditation guidée »

Une page `/mediter` immersive : animation Three.js d'une **onde respiratoire** (sphère qui se dilate/contracte au rythme 4-7-8), **sons binauraux** (Web Audio API : deux oscillateurs à fréquences proches → battement perçu), minuteur, voix off optionnelle.

```js
// battement binaural via Web Audio API
const ctx = new AudioContext();
const base = 200, beat = 6;                 // 6 Hz → ondes thêta (relaxation)
[base, base + beat].forEach((f, i) => {
  const osc = ctx.createOscillator(), pan = new StereoPannerNode(ctx, { pan: i ? 1 : -1 });
  const gain = ctx.createGain(); gain.gain.value = 0.04;
  osc.frequency.value = f; osc.connect(gain).connect(pan).connect(ctx.destination); osc.start();
});
```
```
Animation respiration (synchronisée à l'audio) :
  inspire 4s → sphère grossit (scale 1→1.6), couleur jade clair
  retiens 7s → légère pulsation
  expire  8s → sphère rétrécit, couleur sage profond
  + texte vocalisé (aria-live) "Inspirez… Retenez… Expirez…"
Fallback reduced-motion : cercle CSS qui change d'opacité + consignes textuelles, audio toujours dispo (avec bouton).
```

> **Impact :** fonctionnalité unique sur le marché, fidélisation, temps de session, viralité. **Différenciation +++**

---

## 5. ACCESSIBILITÉ — RGAA niveau AA

### 5.1 Tableau de conformité (critères clés → action)

| Critère RGAA | Exigence | Action concrète dans ZenSpace |
|--------------|----------|-------------------------------|
| **1.1 Images** | Alternative pertinente | `alt` descriptif sur médias informatifs ; `alt=""` sur images décoratives (cartes redondantes avec le titre). |
| **3.2 / 3.3 Contraste** | Texte ≥ 4.5:1 | Palette §2.2 entièrement validée ; fin du terracotta 2.9:1. |
| **7.1 Scripts** | Compatibles AT | Composants JS avec rôles ARIA, fonctionnent au clavier. |
| **8.x Structure** | HTML sémantique | `header/nav/main/footer`, `h1` unique/page, hiérarchie sans saut (footer en `h2`). |
| **10.x Présentation** | Indépendance couleur | Liens **soulignés** (pas seulement colorés). |
| **11.x Formulaires** | Labels + erreurs | `label[for]`, `aria-describedby` vers le message d'erreur, `aria-invalid`. |
| **12.x Navigation** | Skip-link, focus | Skip-link déjà présent ; **`:focus-visible` ajouté partout** (§2.5). |
| **13.x Consultation** | Messages dynamiques | `aria-live` sur flash et résultats. |

### 5.2 Messages flash & résultats vocalisés (corrige l'audit)

```html
<div class="flash-zone" role="status" aria-live="polite" aria-atomic="true"><!-- info/succès --></div>
<div class="flash-zone--error" role="alert"><!-- erreurs --></div>
<div id="results" role="region" aria-label="Résultats" aria-live="polite" aria-busy="false"><!-- … --></div>
```

### 5.3 Formulaires accessibles (erreurs associées)

```html
<label for="email">E-mail</label>
<input id="email" name="email" type="email" required
       aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"
       aria-describedby="email-err">
<p id="email-err" class="field-error" role="alert">
  <?= isset($errors['email']) ? e($errors['email']) : '' ?>
</p>
```

### 5.4 Médias

Vidéos témoignages : pistes de **sous-titres** (`<track kind="captions" srclang="fr">`), contrôles clavier, pas d'autoplay sonore. Audio méditation : transcription des consignes.

> **Impact :** conformité légale (loi 2005 / RGAA), audience élargie, point fort soutenance DWWM. **Accessibilité +++**

---

## 6. SEO & PERFORMANCE

### 6.1 Meta dynamiques par page

```php
// chaque contrôleur fournit son SEO
$this->view('service/show', [
  'service' => $s,
  'seo' => [
    'title'       => $s['title'] . ' — ZenSpace',
    'description' => Str::truncate(strip_tags($s['description']), 155),
    'image'       => env('APP_URL') . '/assets/img/services/' . $s['image'],
    'canonical'   => env('APP_URL') . '/prestation/' . $s['slug'],
  ],
]);
```
```html
<!-- layout.php : Open Graph + Twitter + canonical -->
<link rel="canonical" href="<?= e($seo['canonical'] ?? env('APP_URL').$_SERVER['REQUEST_URI']) ?>">
<meta name="description" content="<?= e($seo['description'] ?? $defaultDesc) ?>">
<meta property="og:title" content="<?= e($seo['title'] ?? 'ZenSpace') ?>">
<meta property="og:description" content="<?= e($seo['description'] ?? $defaultDesc) ?>">
<meta property="og:image" content="<?= e($seo['image'] ?? env('APP_URL').'/assets/img/og-default.jpg') ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
```

### 6.2 Données structurées JSON-LD

```php
// Service + Review + BreadcrumbList sur la fiche prestation
$jsonld = [
  '@context' => 'https://schema.org', '@type' => 'Service',
  'name' => $s['title'], 'description' => strip_tags($s['description']),
  'provider' => ['@type' => 'HealthAndBeautyBusiness', 'name' => 'ZenSpace'],
  'offers' => ['@type' => 'Offer', 'price' => $s['price'], 'priceCurrency' => 'EUR'],
  'aggregateRating' => $avg ? ['@type'=>'AggregateRating','ratingValue'=>$avg,'reviewCount'=>$count] : null,
];
echo '<script type="application/ld+json">' . json_encode(array_filter($jsonld), JSON_UNESCAPED_SLASHES) . '</script>';
```
Types à déployer : `Organization` (accueil), `Service`, `Review`/`AggregateRating`, `Event` (réservation confirmée), `BreadcrumbList`, `FAQPage` (page FAQ), `Article` (blog).

### 6.3 Images modernes (corrige JPEG lourds)

Pipeline de build qui génère WebP/AVIF + plusieurs largeurs, et balise `<picture>` :

```html
<picture>
  <source type="image/avif" srcset="/img/hero-480.avif 480w, /img/hero-960.avif 960w, /img/hero-1440.avif 1440w" sizes="100vw">
  <source type="image/webp" srcset="/img/hero-480.webp 480w, /img/hero-960.webp 960w, /img/hero-1440.webp 1440w" sizes="100vw">
  <img src="/img/hero-960.jpg" width="1440" height="810" alt="" loading="lazy" decoding="async">
</picture>
```
```bash
# génération (script de build / Makefile)
cwebp -q 80 hero.jpg -o hero-960.webp
avifenc --min 24 --max 32 hero.jpg hero-960.avif
```

### 6.4 Chargement & cache

- **Code splitting** : Three.js, FullCalendar, Lottie, Chart.js chargés en `import()` dynamique à la demande (cf. §3.2).
- **OPcache** activé, `validate_timestamps=0` en prod.
- **Redis** pour sessions + cache applicatif (catalogue, catégories).
- **ETag / Cache-Control** sur les assets statiques :

```apache
<IfModule mod_headers.c>
  <FilesMatch "\.(css|js|webp|avif|woff2)$">
    Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>
</IfModule>
```
- **Préchargement** des polices critiques : `<link rel="preload" as="font" type="font/woff2" crossorigin>`.

### 6.5 Objectif Lighthouse ≥ 95 mobile

```
Levier                          Gain estimé
─────────────────────────────────────────────
Three.js lazy + splitté         +25 perf
WebP/AVIF + srcset + dimensions  +15 perf, CLS≈0
OPcache + Redis cache            TTFB −40%
Polices preload + display:swap   −0.3s LCP
JS différé / modules             −0.4s TBT
```

> **Impact :** trafic organique, partage social soigné, conversion mobile. **SEO +++ / Perf +++**

---

## 7. ARCHITECTURE & QUALITÉ DE CODE

### 7.1 Routeur durci (contraintes regex, 405, groupes)

```php
$router->get('/reserver/{id:\d+}', [BookingController::class, 'form']);   // {id} numérique only
$router->get('/prestation/{slug:[a-z0-9\-]+}', [ServiceController::class, 'show']);

// dispatch : distinguer 404 (chemin inconnu) de 405 (méthode interdite)
public function dispatch(string $method, string $uri): void
{
    $path = parse_url($uri, PHP_URL_PATH);
    $matchedPathButNotMethod = false;
    foreach ($this->routes as $route) {
        if (preg_match($route->pattern, $path, $m)) {
            if ($route->method !== $method) { $matchedPathButNotMethod = true; continue; }
            return $route->run(array_slice($m, 1));
        }
    }
    if ($matchedPathButNotMethod) {
        header('Allow: ' . implode(', ', $this->allowedMethods($path)));
        throw new HttpException(405);
    }
    throw new HttpException(404);
}
```

### 7.2 Handler d'exception global + pages d'erreur stylisées

```php
// index.php
set_exception_handler(function (\Throwable $e) {
    $code = $e instanceof HttpException ? $e->getStatus() : 500;
    http_response_code($code);
    if (env('APP_ENV') !== 'prod') { error_log((string) $e); }
    if ($code === 500) { error_log('[500] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()); }
    View::renderError($code);          // 400/403/404/405/500 — gabarits soignés et accessibles
});
```
Pages d'erreur : illustration zen, message rassurant, lien retour, recherche. Cohérentes avec le design system.

### 7.3 Injection de dépendances (conteneur léger)

```php
// Container minimal — découple contrôleurs des dépendances concrètes (DB, Redis, Mailer)
$c = new Container();
$c->singleton(\PDO::class, fn() => Database::connection());
$c->singleton(\Redis::class, fn() => RedisFactory::make());
$c->bind(RateLimiter::class, fn($c) => new RateLimiter($c->get(\Redis::class)));
// le routeur résout les contrôleurs via le conteneur → testabilité accrue
```

### 7.4 Repository pattern étendu

`BaseRepository` abstrait (factorise `PDO`, helpers `find/all/count`). Méthodes `COUNT(*)` partout où on ne fait que compter (dashboard). Value Objects pour les statuts (enum PHP 8.1).

```php
enum BookingStatus: string {
  case Pending = 'pending'; case Confirmed = 'confirmed';
  case Completed = 'completed'; case Cancelled = 'cancelled';
  public function label(): string { /* libellé FR */ }
}
```

### 7.5 Tests

```php
// PHPUnit — flux critiques (exemple : pas de double CA)
public function testCompletedTwiceDoesNotDoubleCountRevenue(): void
{
    $this->markCompleted($bookingId);
    $this->markCompleted($bookingId);                 // 2e fois = no-op
    $this->assertSame(1, $this->mongoCount('revenue', ['booking_id' => $bookingId]));
}
```
```js
// Cypress — parcours réservation + paiement (mode test Stripe)
it('réserve un créneau et paie', () => {
  cy.login('camille@example.com', '…');
  cy.visit('/prestation/massage-californien');
  cy.contains('Réserver').click();
  cy.get('[data-slot="10:00"]').click();
  cy.contains('Payer').click();
  cy.origin('https://checkout.stripe.com', () => { /* carte test 4242… */ });
  cy.contains('Réservation confirmée');
});
```

### 7.6 CI/CD — GitHub Actions

```yaml
name: ci
on: [push, pull_request]
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.2', extensions: pdo_mysql, mongodb, redis, coverage: xdebug }
      - run: composer install --no-interaction
      - run: vendor/bin/phpcs --standard=PSR12 src/        # lint
      - run: vendor/bin/phpstan analyse src/ --level=6     # analyse statique
      - run: vendor/bin/phpunit --coverage-text            # tests unitaires
  e2e:
    needs: quality
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: docker compose up -d
      - run: npx cypress run
  deploy:
    needs: [quality, e2e]
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - run: ./scripts/deploy.sh   # build images prod (prod.ini), push, migrate, switch
```

> **Impact :** moins de régressions, déploiements sûrs, qualité mesurable. **Maintenabilité +++**

---

## 8. Roadmap, matrice effort/impact & KPIs

### 8.1 Matrice priorité × effort

```
IMPACT
  ▲
H │  [Stripe]        [Calendrier]      [Hero 3D]
  │  [Sécurité 🔴]   [Design system]   [Méditation]
  │  [RGAA]          [SEO/JSON-LD]
M │  [Rate-limit]    [Chat Mercure]    [Blog]
  │  [Handler excep] [Fidélité]        [AR mock]
B │  [QR code]       [Heatmap admin]
  └────────────────────────────────────────────▶ EFFORT
        Faible            Moyen            Élevé

QUICK WINS (faire en premier) : Sécurité 🔴, RGAA focus/contraste, SEO meta, handler d'exception.
GROS PARIS (planifier)        : Hero 3D, Calendrier, Stripe, Méditation.
```

### 8.2 Plan par phases (rappel)

| Phase | Contenu | Sortie |
|-------|---------|--------|
| **1 — Assainir** | Tous les 🔴 + 🟠 sécurité, handler global, prod config | Site sûr, déployable |
| **2 — Habiller** | Design system, dark mode, RGAA AA, SEO/JSON-LD, images WebP | Site beau, accessible, référencé |
| **3 — Immerger** | Hero 3D splitté, transitions Swup, calendrier réservation | Effet « waouh », réservation fluide |
| **4 — Monétiser** | Stripe + factures, chat Mercure, QR, fidélité | Revenus, engagement |
| **5 — Rayonner** | Communauté, blog, méditation guidée, admin avancé, tests/CI | Référence du secteur |

### 8.3 KPIs de succès

| Indicateur | Avant | Cible |
|------------|-------|-------|
| Failles critiques | 4+ | **0** |
| Lighthouse mobile (perf) | ~60 | **≥ 95** |
| Conformité RGAA | partielle | **AA** |
| Taux de conversion réservation | référence | **+30 %** |
| Temps moyen sur site | référence | **+50 %** |
| Couverture de tests (flux critiques) | 0 % | **≥ 70 %** |

---

## ✨ Conclusion

ZenSpace 2.0 ne se contente pas de corriger : il **transforme**. D'abord un socle sain et sûr (Phase 1), puis une identité visuelle apaisante et accessible (Phase 2), une immersion 3D mémorable (Phase 3), une monétisation et un engagement réels (Phase 4), et enfin une communauté vivante avec une fonctionnalité signature unique — la méditation guidée binaurale (Phase 5).

Chaque proposition reste dans l'écosystème actuel (PHP/MySQL/MongoDB/Three.js, + Redis/Mercure/Stripe), repose sur l'amélioration progressive, et place l'utilisateur — sa sécurité, son confort, sa sérénité — au centre.

> **Prochaine action recommandée :** lancer la **Phase 1** (sécurité) dès maintenant. Je peux commencer par implémenter les 7 correctifs 🔴 dans le code réel sur une branche dédiée.
