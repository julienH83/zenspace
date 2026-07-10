# Maquettage — ZenSpace

> Dossier de conception (CP2 « Maquetter une application ») : charte graphique,
> wireframes des pages clés et parcours utilisateurs. Il documente les choix de
> conception **en amont** de l'intégration ; la traduction en code est décrite
> dans [`DESIGN.md`](DESIGN.md) (système de composants effectivement implémenté).

Sommaire :
1. [Intentions & contraintes](#1-intentions--contraintes)
2. [Charte graphique](#2-charte-graphique)
3. [Wireframes des pages clés](#3-wireframes-des-pages-clés)
4. [Parcours utilisateurs](#4-parcours-utilisateurs)
5. [Approche responsive & accessibilité](#5-approche-responsive--accessibilité)

---

## 1. Intentions & contraintes

**Positionnement** : institut de bien-être à Bordeaux. L'interface doit évoquer
le calme et la confiance, tout en restant un **site de services** clair et
orienté **réservation** (et non une revue éditoriale).

**Parti pris visuel** : site de services moderne — typographie sans-serif unique
(Inter), titres compacts, mise en page structurée et dense, marque affirmée
(barre utilitaire, pastille de marque, bouton « Réserver » toujours visible),
accent vert sauge, pied de page sombre. La profondeur (ombres douces,
micro-animations d'apparition) reste discrète.

**Contraintes** :
- **Mobile-first** : conception pensée d'abord pour le téléphone, puis élargie.
- **Accessibilité RGAA AA** : contrastes vérifiés, navigation clavier, `prefers-reduced-motion`.
- **Amélioration progressive** : tout fonctionne sans JavaScript ; le JS enrichit.

**Personas** (résumé) :
| Persona | Objectif principal | Pages prioritaires |
|---|---|---|
| Cliente visiteuse | Trouver et réserver un soin | Accueil → Catalogue → Fiche → Réservation |
| Cliente fidèle | Suivre ses réservations et ses points | Connexion → Mon compte |
| Employé / Admin | Gérer prestations, réservations, avis, magazine | Espace gestion |

---

## 2. Charte graphique

### 2.1 Palette

| Rôle | Nom | Hex | Usage | Contraste |
|---|---|---|---|---|
| Fond | Ivoire | `#FBFAF7` | Fond de page | — |
| Surface | Blanc | `#FFFFFF` | Cartes, en-tête, pied | — |
| Surface alt. | Sable | `#F4F1EA` | Bandes de section | — |
| Texte | Encre | `#1C1C1A` | Texte principal | ~15:1 sur ivoire ✅ |
| Texte secondaire | Gris chaud | `#6B6B63` | Légendes, méta | ~4.9:1 sur surface ✅ |
| Filet | Lin | `#E7E3DB` | Bordures discrètes | — |
| Accent | Vert sauge | `#3E5C4E` | Liens, boutons, CTA | blanc dessus ~6.5:1 ✅ |
| Accent foncé | Vert profond | `#2C4438` | Survols | ✅ |
| Accent clair | Vert brume | `#ECF1EE` | Pastilles d'icônes, fonds doux | — |
| Doré | Or | `#9C7A3C` | Étoiles d'avis uniquement | — |

Tous les couples texte/fond visés respectent WCAG AA (≥ 4.5:1 pour le texte
courant). L'accent est utilisé avec parcimonie : la couleur signale l'action.

### 2.2 Typographie

| Usage | Police | Graisses | Remarque |
|---|---|---|---|
| Titres | **Inter** | 700 / 800 | Compacts, `letter-spacing` négatif |
| Texte courant | **Inter** | 400–600 | Lisibilité écran |

Une seule famille (Inter) du texte aux titres : cohérent avec un site de
services (et non une revue). Échelle de titres fluide via `clamp()` :
`h1` ~1.9 → 2.5 rem · `h2` ~1.5 → 2 rem · `h3` 1.2 rem. Corps de texte 1 rem,
interligne 1.6.

### 2.3 Échelle d'espacement & géométrie (design tokens)

- **Espacement** : `--space-1..8` = 0.25 / 0.5 / 0.75 / 1 / 1.5 / 2 / 3 / 4.5 rem.
- **Rayons** : `--radius` 10px (base), `--radius-sm` 6px, `--radius-lg` 18px, pilule 999px.
- **Ombres** : `--shadow-sm / md / lg` (élévation croissante, douce).
- **Mouvement** : `--dur` 220ms, courbe `cubic-bezier(.22,.61,.36,1)`.

### 2.4 Iconographie & logo

- **Icônes** : jeu SVG *inline* (aucune dépendance externe), trait fin 1.6,
  `currentColor` (héritent la couleur du texte). Cf. `Views/partials/icons.php`.
- **Logo / wordmark** : une pastille carrée à coins arrondis portant l'initiale
  « Z » (fond vert accent) accolée au nom « ZenSpace » en Inter 800 — repère de
  marque simple, présent en en-tête et en pied de page.

### 2.5 Inventaire des composants

- **Boutons** : `primaire` (fond accent), `fantôme` (contour), `danger` (action
  destructive) ; états survol (léger soulèvement + ombre), actif, désactivé.
- **Cartes de prestation** : image (ratio 4:3) + corps (catégorie, titre lien,
  note en étoiles, durée) et un pied **prix + bouton « Réserver »** ; élévation
  au survol + léger zoom de l'image.
- **Formulaires** : label au-dessus, focus clavier visible, erreurs signalées par
  couleur **et** contour + message lié (`aria-describedby`).
- **Badges de statut** : pilules colorées (en attente / confirmée / terminée / annulée).
- **Panneau fidélité** : solde + palier (bronze/argent/or) + historique des points.
- **Bandes** : sections pleine largeur à fond sable pour rythmer la page.

---

## 3. Wireframes des pages clés

Wireframes basse fidélité (zoning) — la hiérarchie et les blocs, sans le style.

### 3.1 Accueil

```
┌──────────────────────────────────────────────┐
│ ☎ 05 56… · Lun–Ven 9h–18h        1 rue du Spa │  barre utilitaire
├──────────────────────────────────────────────┤
│ [Z] ZenSpace   Accueil Prestations Contact   Connexion [Réserver] │  en-tête sticky
├──────────────────────────────────────────────┤
│  HÉRO deux colonnes                            │
│  Kicker · Titre · Sous-titre        │  [photo] │
│  [Voir les prestations] [Contact]   │          │
│  ✓ diplômés ✓ en ligne ✓ fidélité   │          │
├──────────────────────────────────────────────┤
│  NOS PRESTATIONS PHARES        « tout le cat.» │  ← services en premier
│  [carte] [carte] [carte]                       │
│   image · cat · titre · ★ · durée              │
│   ─────────────  prix   [Réserver]             │
├──────────────────────────────────────────────┤
│  NOS ENGAGEMENTS (bande) [icône]×3             │
├──────────────────────────────────────────────┤
│  COMMENT ÇA SE PASSE   1—2—3—4 étapes          │
├──────────────────────────────────────────────┤
│  AVIS (bande)  [citation ★★★★★] …               │
├──────────────────────────────────────────────┤
│  CONSEILS BIEN-ÊTRE (magazine, secondaire)     │  ← relégué en bas
│  [lien article] [lien article] [lien article]  │
├──────────────────────────────────────────────┤
│  BANDE CTA verte : « Prêt·e ? » [Réserver]     │
├──────────────────────────────────────────────┤
│  PIED SOMBRE : marque | coord. | horaires | liens │
└──────────────────────────────────────────────┘
```

### 3.2 Catalogue des prestations

```
┌──────────────────────────────────────────────┐
│  Nos prestations                               │
│  Filtres :  [Catégorie ▾]  Prix ≤ [====]  Durée ≤ [===] │  (GET, amélioré en JS)
├──────────────────────────────────────────────┤
│  Grille de cartes (responsive auto-fill)       │
│  [img]        [img]        [img]               │
│  catégorie    catégorie    catégorie           │
│  Titre        Titre        Titre               │
│  ★ note       ★ note       ★ note              │
│  durée · prix durée · prix durée · prix        │
└──────────────────────────────────────────────┘
```
Sans JS : la soumission GET recharge la page filtrée (URL partageable).
Avec JS : `fetch` met à jour la grille sans rechargement.

### 3.3 Fiche prestation + planning

```
┌──────────────────────────────────────────────┐
│  [Bannière photo 16:7]                         │
│  Catégorie · Titre serif · durée · PRIX        │
│  Description…                                  │
│  [Réserver]  [Retour au catalogue]             │
├──────────────────────────────────────────────┤
│  PROCHAINES DISPONIBILITÉS (7 jours)           │
│  Jour1  Jour2  Jour3 …                          │
│  09:00  09:00  —                               │  créneaux : libre (cliquable)
│  10:30  10:30  10:30                           │           réservé/passé (grisé)
│  …                                             │
├──────────────────────────────────────────────┤
│  AVIS CLIENTS  ★ moyenne (N)                    │
│  [citation] [citation] …                       │
└──────────────────────────────────────────────┘
```

### 3.4 Tunnel de réservation

```
Fiche ──[Réserver]──▶ ┌───────────────────────┐
                      │ Formulaire réservation │
                      │  Prestation (rappel)   │
                      │  Date  [____]          │
                      │  Créneau ( ) 09:00 …   │
                      │  [Confirmer]           │
                      └───────────┬────────────┘
                                  ▼
                      Récapitulatif « Mon compte »
                      (réservation en attente)
```

### 3.5 Espace de gestion (back-office)

```
┌───────────┬──────────────────────────────────┐
│ GESTION   │  Titre de la section    [+ Créer] │
│ Tableau   │  ┌──────────────────────────────┐ │
│ Prestations│  │ Tableau de données           │ │
│ Réservations│ │  ligne … [Modifier][Suppr.]  │ │
│ Avis      │  │  …                            │ │
│ Magazine  │  └──────────────────────────────┘ │
│ Employés* │                                    │
│ Stats*    │  (* = admin uniquement)            │
└───────────┴──────────────────────────────────┘
```

---

## 4. Parcours utilisateurs

### 4.1 Visiteuse → réservation

```mermaid
flowchart TD
    A[Accueil] --> B[Catalogue]
    B --> C{Filtre<br/>catégorie / prix / durée}
    C --> D[Fiche prestation]
    D --> E{Connecté ?}
    E -- Non --> F[Connexion / Inscription]
    F --> G[Choix date + créneau]
    E -- Oui --> G
    G --> H{Créneau libre ?}
    H -- Non --> G
    H -- Oui --> I[Réservation créée<br/>statut : en attente]
    I --> J[Mon compte]
```

### 4.2 Cliente → avis & fidélité

```mermaid
flowchart TD
    A[Employé confirme la réservation] --> B[Prestation réalisée]
    B --> C[Employé passe la résa à « terminée »]
    C --> D[+ points de fidélité<br/>au client]
    C --> E[Le client peut déposer un avis]
    E --> F[Avis en attente de modération]
    F --> G{Validé par le staff ?}
    G -- Oui --> H[Avis publié<br/>note moyenne mise à jour par trigger]
    G -- Non --> I[Avis refusé]
```

### 4.3 Administrateur → gestion

```mermaid
flowchart LR
    A[Espace gestion] --> B[Prestations<br/>créer / éditer / désactiver]
    A --> C[Réservations<br/>changer le statut]
    A --> D[Avis<br/>valider / refuser]
    A --> E[Magazine<br/>rédiger / publier]
    A --> F[Employés*<br/>créer / désactiver]
    A --> G[Statistiques*<br/>CA par prestation]
    F -. admin only .-> A
    G -. admin only .-> A
```

---

## 5. Approche responsive & accessibilité

**Mobile-first** : la conception part de l'écran ~375 px (une colonne, navigation
compacte) puis s'élargit. Les grilles utilisent `auto-fill/minmax` (elles
reflowent naturellement) et les titres `clamp()` (taille fluide). Points de
rupture principaux : ~768 px (empilement des blocs) et ~860 px (sidebar de
gestion → pleine largeur).

**Accessibilité** (RGAA AA) :
- Contrastes vérifiés (cf. §2.1) ; couleur jamais seul vecteur d'information.
- Focus clavier visible partout (`:focus-visible`), lien d'évitement, points de
  repère ARIA (`nav`, `main`, `role="status/alert"` pour les messages).
- Alternatives textuelles sur les images de contenu ; images décoratives `alt=""`.
- `prefers-reduced-motion` : toutes les animations d'apparition et transitions
  sont neutralisées ; le contenu reste évidemment visible sans JavaScript.
