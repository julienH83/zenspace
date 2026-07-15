# 📚 Documentation — ZenSpace

Plateforme de réservation de prestations bien-être (PHP MVC orienté objet · MySQL · MongoDB · Redis · Docker).
Projet réalisé dans le cadre du Titre Professionnel **DWWM (RNCP37674)**.

## Sommaire des documents

| Document | Contenu |
|----------|---------|
| [JOURNAL.md](JOURNAL.md) | **Journal complet** de toutes les évolutions (chronologique) : correctifs, refontes, ajouts, retraits. |
| [IMPLEMENTATION.md](IMPLEMENTATION.md) | État **actuel** du projet : ce qui est livré et vérifié, fonctionnalités, comment activer les options de prod. |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Architecture MVC orientée objet : couches, classes, flux d'une requête, preuves POO. |
| [MAQUETTAGE.md](MAQUETTAGE.md) | **Conception** (CP2) : charte graphique, wireframes des pages clés, parcours utilisateurs. |
| [DESIGN.md](DESIGN.md) | Direction visuelle « minimaliste magazine » : palette, typographie, composants, accessibilité RGAA. |
| [SECURITE.md](SECURITE.md) | Mesures de sécurité (auth, CSRF, injections, en-têtes, rate-limiting…). |
| [BASE_DE_DONNEES.md](BASE_DE_DONNEES.md) | Schéma relationnel + MongoDB, choix de conception. |
| [DEPLOIEMENT.md](DEPLOIEMENT.md) | Démarrage Docker, comptes de démo, mise en production. |
| [REFONTE_ZENSPACE.md](REFONTE_ZENSPACE.md) | Plan d'action initial (vision exhaustive) — document de référence. |

## Démarrage rapide

```bash
cd reservation-prestations
docker compose up -d            # web (8080) + mysql + mongo + redis
# Schéma + données + migration :
docker exec -i zenspace_mysql mysql -uroot -proot zenspace < db/schema.sql
docker exec -i zenspace_mysql mysql -uroot -proot zenspace < db/seed.sql
docker exec -i zenspace_mysql mysql -uroot -proot zenspace < db/migrations/2026_06_01_hardening.sql
docker exec -i zenspace_mysql mysql -uroot -proot zenspace < db/seed_articles.sql
docker exec -i zenspace_mysql mysql -uroot -proot zenspace < db/migrations/2026_07_10_triggers_ratings.sql
```
→ http://localhost:8080

## Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|------|--------|--------------|
| Administrateur | admin@zenspace.fr | Admin1234! |
| Employé | employe@zenspace.fr | Employe1234! |
| Client | client@zenspace.fr | Client1234! |

## Tests

```bash
composer install
vendor/bin/phpunit            # tests unitaires (Router, RateLimiter, Seo, Validator)
npx cypress run               # tests de bout en bout (app démarrée)
```
