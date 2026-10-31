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

use Mrzpfe\DataProvider;
use Mrzpfe\TermColors;

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
				<label for="mrzpfe_color_attribute"><?php esc_html_e( 'Color attribute', 'mrz-product-filter-exp' ); ?></label>
			</th>
			<td>
				<?php if ( empty( $attributes ) ) : ?>
					<p class="description"><?php esc_html_e( 'No global product attributes are defined. Create one under Products → Attributes.', 'mrz-product-filter-exp' ); ?></p>
				<?php else : ?>
					<select name="mrzpfe[color_attribute]" id="mrzpfe_color_attribute">
						<option value=""><?php esc_html_e( '— None —', 'mrz-product-filter-exp' ); ?></option>
						<?php foreach ( $attributes as $tax => $label ) : ?>
							<option value="<?php echo esc_attr( $tax ); ?>" <?php selected( $color_attribute, $tax ); ?>>
								<?php echo esc_html( $label . ' (' . $tax . ')' ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'This attribute will be displayed as color swatches in the filter (if it is added to the filter sources).', 'mrz-product-filter-exp' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Color per term', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<div
					id="mrzpfe_term_colors"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'mrzpfe_fetch_terms' ) ); ?>"
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php if ( '' === $color_attribute ) : ?>
						<p class="description"><?php esc_html_e( 'Select an attribute above first.', 'mrz-product-filter-exp' ); ?></p>
					<?php elseif ( empty( $initial_terms ) ) : ?>
						<p class="description"><?php esc_html_e( 'This attribute has no terms.', 'mrz-product-filter-exp' ); ?></p>
					<?php else : ?>
						<?php foreach ( $initial_terms as $term ) : ?>
							<?php $color = (string) get_term_meta( $term->term_id, TermColors::META_KEY, true ); ?>
							<div class="mrz-product-filter-exp-term-row" data-term-id="<?php echo (int) $term->term_id; ?>">
								<span class="mrz-product-filter-exp-term-name"><?php echo esc_html( $term->name ); ?></span>
								<input
									type="text"
									name="mrzpfe[term_colors][<?php echo (int) $term->term_id; ?>]"
									value="<?php echo esc_attr( $color ); ?>"
									class="mrz-product-filter-exp-color-picker" />
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<p class="description">
					<?php esc_html_e( 'The color is saved on the term itself (reusable). You can also set it under Products → Attributes → (attribute) → term.', 'mrz-product-filter-exp' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>

<template id="mrz-product-filter-exp-term-color-template">
	<div class="mrz-product-filter-exp-term-row" data-term-id="__TERM_ID__">
		<span class="mrz-product-filter-exp-term-name">__TERM_NAME__</span>
		<input type="text" name="mrzpfe[term_colors][__TERM_ID__]" value="__COLOR__" class="mrz-product-filter-exp-color-picker" />
	</div>
</template>
