# 🎨 Direction visuelle — ZenSpace

Direction **minimaliste / éditoriale (« magazine »)** pour un institut de bien-être
haut de gamme : sobre, beaucoup d'espace blanc, sans animation superflue.
Tout est défini dans `src/public/assets/css/app.css`.

## Palette (toutes validées WCAG AA)

| Token | Valeur | Usage | Contraste |
|-------|--------|-------|-----------|
| `--bg` | `#FBFAF7` | fond ivoire | — |
| `--surface` | `#FFFFFF` | cartes, en-tête, pied | — |
| `--text` | `#1C1C1A` | texte principal (encre) | ~15:1 sur ivoire |
| `--muted` | `#6B6B63` | texte secondaire | ~4.9:1 |
| `--line` | `#E7E3DB` | filets / bordures | — |
| `--accent` | `#3E5C4E` | liens, bouton primaire (sauge) | ~6.5:1 (blanc dessus) |
| `--accent-ink` | `#2C4438` | survol | — |
| `--gold` | `#9C7A3C` | étoiles d'avis uniquement | — |

Aucun **thème sombre** (retiré). Des alias (`--cta-bg`, `--mist-100`, `--sage-700`…)
sont conservés pour compatibilité avec quelques styles inline.

## Typographie

- **Titres** : `Fraunces` (serif optique), grands, poids 500–600, interlignage serré.
- **Texte** : `Inter`, ~1.05rem, interligne 1.65.
- Échelle fluide avec `clamp()`.

## Composants

- **Hero** : photo **plein cadre** (full-bleed, casse la largeur du conteneur) + voile
  sombre, kicker en capitales espacées, grand titre serif, **un seul** bouton.
- **Boutons** : sobres, coins légers (`--radius:8px`, **pas** de forme pilule).
  `.btn-primary` (sauge plein), `.btn-ghost` (contour fin).
- **Cartes** (`.card.card-link`) : la carte **entière est un lien** (plus de bouton
  « Voir le détail »), filet fin, image en `object-fit:cover`, survol discret.
- **Sections** : `padding` généreux, `.section-head` (titre + lien « Voir tout »),
  `.eyebrow`/`.hero-kicker` (petites étiquettes en capitales colorées en accent).
- **Avis** : `figure/blockquote` en italique serif, étoiles dorées.
- **Formulaires** : champs à bordure simple, focus visible net.

## Accessibilité (RGAA / WCAG AA)

- `:focus-visible` (contour 2px accent) sur liens, boutons et champs.
- `skip-link` vers `#main`.
- Messages flash : `role="status" aria-live="polite"` (succès/info) et `role="alert"` (erreurs).
- Formulaires : `aria-invalid` + `aria-describedby` reliant chaque erreur à son champ.
- Contrastes ≥ 4.5:1 sur tous les couples texte/fond.
- `@media (prefers-reduced-motion: reduce)` neutralise les transitions.
- Planning de disponibilités : libellés `aria-label` explicites (« Réserver le … à … »).

## Ce qui a été RETIRÉ (par rapport aux versions précédentes)

Pétales animées, bouton + thème sombre, hero 3D (Three.js), aperçu 3D / réalité
augmentée, page Méditation, glassmorphism, dégradés, animations d'apparition au défilement,
emojis dans les boutons, boutons redondants. Objectif : rendu **net, intemporel, pro**.

## Cache des assets

Le `<link>` CSS porte un paramètre de version basé sur la date de modification
(`app.css?v=<mtime>`) : toute modification du CSS est prise en compte sans vidage
manuel du cache navigateur.
