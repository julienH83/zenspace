# ZenSpace — Réservation de prestations bien-être

[![CI](https://github.com/julienH83/zenspace/actions/workflows/ci.yml/badge.svg)](https://github.com/julienH83/zenspace/actions/workflows/ci.yml)

Application web de réservation de prestations pour un institut de bien-être.
Les visiteurs consultent le catalogue de prestations, filtrent en direct, créent un
compte, réservent un créneau et suivent leurs réservations. Les employés gèrent les
prestations et les réservations ; l'administrateur gère les employés et consulte les
statistiques.

> Projet réalisé dans le cadre du Titre Professionnel **Développeur Web et Web Mobile (DWWM — RNCP37674)**.

---

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Démarrage rapide (Docker)](#démarrage-rapide-docker)
- [Comptes de démonstration](#comptes-de-démonstration)
- [Structure du projet](#structure-du-projet)
- [Documentation](#documentation)

---

## Fonctionnalités

### Visiteur (non connecté)
- Page d'accueil : présentation de l'institut, mise en avant de l'équipe, avis clients validés
- Catalogue des prestations avec **filtres dynamiques sans rechargement** (catégorie, prix, durée)
- Page détail d'une prestation
- Création de compte (rôle « client ») avec mot de passe fort + consentement RGPD
- Connexion / mot de passe oublié (lien de réinitialisation)
- Page de contact (formulaire envoyé par e-mail)

### Client (connecté)
- Réservation d'un créneau pour une prestation
- Espace personnel : liste et détail de ses réservations
- Annulation d'une réservation tant qu'elle n'est pas confirmée
- Avis (note 1 à 5 + commentaire) une fois la prestation terminée

### Employé (connecté)
- Gestion des prestations (créer / modifier / supprimer)
- Gestion des réservations (changement de statut : en attente → confirmée → terminée / annulée)
- Modération des avis (valider / refuser)

### Administrateur (connecté)
- Tout ce que peut faire un employé
- Création / désactivation de comptes employés (impossible de créer un admin depuis l'app)
- Tableau de bord **statistiques** (chiffre d'affaires par prestation, nombre de réservations)
  alimenté par une **base NoSQL (MongoDB)**

---

## Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML5, CSS3 (responsive, mobile-first), JavaScript (vanilla, `fetch`) |
| Back-end | PHP 8.2 (architecture MVC maison, sans framework) |
| Accès données SQL | PDO (requêtes préparées) |
| Base relationnelle | MySQL 8 |
| Base non relationnelle | MongoDB 7 (statistiques) |
| Serveur web | Apache 2.4 |
| Conteneurisation | Docker + Docker Compose |

---

## Architecture

Architecture **MVC en couches** :

```
Requête HTTP
   │
   ▼
public/index.php  ──►  Router  ──►  Controller  ──►  Repository (PDO)  ──►  MySQL
                                        │
                                        ├──►  Mongo (service)          ──►  MongoDB
                                        │
                                        └──►  View  ──►  HTML renvoyé au navigateur
```

- **Controllers** : reçoivent la requête, appliquent les règles métier, choisissent la vue.
- **Repositories** : seuls à parler à la base SQL (toutes les requêtes y sont centralisées et préparées).
- **Core** : briques techniques réutilisables (Router, Database, Auth, Csrf, Validator, View).
- **Views** : gabarits PHP (présentation uniquement).

---

## Démarrage rapide (Docker)

Prérequis : **Docker Desktop** installé.

```bash
# 1. Copier le fichier d'environnement
cp .env.example .env

# 2. Construire et lancer les conteneurs (web + MySQL + MongoDB)
docker compose up -d --build

# 3. Créer le schéma + jeu de données de démo (ordre impératif)
docker compose exec -T mysql mysql -uroot -proot zenspace < db/schema.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/seed.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/migrations/2026_06_01_hardening.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/seed_articles.sql

# 4. Ouvrir l'application
#    http://localhost:8080
```

Pour tout arrêter : `docker compose down` (ajouter `-v` pour supprimer aussi les données).

---

## Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | admin@zenspace.fr | Admin1234! |
| Employé | employe@zenspace.fr | Employe1234! |
| Client | client@zenspace.fr | Client1234! |

---

## Structure du projet

```
reservation-prestations/
├── docker-compose.yml        # Orchestration des 3 conteneurs
├── Dockerfile                # Image PHP + Apache
├── .env.example              # Variables d'environnement (modèle)
├── render.yaml                # Blueprint de déploiement (Render)
├── db/
│   ├── schema.sql            # Création des tables (base relationnelle)
│   ├── seed.sql               # Données de démonstration
│   └── migrations/            # Migrations versionnées (appliquées après schema.sql)
├── docker/apache/vhost.conf  # Configuration Apache
├── docs/                     # Documentation (déploiement, sécurité, BDD)
└── src/
    ├── public/               # Racine web exposée
    │   ├── index.php         # Front controller (point d'entrée unique)
    │   ├── .htaccess         # Réécriture d'URL
    │   └── assets/           # CSS + JS + images
    └── app/
        ├── Core/             # Router, Database, Mongo, Auth, Csrf, Validator, View
        ├── Controllers/      # Logique des pages
        ├── Repositories/     # Accès SQL (PDO)
        └── Views/            # Gabarits HTML
```

---

## Documentation

- [`docs/DEPLOIEMENT.md`](docs/DEPLOIEMENT.md) — installation locale et mise en ligne
- [`docs/BASE_DE_DONNEES.md`](docs/BASE_DE_DONNEES.md) — modèle de données (relationnel + NoSQL)
- [`docs/SECURITE.md`](docs/SECURITE.md) — mesures de sécurité et RGPD

---

## Licence

Projet pédagogique — tous droits réservés.
