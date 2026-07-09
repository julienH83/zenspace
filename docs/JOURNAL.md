# 📔 Journal des évolutions — ZenSpace

Historique complet et chronologique de tout ce qui a été réalisé. Sert de mémoire
du projet (ce qui a été ajouté, modifié, puis parfois retiré, et pourquoi).

---

## 0. Mise en route — correction du démarrage Docker

**Problème** : `localhost:8080` renvoyait un 404 Apache. Le conteneur `zenspace_web`
tournait depuis un **ancien emplacement** (`C:\Users\Julie\reservation-prestations\src`,
dossier vide) au lieu de `C:\Users\Julie\workspace\reservation-prestations\src`.
**Correctif** : recréation des conteneurs depuis le bon dossier (`docker compose up -d --force-recreate`).
Le volume pointe désormais sur le bon `src`, l'application répond en 200.

---

## 1. Audit complet

Audit en 4 axes (sécurité, architecture, base de données, front/accessibilité).
Bilan : base **saine et au-dessus du niveau attendu**, mais plusieurs failles et bugs.
A débouché sur le plan de refonte ([REFONTE_ZENSPACE.md](REFONTE_ZENSPACE.md)).

---

## 2. Phase 1 — Sécurité & correction des bugs

Tous corrigés et vérifiés sur l'app :

1. **Reset de mot de passe par e-mail** : le lien (avec jeton) n'est plus affiché en
   flash mais envoyé par e-mail (transport `log` → `storage/mail/*.eml` en dev).
   Nouvelle classe `App\Core\Mailer`. Vue `emails/reset.php`.
2. **Injection NoSQL** (`StatsController`) : validation stricte des dates `Y-m-d` +
   agrégation Mongo native (`$group`/`$sum`).
3. **Créneau annulé → erreur fatale** : colonne générée `slot_key` (NULL si annulé)
   + index unique `uq_slot_active` + `try/catch` PDO (code 23000) dans `BookingController`.
4. **Double comptage du CA** : `Mongo::upsert()` idempotent (clé `booking_id`) + garde
   sur transition réelle vers « completed ».
5. **Filtres catalogue en SSR** : `ServiceController::index()` lit `$_GET` et filtre
   côté serveur (fonctionne sans JS) ; le JS est une amélioration.
6. **Durcissement prod** : `docker/php/prod.ini` (erreurs masquées, OPcache figé),
   `App\Core\SecurityHeaders` (CSP, X-Frame-Options, nosniff, Referrer-Policy, HSTS),
   cookies `HttpOnly`/`Secure`/`SameSite`.
7. **Secrets MySQL** : création d'un utilisateur applicatif dédié `zenspace_app`
   (privilèges SELECT/INSERT/UPDATE/DELETE uniquement), suppression du fallback `root`.

**Durcissement complémentaire** :
- `App\Core\RateLimiter` (anti-bruteforce) par **IP et par compte**, pilote fichier
  par défaut, pilote **Redis** disponible.
- **Expiration de session** (20 min d'inactivité) + rotation d'ID (30 min).
- **Déconnexion complète** (suppression du cookie).
- **Invalidation de tous les jetons** de reset après un changement de mot de passe.

---

## 3. Phase 7 — Architecture & qualité

- **Routeur** durci : contraintes regex (`{id:\d+}`, `{slug:[a-z0-9\-]+}`) + gestion
  du **405** (méthode non autorisée) avec en-tête `Allow`.
- **Handler d'exception global** + pages d'erreur stylisées (400/403/404/405/419/429/500).
- `App\Repositories\BaseRepository` (classe abstraite), `App\Core\Container` (injection
  de dépendances), `App\Core\HttpException`, `App\Core\AuditLog` (journal Mongo).
- **PHPUnit** : 14 tests / 27 assertions (Router, RateLimiter, Seo, Validator).
- **Cypress** : parcours public + connexion.
- **CI GitHub Actions** : lint PHP → PHPUnit → e2e Docker+Cypress → déploiement (gabarit).

---

## 4. Phase 2/6 — Design & SEO (1re version)

- Design « Onsen Glass » (glassmorphism) + thème sombre + `:focus-visible` + RGAA
  (aria-live, aria-describedby).
- SEO : `App\Core\Seo` (meta dynamiques, Open Graph, Twitter, canonical, **JSON-LD**
  Service / Organization / Article).

> ⚠️ Cette première version visuelle a été **remplacée** par la suite (voir §8).

---

## 5. Phase 4 — Nouvelles fonctionnalités

- **Magazine / blog** (table `article`, `MagazineController`, vues, 3 articles seedés). ✅ conservé
- **Tableau de bord admin** avec graphique **Chart.js** (CA par prestation). ✅ conservé
- **Journal d'audit** (`AuditLog` → Mongo) câblé sur suppression service / désactivation employé. ✅ conservé
- **Fidélité** (`LoyaltyRepository` + partial badge) — socle. ✅ conservé
- **Scaffolds** : Paiement Stripe, QR signé HMAC, Chat Mercure (pages de démo, à
  brancher avec clés/services). ✅ conservés (scaffolds)

---

## 6. Expérimentations 3D / Réalité augmentée (RETIRÉES)

Sur la fiche prestation, plusieurs essais successifs d'un aperçu 3D :
- aperçu « ambiance » générique (galets + bougies) ;
- scène **spécifique par prestation** (table de massage, lit de soin, bassin spa) ;
- rendu **réaliste** (IBL, ACES, bump maps, bloom, mannequin allongé, **vrai modèle
  GLB** « Lantern » chargé via GLTFLoader) ;
- bouton « Réalité augmentée » (WebXR sur mobile compatible).

**Décision finale** : tout cet aspect 3D/AR a été jugé **gadget** et **RETIRÉ** au profit
d'une fonctionnalité utile (voir §7). Fichiers supprimés : `ar-preview.js`, `hero3d.js`,
`lantern.glb`, `assets/3d/`.

---

## 7. Remplacement par une fonctionnalité utile : planning de disponibilités

La fiche prestation affiche désormais un **planning des 7 prochains jours d'ouverture** :
- créneaux **libres cliquables** (réservation pré-remplie en un clic), **réservés/passés** grisés ;
- respecte les horaires (dimanche fermé, samedi matin uniquement, créneaux passés exclus) ;
- 100 % côté serveur (fonctionne sans JS), accessible.
- Ajout d'un bloc **« Comment se déroule le soin ? »** (étapes propres à chaque catégorie).
- Nouvelle méthode `BookingRepository::takenSlots()` + `ServiceController::buildAvailability()`.

---

## 8. Refonte visuelle « minimaliste magazine »

Le design précédent faisait « trop IA / trop chargé ». Nouvelle direction **sobre et éditoriale** :
- **Retiré** : pétales animées, bouton + thème sombre, hero 3D, page **Méditation**
  (route/contrôleur/vue/JS), emojis dans les boutons, boutons « Voir le détail »
  (cartes désormais cliquables d'un bloc), glassmorphism, dégradés, animations d'apparition.
- **Nouvelle identité** : palette neutre (ivoire `#FBFAF7`, encre `#1C1C1A`, accent sauge
  `#3E5C4E`), typographie **Fraunces + Inter**, **hero photo plein cadre** + un seul CTA,
  sections aérées, cartes à filet fin, boutons sobres (coins légers). Détails : [DESIGN.md](DESIGN.md).
- `app.css` **réécrit intégralement** ; **cache-busting** ajouté sur le `<link>` CSS.

---

## 9. Détails finaux

- Pied de page : mention **« Tous droits réservés »** ajoutée.
- **Confirmation POO** : 44 classes, namespaces PSR-4, héritage (`Controller`,
  `BaseRepository`, `HttpException`), abstraction, encapsulation, `declare(strict_types=1)`
  partout. Les vues sont des gabarits (couche V), `index.php` le front controller,
  `helpers.php` de fines fonctions globales — architecture **MVC orientée objet**. Voir [ARCHITECTURE.md](ARCHITECTURE.md).

---

## Récapitulatif des fichiers clés ajoutés

`src/app/Core/` : `Mailer`, `RateLimiter`, `SecurityHeaders`, `Seo`, `HttpException`,
`Container`, `AuditLog`. — `src/app/Repositories/BaseRepository`. —
`src/app/Controllers/` : `Magazine`, `Payment`, `Qr`, `Chat`. — `docker/php/prod.ini`,
`db/migrations/2026_06_01_hardening.sql`, `db/seed_articles.sql`, `composer.json`,
`phpunit.xml`, `tests/`, `cypress/`, `.github/workflows/ci.yml`.
