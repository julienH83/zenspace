<?php
/**
 * Monogramme de marque ZenSpace (SVG inline).
 *
 * Inliné plutôt qu'en <img> pour deux raisons :
 *  - `currentColor` hérite naturellement de la couleur du conteneur (ex. or
 *    dans le header, crème dans le footer), sans hack de filtre CSS ;
 *  - un aller-retour HTTP en moins par page.
 *
 * @var string|null $class  Classes CSS additionnelles (facultatif).
 */
$brandClass = 'brand-mark' . (isset($class) ? ' ' . $class : '');
?>
<svg class="<?= $brandClass ?>"
     viewBox="0 0 64 64"
     fill="none"
     role="img"
     aria-hidden="true"
     focusable="false">
    <!-- Anneau extérieur : filet principal du sceau -->
    <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="1.6"/>
    <!-- Anneau intérieur : double filet (typographie de sceau) -->
    <circle cx="32" cy="32" r="26.5" stroke="currentColor" stroke-width="0.7" opacity="0.55"/>
    <!-- Étoile à quatre branches (ornement typographique classique) -->
    <path d="M32 8.5 L32.9 11.4 L35.8 12.3 L32.9 13.2 L32 16.1 L31.1 13.2 L28.2 12.3 L31.1 11.4 Z"
          fill="currentColor"/>
    <!-- Filets ornementaux gauche et droite (équilibrent le sceau) -->
    <path d="M11.5 32 h3.2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
    <path d="M49.3 32 h3.2" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/>
    <!-- Monogramme "Z" serif italique dessiné en chemins vectoriels -->
    <g fill="currentColor" transform="translate(32 34) rotate(-8) translate(-32 -34)">
        <!-- Trait supérieur -->
        <path d="M20.5 22 L44 22 L44 24.4 L42.6 24.4 L42.6 23.3 L22.4 23.3 L22.4 24.4 L20.5 24.4 Z"/>
        <!-- Diagonale -->
        <path d="M41.2 23.3 L44 23.3 L26.4 44.7 L23 44.7 Z"/>
        <!-- Trait inférieur -->
        <path d="M20 43.6 L21.4 43.6 L21.4 44.7 L41.6 44.7 L41.6 43.6 L43.5 43.6 L43.5 46 L20 46 Z"/>
    </g>
</svg>
