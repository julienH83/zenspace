# 🏛️ Architecture — ZenSpace

Application **MVC orientée objet** en PHP 8.2, sans framework, avec autoloader PSR-4 maison.

## Couches

```
Requête HTTP
   │
   ▼
public/index.php  ── front controller (bootstrap + déclaration des routes)
   │
   ▼
App\Core\Router   ── associe méthode+URL → [Contrôleur, action] (regex, 405)
   │
   ▼
App\Controllers\* ── orchestrent : validation, autorisation, CSRF, flash, redirection
   │            \
   │             └─► App\Core\* (Auth, Csrf, Validator, Seo, Mailer, RateLimiter…)
   ▼
App\Repositories\* ── accès données (PDO préparé) ; héritent de BaseRepository
   │
   ├─► MySQL (relationnel)        via App\Core\Database (singleton PDO)
   └─► MongoDB (stats / audit)    via App\Core\Mongo
   │
   ▼
App\Core\View     ── rend un gabarit app/Views/*.php dans le layout commun
   │
   ▼
Réponse HTML / JSON
```

## Preuves « orienté objet » (mesuré dans le code)

| Indicateur | Valeur |
|------------|--------|
| Classes | **44** (`final` partout, sauf `BaseRepository` `abstract` et `Controller` de base) |
| Namespaces `App\…` | 44 |
| `declare(strict_types=1)` | dans tous les fichiers de classe |
| Héritage (`extends`) | 20 classes (contrôleurs ← `Controller`, repos ← `BaseRepository`, `HttpException` ← `RuntimeException`) |
| Visibilité | `private`, `protected`, `public`, `static` utilisés (encapsulation) |

**Les 4 piliers** :
- **Encapsulation** : membres `private`/`protected`, accès via méthodes (`Database::getConnection`, `Auth::user`).
- **Héritage** : tous les contrôleurs étendent `Controller` ; `ArticleRepository`/`LoyaltyRepository` étendent `BaseRepository`.
- **Abstraction** : `abstract class BaseRepository` ; `HttpException` spécialise `RuntimeException`.
- **Polymorphisme** : le routeur instancie n'importe quel contrôleur et appelle son action ; `requireRole()` partagée par héritage.

**Patrons & techniques** : Singleton (`Database`, `Mongo`), Front Controller (`index.php`),
Repository (`*Repository`), Injection de dépendances (`Container`), autoloader PSR-4
(`App::registerAutoload`).

> Les **vues** (`app/Views/*.php`) sont des gabarits (couche V), `index.php` le front
> controller, et `helpers.php` de fines fonctions globales (`e()`, `csrf()`) qui ne font
> qu'appeler les classes. C'est l'usage standard, y compris dans Symfony/Laravel.

## Arborescence

```
src/
├── public/                 # racine web (seul dossier exposé)
│   ├── index.php           # front controller
│   └── assets/             # css, js, images
├── app/
│   ├── Core/               # noyau : App, Router, Controller, View, Database, Mongo,
│   │                       #   Auth, Csrf, Validator, Flash, Seo, Mailer, RateLimiter,
│   │                       #   SecurityHeaders, HttpException, Container, AuditLog, Str
│   ├── Controllers/        # Home, Service, Auth, Booking, Review, Contact, Magazine,
│   │   └── Admin/          #   Payment, Qr, Chat + Admin\(Dashboard, AdminService,
│   │                       #   AdminBooking, AdminReview, Employee, Stats)
│   ├── Repositories/       # BaseRepository + User, Service, Booking, Review,
│   │                       #   PasswordReset, Contact, Article, Loyalty
│   └── Views/              # gabarits par section + layout.php + emails/
└── storage/                # mail (dev), ratelimit, cache (hors webroot)
```

## Cycle d'une requête (exemple : voir une prestation)

1. `GET /prestation/massage-californien` → `index.php` → `Router::dispatch`.
2. La route `'/prestation/{slug:[a-z0-9\-]+}'` correspond → `ServiceController::show('massage-californien')`.
3. Le contrôleur appelle `ServiceRepository::findBySlug()`, `ReviewRepository::findValidatedByService()`,
   `buildAvailability()` (via `BookingRepository::takenSlots()`).
4. `View::render('service/show', [...])` injecte le gabarit dans `layout.php`.
5. Réponse HTML (avec balises SEO via `Seo::tags`).

En cas d'erreur (404, 403, 405, 500…), une `HttpException` est levée et capturée par
le **handler global** (`App::registerExceptionHandler`) qui rend une page d'erreur stylisée.
