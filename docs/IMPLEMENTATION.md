# 🛠️ ZenSpace 2.0 — Journal d'implémentation

Ce document récapitule ce qui a été **réellement implémenté et vérifié** sur l'application
(voir le plan complet dans [REFONTE_ZENSPACE.md](REFONTE_ZENSPACE.md)).

> Légende : ✅ implémenté & vérifié sur l'app en cours d'exécution · 🧩 scaffold (code complet, nécessite une config/clé externe pour être 100 % live)

---

## Phase 1 — Sécurité & bugs ✅

| Correctif | Statut | Vérification |
|-----------|--------|--------------|
| Reset par e-mail (token jamais exposé) | ✅ | POST renvoie 302, **0** occurrence du lien dans la réponse HTTP ; `.eml` écrit dans `src/storage/mail/` |
| Injection NoSQL (`from`/`to`) | ✅ | Validation stricte `Y-m-d` + agrégation Mongo `$group`/`$sum` |
| Créneau annulé → fatal | ✅ | Colonne générée `slot_key` (NULL si annulé) + `try/catch` PDO. Test SQL : rebook d'un créneau annulé **autorisé**, doublon actif **bloqué (1062)** |
| Double comptage du CA | ✅ | `Mongo::upsert()` idempotent + garde sur transition `!= completed` |
| Filtres catalogue sans JS | ✅ | `ServiceController::index()` lit `$_GET` et appelle `search()` ; `<form method="get">` |
| Erreurs/headers/cookies prod | ✅ | `prod.ini` + `SecurityHeaders` (CSP, X-Frame-Options, nosniff…) ; cookie `Secure`/`HttpOnly`/`SameSite` ; headers **non dupliqués** |
| Secrets MySQL | ✅ | User dédié `zenspace_app` (SELECT/INSERT/UPDATE/DELETE), fallback root supprimé, `.env` basculé |
| Rate-limiting IP + compte | ✅ | `RateLimiter` (pilote fichier par défaut, Redis prêt) ; compteurs dans `src/storage/ratelimit/` |
| Expiration de session (20 min) | ✅ | `App::enforceSessionLifetime()` + rotation d'ID toutes les 30 min |
| Déconnexion complète | ✅ | Suppression du cookie + `session_destroy()` |
| Invalidation des tokens post-reset | ✅ | `PasswordResetRepository::invalidateAllForUser()` |

## Phase 2 — Design (version FINALE : minimaliste « magazine ») ✅
- `app.css` **réécrit** dans une direction **sobre et éditoriale** (voir [DESIGN.md](DESIGN.md)) :
  palette neutre (ivoire/encre/sauge, toutes AA), typographie **Fraunces/Inter**,
  hero photo plein cadre + un seul CTA, cartes-liens à filet fin, boutons sobres.
- **Retiré** : glassmorphism, thème sombre, pétales animées, animations d'apparition.
- RGAA : `:focus-visible` global, `aria-live` sur flash, `aria-describedby`/`aria-invalid`
  sur les formulaires, skip-link, contrastes AA. Cache-busting CSS (`?v=mtime`).

## Phase 3 — Animations & 3D ❌ RETIRÉ
- Le hero 3D (Three.js), la page **Méditation** (sons binauraux) et l'aperçu **3D /
  réalité augmentée** des fiches ont été **supprimés** (jugés « gadget »). Fichiers
  retirés : `hero3d.js`, `ar-preview.js`, `meditation.js`, `lantern.glb`, `assets/3d/`.
- Remplacés par un **planning de disponibilités** utile sur la fiche prestation
  (créneaux libres cliquables → réservation pré-remplie). Voir [JOURNAL.md](JOURNAL.md) §7.

## Phase 4 — Nouvelles fonctionnalités
| Fonctionnalité | Statut | Détail |
|----------------|--------|--------|
| Planning de disponibilités | ✅ | Fiche prestation : 7 jours d'ouverture, créneaux libres/réservés/passés, clic → réservation pré-remplie |
| Magazine / blog | ✅ | `/magazine` + `/magazine/{slug}`, table `article`, 3 articles seedés, JSON-LD Article |
| Dashboard admin Chart.js | ✅ | Graphique CA par prestation (`/admin/statistiques`), tableau de repli accessible |
| Journal d'audit | ✅ | `AuditLog::record()` → Mongo `audit_log`, câblé sur suppression service / désactivation employé |
| Fidélité | ✅ (socle) | `LoyaltyRepository` (solde/historique/award) + partial badge ; attribution à brancher dans le flux |
| QR de réservation | 🧩 | `/reservation/{id}/qr` signé HMAC, rendu via lib `qrcode` (CDN) |
| Paiement Stripe | 🧩 | `PaymentController` rend une démo tant que `STRIPE_SECRET` est vide ; code réel en commentaire |
| Chat temps réel (Mercure) | 🧩 | `/chat` page de démo + extrait `EventSource`/Mercure documenté |

## Phase 6 — SEO & performance ✅
- `Seo::tags()` : `<title>`, meta description **par page**, Open Graph, Twitter Card, canonical.
- **JSON-LD** : Organization (accueil), Service + AggregateRating (fiche prestation), Article (blog).
- Cache long sur les assets (Apache) + cache-busting CSS.
- 🧩 Reste : génération AVIF/WebP + `srcset` (pipeline d'images à ajouter).

## Phase 7 — Architecture & qualité ✅
- Routeur : contraintes regex (`{id:\d+}`, `{slug:[a-z0-9\-]+}`) + **405** avec en-tête `Allow`.
- Handler d'exception global + pages d'erreur stylisées (400/403/404/405/419/429/500).
- `BaseRepository`, conteneur DI (`Container`).
- **PHPUnit** : 14 tests / 27 assertions ✅ (Router, RateLimiter, Seo, Validator).
- **Cypress** : smoke + parcours connexion.
- **CI GitHub Actions** : lint PHP → PHPUnit → e2e Docker+Cypress → déploiement (gabarit).

---

## Comment activer les options de production

```bash
# 1) Compte MySQL dédié (déjà créé en dev). En prod : secrets Docker.
# 2) Redis pour le rate-limiting / sessions :
#    .env -> RATE_LIMIT_DRIVER=redis
# 3) E-mails réels :
#    .env -> MAIL_TRANSPORT=mail (ou brancher un relais SMTP / PHPMailer)
# 4) Image de production (erreurs masquées, OPcache figé) :
#    docker build avec docker/php/prod.ini à la place de php.ini
# 5) Stripe : composer require stripe/stripe-php + STRIPE_SECRET/STRIPE_WEBHOOK_SECRET
# 6) Migration BDD : db/migrations/2026_06_01_hardening.sql
```

## Tests

```bash
composer install
vendor/bin/phpunit            # tests unitaires
npx cypress run               # e2e (app démarrée via docker compose)
```
