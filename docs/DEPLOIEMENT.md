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

# 4. Initialiser la base relationnelle (ordre impératif : schéma, données,
#    migration — qui crée entre autres la table "article" — puis les articles)
docker compose exec -T mysql mysql -uroot -proot zenspace < db/schema.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/seed.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/migrations/2026_06_01_hardening.sql
docker compose exec -T mysql mysql -uroot -proot zenspace < db/seed_articles.sql
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

## 3. Mise en production sur Render

L'application est conteneurisée : elle peut être déployée sur tout hébergeur
supportant Docker (Render, Railway, un VPS avec Docker, etc.). Le dépôt fournit
un blueprint [`render.yaml`](../render.yaml) prêt à l'emploi pour Render.

### 3.1 Ce que le blueprint déploie automatiquement
- Le **service web** (`zenspace-web`), construit directement à partir du
  `Dockerfile` du dépôt.
- Un **service Redis managé** (`zenspace-redis`), utilisé pour le rate limiting
  (`RATE_LIMIT_DRIVER=redis`).
- `APP_SECRET` généré aléatoirement par Render, `APP_ENV=prod`.

### 3.2 Ce qui reste à créer manuellement (Render ne propose pas de MySQL/MongoDB managés)
1. **MongoDB** : créer un cluster gratuit sur [MongoDB Atlas](https://www.mongodb.com/atlas)
   (M0), copier l'URI de connexion (`mongodb+srv://...`) dans la variable
   `MONGO_URI` du service `zenspace-web` sur le dashboard Render.
2. **MySQL** : Render n'a pas d'offre MySQL managée. Deux options :
   - un hébergeur MySQL externe (à choisir selon les conditions du moment —
     vérifier le tarif/quota avant de s'engager) ;
   - un « Private Service » Render exécutant l'image `mysql:8.0` avec un disque
     persistant (payant à partir du plan Starter).
   Renseigner ensuite `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` sur
   le service `zenspace-web`.
3. Une fois la base MySQL joignable, initialiser le schéma **une seule fois**
   depuis un poste ayant accès à la base distante :
   ```bash
   mysql -h <host> -P <port> -u <user> -p <db> < db/schema.sql
   mysql -h <host> -P <port> -u <user> -p <db> < db/seed.sql
   mysql -h <host> -P <port> -u <user> -p <db> < db/migrations/2026_06_01_hardening.sql
   ```

### 3.3 Déploiement
1. Se connecter sur [render.com](https://render.com) avec le compte existant.
2. **New +** → **Blueprint** → sélectionner le dépôt GitHub `julienH83/zenspace`.
   Render détecte `render.yaml` et propose de créer les deux services.
3. Renseigner les variables `sync: false` (`DB_*`, `MONGO_URI`) une fois les
   bases externes créées (étape 3.2).
4. Render construit l'image et déploie ; l'URL publique est de la forme
   `https://zenspace-web.onrender.com`.

### 3.4 Limites du plan gratuit à connaître pour la démonstration
- Le plan `free` de Render met le service en veille après une période
  d'inactivité ; la première requête après veille peut prendre ~30 s à charger.
- Pas de disque persistant sur le plan `free` du service web : normal, la
  persistance des données est portée par MongoDB Atlas et la base MySQL
  externe, pas par le conteneur web.

### 3.5 Autres points de durcissement pour une vraie mise en production
1. Utiliser `docker/php/prod.ini` (déjà présent dans le dépôt) plutôt que
   `docker/php/php.ini` dans l'image de production.
2. Un compte MySQL applicatif à privilèges réduits, jamais `root`
   (voir `db/migrations/2026_06_01_hardening.sql`).
3. Configurer un vrai service d'envoi d'e-mails (réinitialisation de mot de
   passe, confirmation de réservation, formulaire de contact) — `MAIL_TRANSPORT`
   passe de `log` à `mail`/SMTP.
4. HTTPS : géré nativement par Render (certificat TLS automatique sur les
   domaines `*.onrender.com` et les domaines personnalisés).

## 4. Dépannage

| Problème | Solution |
|---|---|
| « Connection refused » MySQL au démarrage | MySQL met quelques secondes à démarrer ; relancer la commande d'import après 10 s. |
| Page blanche | Vérifier `APP_ENV=dev` puis consulter les logs : `docker compose logs web`. |
| Modifs non prises en compte | Le dossier `src/` est monté en direct ; un simple rafraîchissement suffit. |
