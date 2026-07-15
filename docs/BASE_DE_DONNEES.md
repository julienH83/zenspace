# Base de données — ZenSpace

Le projet utilise **deux types de bases**, comme exigé par le référentiel :
- une base **relationnelle** (MySQL) pour les données métier,
- une base **non relationnelle** (MongoDB) pour les statistiques.

---

## 1. Base relationnelle (MySQL)

### Modèle conceptuel (résumé)

```
role 1───∞ user 1───∞ booking ∞───1 service ∞───1 category
                         │
                         ├───∞ booking_status_history
                         └───1 review
user 1───∞ password_reset
contact_message (indépendante)
```

### Tables

| Table | Rôle |
|---|---|
| `role` | Les 3 rôles : client, employe, admin |
| `user` | Comptes utilisateurs (mot de passe haché bcrypt) |
| `password_reset` | Jetons de réinitialisation de mot de passe (hachés, avec expiration) |
| `category` | Catégories de prestations |
| `service` | Catalogue des prestations |
| `booking` | Réservations |
| `booking_status_history` | Historique des changements de statut d'une réservation |
| `review` | Avis clients (validés avant publication) |
| `contact_message` | Messages du formulaire de contact |
| `loyalty_ledger` | Grand-livre des points de fidélité (ajouté par migration) |
| `article` | Articles du magazine bien-être (ajouté par migration) |

### Choix techniques
- **Clés étrangères** entre toutes les tables liées (intégrité référentielle).
- **Contraintes CHECK** : prix ≥ 0, durée > 0, note entre 1 et 5.
- **Type ENUM** pour le statut de réservation (`pending`/`confirmed`/`completed`/`cancelled`).
- **Colonne générée** `slot_key` (STORED) : libère réellement un créneau annulé
  tout en gardant l'unicité des créneaux actifs (migration de durcissement).
- **Index** sur les colonnes fréquemment filtrées (email, statut, catégorie).
- **Contrainte d'unicité** `uq_slot` : un créneau ne peut être réservé deux fois.
- Accès **exclusivement via PDO en requêtes préparées** (anti-injection SQL).
- Création par **scripts SQL versionnés** (`schema.sql` + migrations), sans ORM.

### Triggers — note moyenne maintenue automatiquement
La note moyenne et le nombre d'avis validés d'une prestation sont **dénormalisés**
sur la table `service` (`rating_avg`, `rating_count`) et tenus à jour par **trois
triggers** sur la table `review` (`AFTER INSERT / UPDATE / DELETE`). Seuls les avis
**validés** (`is_validated = 1`) sont comptés.

Intérêt : le catalogue et la page d'accueil affichent les étoiles **sans recalcul
ni jointure d'agrégation** à chaque requête ; la logique vit dans la base et reste
cohérente quel que soit le point d'entrée (application, script SQL, modération).

Voir `db/migrations/2026_07_10_triggers_ratings.sql`.

---

## 2. Base non relationnelle (MongoDB)

### Pourquoi une base NoSQL ?
Les statistiques (chiffre d'affaires, volume de réservations) sont des données
**analytiques** : on les écrit une fois, on les agrège souvent. Une base
documentaire comme MongoDB est bien adaptée et évite de surcharger la base
métier avec des calculs lourds.

### Collection `revenue`
Chaque fois qu'une réservation passe au statut **« terminée »**, un document est
inséré :

```json
{
  "booking_id": 42,
  "service_id": 1,
  "service_title": "Massage relaxant californien",
  "amount": 65.00,
  "date": "2026-05-31"
}
```

### Utilisation
L'espace administrateur (`/admin/statistiques`) lit cette collection, filtre
éventuellement par période, puis agrège par prestation (CA total + nombre).

Voir le code dans :
- écriture : `app/Controllers/Admin/AdminBookingController.php` (méthode `updateStatus`)
- lecture/agrégation : `app/Controllers/Admin/StatsController.php`
- accès bas niveau : `app/Core/Mongo.php`
