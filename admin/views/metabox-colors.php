<?php
/**
 * Métabox : pastilles de couleur (attribut couleur + couleur par terme).
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MrzProductFilterExp\DataProvider;
use MrzProductFilterExp\TermColors;

$attributes      = DataProvider::get_attribute_taxonomies();
$color_attribute = (string) $values['color_attribute'];

$initial_terms = array();
if ( '' !== $color_attribute && taxonomy_exists( $color_attribute ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $color_attribute,
			'hide_empty' => false,
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		$initial_terms = $terms;
	}
}
?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrz_product_filter_exp_color_attribute"><?php esc_html_e( 'Attribut « couleur »', 'mrz-product-filter-exp' ); ?></label>
			</th>
			<td>
				<?php if ( empty( $attributes ) ) : ?>
					<p class="description"><?php esc_html_e( 'Aucun attribut de produit global n\'est défini. Créez-en un dans Produits → Attributs.', 'mrz-product-filter-exp' ); ?></p>
				<?php else : ?>
					<select name="mrz_product_filter_exp[color_attribute]" id="mrz_product_filter_exp_color_attribute">
						<option value=""><?php esc_html_e( '— Aucun —', 'mrz-product-filter-exp' ); ?></option>
						<?php foreach ( $attributes as $tax => $label ) : ?>
							<option value="<?php echo esc_attr( $tax ); ?>" <?php selected( $color_attribute, $tax ); ?>>
								<?php echo esc_html( $label . ' (' . $tax . ')' ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Cet attribut sera rendu en pastilles de couleur dans le filtre (s\'il est ajouté dans les sources).', 'mrz-product-filter-exp' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Couleur par terme', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<div
					id="mrz_product_filter_exp_term_colors"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'mrz_product_filter_exp_fetch_terms' ) ); ?>"
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php if ( '' === $color_attribute ) : ?>
						<p class="description"><?php esc_html_e( 'Sélectionnez d\'abord un attribut ci-dessus.', 'mrz-product-filter-exp' ); ?></p>
					<?php elseif ( empty( $initial_terms ) ) : ?>
						<p class="description"><?php esc_html_e( 'Aucun terme dans cet attribut.', 'mrz-product-filter-exp' ); ?></p>
					<?php else : ?>
						<?php foreach ( $initial_terms as $term ) : ?>
							<?php $color = (string) get_term_meta( $term->term_id, TermColors::META_KEY, true ); ?>
							<div class="mrz-product-filter-exp-term-row" data-term-id="<?php echo (int) $term->term_id; ?>">
								<span class="mrz-product-filter-exp-term-name"><?php echo esc_html( $term->name ); ?></span>
								<input
									type="text"
									name="mrz_product_filter_exp[term_colors][<?php echo (int) $term->term_id; ?>]"
									value="<?php echo esc_attr( $color ); ?>"
									class="mrz-product-filter-exp-color-picker" />
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<p class="description">
					<?php esc_html_e( 'La couleur est enregistrée sur le terme lui-même (réutilisable). Vous pouvez aussi la définir depuis Produits → Attributs → (attribut) → terme.', 'mrz-product-filter-exp' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>

<template id="mrz-product-filter-exp-term-color-template">
	<div class="mrz-product-filter-exp-term-row" data-term-id="__TERM_ID__">
		<span class="mrz-product-filter-exp-term-name">__TERM_NAME__</span>
		<input type="text" name="mrz_product_filter_exp[term_colors][__TERM_ID__]" value="__COLOR__" class="mrz-product-filter-exp-color-picker" />
	</div>
</template>
