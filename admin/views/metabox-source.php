<?php
/**
 * Métabox : sources des filtres (répéteur triable).
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MrzProductFilterExp\DataProvider;
use MrzProductFilterExp\FilterConfig;

$available = DataProvider::get_available_sources();
$sources   = isset( $values['sources'] ) ? (array) $values['sources'] : array();

/**
 * Rend le contenu d'une ligne de source.
 *
 * @param string $index Index (numérique ou __INDEX__ pour le template).
 * @param array  $row   Données de la ligne.
 * @param array  $available Sources disponibles.
 */
$render_row = static function ( $index, $row, $available ) {
	$key   = isset( $row['key'] ) ? $row['key'] : '';
	$mode  = isset( $row['mode'] ) ? $row['mode'] : 'checkbox';
	$logic = isset( $row['logic'] ) ? $row['logic'] : 'or';
	$label = isset( $row['label'] ) ? $row['label'] : '';
	$open  = ! empty( $row['open'] );
	$base  = 'mrz_product_filter_exp[sources][' . $index . ']';
	?>
	<div class="mrz-product-filter-exp-source-row">
		<span class="mrz-product-filter-exp-drag dashicons dashicons-menu" aria-hidden="true"></span>

		<select name="<?php echo esc_attr( $base . '[key]' ); ?>" class="mrz-product-filter-exp-source-key">
			<option value=""><?php esc_html_e( '— Choisir —', 'mrz-product-filter-exp' ); ?></option>
			<?php foreach ( $available as $src_key => $src ) : ?>
				<option value="<?php echo esc_attr( $src_key ); ?>" <?php selected( $key, $src_key ); ?>>
					<?php echo esc_html( $src['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<input type="text" name="<?php echo esc_attr( $base . '[label]' ); ?>" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Titre (optionnel)', 'mrz-product-filter-exp' ); ?>" class="mrz-product-filter-exp-source-label" />

		<select name="<?php echo esc_attr( $base . '[mode]' ); ?>" class="mrz-product-filter-exp-source-mode">
			<option value="checkbox" <?php selected( $mode, 'checkbox' ); ?>><?php esc_html_e( 'Cases à cocher', 'mrz-product-filter-exp' ); ?></option>
			<option value="radio" <?php selected( $mode, 'radio' ); ?>><?php esc_html_e( 'Boutons radio', 'mrz-product-filter-exp' ); ?></option>
			<option value="dropdown" <?php selected( $mode, 'dropdown' ); ?>><?php esc_html_e( 'Menu déroulant', 'mrz-product-filter-exp' ); ?></option>
		</select>

		<span class="mrz-product-filter-exp-source-logic-wrap">
			<select name="<?php echo esc_attr( $base . '[logic]' ); ?>" class="mrz-product-filter-exp-source-logic">
				<option value="or" <?php selected( $logic, 'or' ); ?>><?php esc_html_e( 'OU', 'mrz-product-filter-exp' ); ?></option>
				<option value="and" <?php selected( $logic, 'and' ); ?>><?php esc_html_e( 'ET', 'mrz-product-filter-exp' ); ?></option>
			</select>
		</span>

		<label class="mrz-product-filter-exp-source-open">
			<input type="checkbox" name="<?php echo esc_attr( $base . '[open]' ); ?>" value="1" <?php checked( $open ); ?> />
			<?php esc_html_e( 'Déplié', 'mrz-product-filter-exp' ); ?>
		</label>

		<button type="button" class="button-link mrz-product-filter-exp-source-remove" aria-label="<?php esc_attr_e( 'Retirer', 'mrz-product-filter-exp' ); ?>">&times;</button>
	</div>
	<?php
};
?>

<p class="description">
	<?php esc_html_e( 'Ajoutez les filtres à afficher (catégories, étiquettes, attributs). Glissez-déposez pour changer l\'ordre. L\'attribut choisi comme « couleur » ci-dessous s\'affiche automatiquement en pastilles.', 'mrz-product-filter-exp' ); ?>
</p>

<div class="mrz-product-filter-exp-sources" data-next-index="<?php echo esc_attr( count( $sources ) ); ?>">
	<?php foreach ( $sources as $i => $row ) : ?>
		<?php $render_row( $i, $row, $available ); ?>
	<?php endforeach; ?>
</div>

<p>
	<button type="button" class="button mrz-product-filter-exp-source-add">
		<?php esc_html_e( 'Ajouter un filtre', 'mrz-product-filter-exp' ); ?>
	</button>
</p>

<template id="mrz-product-filter-exp-source-template">
	<?php $render_row( '__INDEX__', array(), $available ); ?>
</template>
