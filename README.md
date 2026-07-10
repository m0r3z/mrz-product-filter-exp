# MRZ Product Filter

Filtres de produits WooCommerce (catégories, attributs, étiquettes, prix, pastilles de couleur) affichés en colonne à gauche ou au-dessus de la boutique, avec mise à jour AJAX. Mise en page minimaliste (wireframe noir & blanc), à styliser dans le thème.

## Fonctionnement

- Chaque **jeu de filtres** est un post d'un CPT dédié (`Jeux de filtres`) avec ses métaboxes.
- On y choisit les **sources** (catégories, étiquettes, attributs), leur mode d'affichage (cases à cocher, boutons radio, menu déroulant) et l'ordre (glisser-déposer).
- Un **attribut « couleur »** peut être rendu en pastilles ; la couleur est stockée sur chaque terme (réutilisable, éditable aussi depuis Produits → Attributs).
- Le panneau s'affiche **automatiquement** sur la boutique / les archives (colonne gauche ou au-dessus), ou manuellement via `[mrz_product_filter id="X"]`.

## Filtrage

- Le filtrage s'appuie sur les **paramètres d'URL natifs WooCommerce** (`filter_{attribut}`, `min_price`, `max_price`) et sur des paramètres propres pour catégories/étiquettes (`mpf_product_cat`, `mpf_product_tag`).
- Avec JavaScript : la boucle produits (`ul.products`) est rafraîchie en **AJAX**, l'URL est mise à jour.
- Sans JavaScript : le formulaire recharge la page et WooCommerce filtre nativement (attributs / prix) ; catégories et étiquettes sont injectées dans la requête principale par le plugin.

## Prérequis

- WordPress 6.3+, PHP 7.4+, WooCommerce.

## Développement

- Namespace : `MrzProductFilterExp\`, autoloader maison (`includes/class-*.php`).
- Text domain : `mrz-product-filter-exp`.
- Régénérer les traductions : `wp i18n make-pot . languages/mrz-product-filter-exp.pot`.

## Limites connues (v1)

- Les compteurs par terme sont statiques (nombre total de produits publiés) et ne se recalculent pas après filtrage.
- Le filtrage par prix utilise la meta `_price` (approximation suffisante pour la majorité des cas, y compris produits variables).
- Sans JavaScript, la sélection multiple par cases à cocher ne conserve qu'une valeur par filtre (les attributs simples restent nativement compatibles).
- La colonne gauche s'appuie sur les hooks `woocommerce_before_shop_loop` / `woocommerce_after_shop_loop` : selon le thème (ex. Salient), la mise en page peut demander un ajustement CSS.

## Feuille de route (v2)

- Filtres tailles / pointures en carrés, avec rayure des tailles indisponibles.
- Filtres par note et par disponibilité.
- Compteurs dynamiques recalculés après filtrage.
