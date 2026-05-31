# Sécurité & RGPD — ZenSpace

Synthèse des mesures de sécurité mises en place, utile pour l'oral du jury.

---

## 1. Authentification & mots de passe

- Mots de passe **hachés avec bcrypt** (`password_hash`, coût 12). Jamais stockés
  ni journalisés en clair. Vérification via `password_verify`.
  → `app/Core/Auth.php`
- **Politique de mot de passe fort** validée côté serveur : 10 caractères minimum,
  au moins une majuscule, une minuscule, un chiffre et un caractère spécial.
  → `app/Core/Validator.php` (méthode `strongPassword`)
- **Régénération de l'identifiant de session** à la connexion (`session_regenerate_id`)
  pour prévenir la fixation de session.

## 2. Gestion des rôles (contrôle d'accès)

- Trois rôles : `client`, `employe`, `admin`.
- Chaque action protégée vérifie le rôle via `requireRole()` (→ `app/Core/Controller.php`).
- L'espace employé est accessible aux employés ET admins ; la gestion des employés
  et les statistiques sont **réservées à l'admin**.
- **Impossible de créer un compte administrateur depuis l'application** : la création
  d'employé force le rôle `employe` (→ `EmployeeController`).
- Un client ne peut consulter/annuler que **ses propres** réservations (vérification
  de propriété dans `BookingController`).

## 3. Protection des formulaires

- **Jeton CSRF** sur tous les formulaires POST (→ `app/Core/Csrf.php`), comparé en
  temps constant (`hash_equals`).
- Cookie de session en **HttpOnly** (inaccessible au JavaScript) et **SameSite=Lax**.

## 4. Protection contre les injections

- **Injections SQL** : 100 % des requêtes passent par PDO en **requêtes préparées**
  avec paramètres liés. Aucune concaténation de saisie utilisateur dans le SQL.
- **Failles XSS** : toutes les données affichées sont échappées via `htmlspecialchars`
  (helper `e()` / `View::e`).

## 5. Réinitialisation de mot de passe

- Jeton aléatoire de 256 bits ; **seul son hash SHA-256 est stocké** en base.
- Jeton à **usage unique** et avec **expiration** (1 heure).
- Réponse **identique** que l'e-mail existe ou non (protection contre l'énumération
  des comptes).

## 6. RGPD

- **Consentement explicite obligatoire** à l'inscription (case à cocher + colonne
  `rgpd_consent` en base).
- Collecte **minimale** des données (uniquement ce qui est nécessaire à la réservation).
- Mots de passe non récupérables (hachés).
- Pistes d'évolution : export des données personnelles, suppression/anonymisation
  de compte, page « politique de confidentialité ».

## 7. Bonnes pratiques diverses

- Affichage des erreurs **désactivé en production** (`APP_ENV=prod`).
- Secrets (mots de passe BDD, clés) dans `.env`, **exclu du dépôt Git** (`.gitignore`).
- Seul le dossier `public/` est exposé par le serveur web ; le code applicatif
  (`app/`) n'est pas accessible directement par URL.
- Suppression « douce » des prestations (désactivation) pour préserver l'historique.
