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

### Choix techniques
- **Clés étrangères** entre toutes les tables liées (intégrité référentielle).
- **Contraintes CHECK** : prix ≥ 0, durée > 0, note entre 1 et 5.
- **Index** sur les colonnes fréquemment filtrées (email, statut, catégorie).
- **Contrainte d'unicité** `uq_slot` : un créneau ne peut être réservé deux fois.
- Accès **exclusivement via PDO en requêtes préparées** (anti-injection SQL).
- Création par **scripts SQL** (`schema.sql` + `seed.sql`), sans ORM.

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
