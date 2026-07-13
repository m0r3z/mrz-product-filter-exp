<?php
/**
 * Vue : panneau de filtres produits.
 *
 * @var array  $config  Configuration résolue (DataProvider::get_config).
 * @var string $uid     Identifiant unique de l'instance.
 * @var array  $chosen  Valeurs actuellement sélectionnées (depuis l'URL).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MrzProductFilterExp\Renderer;

// Liste de tous les paramètres gérés, pour construire l'URL de réinitialisation.
$all_params = array( 'min_price', 'max_price', 'paged' );
foreach ( $config['sections'] as $s ) {
	$all_params[] = $s['param'];
}
$reset_url = remove_query_arg( $all_params );
?>
<div
	class="mrz-product-filter-exp mrz-product-filter-exp--<?php echo esc_attr( $config['position'] ); ?>"
	id="<?php echo esc_attr( $uid ); ?>"
	data-mrz-product-filter-exp="1"
>
	<button type="button" class="mrz-product-filter-exp-toggle" aria-expanded="false">
		<?php esc_html_e( 'Filtres', 'mrz-product-filter-exp' ); ?>
	</button>
	<form class="mrz-product-filter-exp-form" method="get" action="">
		<?php foreach ( $config['sections'] as $section ) : ?>
			<?php
			$param = $section['param'];
			$mode  = $section['mode'];
			// Pas de suffixe []: en fallback sans JS, un paramètre scalaire reste
			// compatible avec le filtrage natif WooCommerce (qui attend une chaîne).
			// La sélection multiple est recomposée en liste séparée par des virgules par le JS.
			$field_name = $param;
			$is_attr    = ( 0 === strpos( $param, 'filter_' ) );
			?>
			<details class="mrz-product-filter-exp-section" data-param="<?php echo esc_attr( $param ); ?>" data-logic="<?php echo esc_attr( $section['logic'] ); ?>"<?php echo $section['open'] ? ' open' : ''; ?>>
				<summary class="mrz-product-filter-exp-title"><?php echo esc_html( $section['label'] ); ?></summary>
				<?php if ( $is_attr ) : ?>
					<?php // Logique OU/ET pour cet attribut (fallback sans JS ; le JS le repositionne). ?>
					<input type="hidden" name="query_type_<?php echo esc_attr( substr( $param, 7 ) ); ?>" value="<?php echo esc_attr( $section['logic'] ); ?>" />
				<?php endif; ?>

				<?php if ( 'dropdown' === $mode ) : ?>
					<div class="mrz-product-filter-exp-options">
						<select name="<?php echo esc_attr( $param ); ?>" class="mrz-product-filter-exp-select">
							<option value=""><?php esc_html_e( '— Tous —', 'mrz-product-filter-exp' ); ?></option>
							<?php foreach ( $section['terms'] as $term ) : ?>
								<option value="<?php echo esc_attr( $term['slug'] ); ?>" <?php selected( Renderer::is_chosen( $chosen, $param, $term['slug'] ) ); ?>>
									<?php
									echo esc_html( $term['name'] );
									if ( $config['show_counts'] ) {
										echo esc_html( ' (' . $term['count'] . ')' );
									}
									?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

				<?php elseif ( 'color' === $mode ) : ?>
					<div class="mrz-product-filter-exp-options mrz-product-filter-exp-swatches">
						<?php foreach ( $section['terms'] as $term ) : ?>
							<?php $color = isset( $term['color'] ) ? $term['color'] : ''; ?>
							<label class="mrz-product-filter-exp-swatch" title="<?php echo esc_attr( $term['name'] ); ?>">
								<input
									type="checkbox"
									name="<?php echo esc_attr( $field_name ); ?>"
									value="<?php echo esc_attr( $term['slug'] ); ?>"
									<?php checked( Renderer::is_chosen( $chosen, $param, $term['slug'] ) ); ?>
								/>
								<span class="mrz-product-filter-exp-dot"<?php echo '' !== $color ? ' style="background-color:' . esc_attr( $color ) . '"' : ''; ?>></span>
							</label>
						<?php endforeach; ?>
					</div>

				<?php else : ?>
					<?php $input_type = ( 'radio' === $mode ) ? 'radio' : 'checkbox'; ?>
					<ul class="mrz-product-filter-exp-options mrz-product-filter-exp-list">
						<?php if ( 'radio' === $mode ) : ?>
							<li class="mrz-product-filter-exp-option">
								<label>
									<input type="radio" name="<?php echo esc_attr( $param ); ?>" value="" <?php checked( empty( $chosen['terms'][ $param ] ) ); ?> />
									<span><?php esc_html_e( '— Tous —', 'mrz-product-filter-exp' ); ?></span>
								</label>
							</li>
						<?php endif; ?>
						<?php foreach ( $section['terms'] as $term ) : ?>
							<li class="mrz-product-filter-exp-option">
								<label>
									<input
										type="<?php echo esc_attr( $input_type ); ?>"
										name="<?php echo esc_attr( $field_name ); ?>"
										value="<?php echo esc_attr( $term['slug'] ); ?>"
										<?php checked( Renderer::is_chosen( $chosen, $param, $term['slug'] ) ); ?>
									/>
									<span class="mrz-product-filter-exp-option-label"><?php echo esc_html( $term['name'] ); ?></span>
									<?php if ( $config['show_counts'] ) : ?>
										<span class="mrz-product-filter-exp-count"><?php echo esc_html( '(' . $term['count'] . ')' ); ?></span>
									<?php endif; ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</details>
		<?php endforeach; ?>

		<?php if ( ! empty( $config['price'] ) ) : ?>
			<?php
			$price     = $config['price'];
			$cur_min   = ( '' !== $chosen['min_price'] ) ? (int) $chosen['min_price'] : $price['min'];
			$cur_max   = ( '' !== $chosen['max_price'] ) ? (int) $chosen['max_price'] : $price['max'];
			?>
			<details class="mrz-product-filter-exp-section mrz-product-filter-exp-price" data-min="<?php echo esc_attr( $price['min'] ); ?>" data-max="<?php echo esc_attr( $price['max'] ); ?>"<?php echo $price['open'] ? ' open' : ''; ?>>
				<summary class="mrz-product-filter-exp-title"><?php echo esc_html( $price['label'] ); ?></summary>
				<div class="mrz-product-filter-exp-options">
					<div class="mrz-product-filter-exp-price-slider" aria-hidden="true"></div>
					<div class="mrz-product-filter-exp-price-fields">
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Prix minimum', 'mrz-product-filter-exp' ); ?></span>
							<input type="number" class="mrz-product-filter-exp-price-min" name="min_price" value="<?php echo esc_attr( $cur_min ); ?>" min="<?php echo esc_attr( $price['min'] ); ?>" max="<?php echo esc_attr( $price['max'] ); ?>" step="1" />
						</label>
						<span class="mrz-product-filter-exp-price-sep">–</span>
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Prix maximum', 'mrz-product-filter-exp' ); ?></span>
							<input type="number" class="mrz-product-filter-exp-price-max" name="max_price" value="<?php echo esc_attr( $cur_max ); ?>" min="<?php echo esc_attr( $price['min'] ); ?>" max="<?php echo esc_attr( $price['max'] ); ?>" step="1" />
						</label>
					</div>
				</div>
			</details>
		<?php endif; ?>

		<div class="mrz-product-filter-exp-actions">
			<button type="submit" class="mrz-product-filter-exp-submit button"><?php esc_html_e( 'Filtrer', 'mrz-product-filter-exp' ); ?></button>
			<?php if ( $config['show_reset'] ) : ?>
				<a class="mrz-product-filter-exp-reset" href="<?php echo esc_url( $reset_url ); ?>"><?php echo esc_html( $config['reset_text'] ); ?></a>
			<?php endif; ?>
		</div>
	</form>
</div>
