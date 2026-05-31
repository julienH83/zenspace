# Déploiement — ZenSpace

## 1. Installation locale (Docker)

### Prérequis
- Docker Desktop (inclut Docker Compose)

### Étapes

```bash
# 1. Récupérer le projet et se placer dedans
cd reservation-prestations

# 2. Créer le fichier d'environnement
cp .env.example .env

# 3. Lancer les conteneurs (serveur web + MySQL + MongoDB)
docker compose up -d --build

# 4. Initialiser la base relationnelle (dans l'ordre : schéma puis données)
docker compose exec -T mysql mysql -uroot -proot zenspace < db/schema.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/seed.sql
```

L'application est disponible sur **http://localhost:8080**.

> La base NoSQL (MongoDB) ne nécessite aucune initialisation : la collection
> `revenue` est créée automatiquement à la première prestation terminée.

### Arrêt

```bash
docker compose down       # arrête les conteneurs
docker compose down -v    # + supprime les données (réinitialisation complète)
```

## 2. Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | admin@zenspace.fr | Admin1234! |
| Employé | employe@zenspace.fr | Employe1234! |
| Client | client@zenspace.fr | Client1234! |

## 3. Mise en production (piste)

L'application est conteneurisée : elle peut être déployée sur tout hébergeur
supportant Docker (Render, Railway, un VPS avec Docker, etc.).

Points à adapter pour la production :
1. Mettre `APP_ENV=prod` dans `.env` (masque les erreurs détaillées).
2. Générer une vraie valeur aléatoire pour `APP_SECRET`.
3. Utiliser des bases managées (MySQL + MongoDB) plutôt que les conteneurs locaux,
   et renseigner les variables `DB_*` et `MONGO_*` correspondantes.
4. Servir le site en HTTPS (certificat TLS, ex. via un reverse proxy).
5. Configurer un vrai service d'envoi d'e-mails (réinitialisation de mot de passe,
   confirmation de réservation, formulaire de contact).

## 4. Dépannage

| Problème | Solution |
|---|---|
| « Connection refused » MySQL au démarrage | MySQL met quelques secondes à démarrer ; relancer la commande d'import après 10 s. |
| Page blanche | Vérifier `APP_ENV=dev` puis consulter les logs : `docker compose logs web`. |
| Modifs non prises en compte | Le dossier `src/` est monté en direct ; un simple rafraîchissement suffit. |
