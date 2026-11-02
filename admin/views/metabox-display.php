<?php
/**
 * Métabox : affichage (position, auto-injection, prix, options).
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Vue incluse depuis une méthode : ses variables sont locales, pas globales.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$position     = (string) $values['position'];
$auto_targets = (array) $values['auto_targets'];
?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Position', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="radio" name="mrzpfe[position]" value="left" <?php checked( $position, 'left' ); ?> />
					<?php esc_html_e( 'Left column', 'mrz-product-filter-exp' ); ?>
				</label>
				&nbsp;
				<label>
					<input type="radio" name="mrzpfe[position]" value="top" <?php checked( $position, 'top' ); ?> />
					<?php esc_html_e( 'Above products', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:10px;">
					<label>
						<input type="checkbox" name="mrzpfe[sticky]" value="1" <?php checked( ! empty( $values['sticky'] ) ); ?> />
						<?php esc_html_e( 'Sticky panel: stays visible while scrolling (left column only)', 'mrz-product-filter-exp' ); ?>
					</label>
				</p>
				<p>
					<label>
						<?php esc_html_e( 'Top offset (px)', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrzpfe[sticky_offset]" value="<?php echo esc_attr( $values['sticky_offset'] ); ?>" min="0" max="400" step="1" style="width:6em;" />
					</label>
					<span class="description"><?php esc_html_e( 'Space above the panel once it sticks (e.g. to clear a fixed header).', 'mrz-product-filter-exp' ); ?></span>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Automatic display', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzpfe[auto_display]" value="1" <?php checked( ! empty( $values['auto_display'] ) ); ?> />
					<?php esc_html_e( 'Automatically display this filter set on the shop / archives below', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:8px;">
					<label style="margin-right:12px;">
						<input type="checkbox" name="mrzpfe[auto_targets][]" value="shop" <?php checked( in_array( 'shop', $auto_targets, true ) ); ?> />
						<?php esc_html_e( 'Shop page', 'mrz-product-filter-exp' ); ?>
					</label>
					<label style="margin-right:12px;">
						<input type="checkbox" name="mrzpfe[auto_targets][]" value="product_cat" <?php checked( in_array( 'product_cat', $auto_targets, true ) ); ?> />
						<?php esc_html_e( 'Category archives', 'mrz-product-filter-exp' ); ?>
					</label>
					<label>
						<input type="checkbox" name="mrzpfe[auto_targets][]" value="product_tag" <?php checked( in_array( 'product_tag', $auto_targets, true ) ); ?> />
						<?php esc_html_e( 'Tag archives', 'mrz-product-filter-exp' ); ?>
					</label>
				</p>
				<p class="description">
					<?php esc_html_e( 'Otherwise, place the panel manually with the shortcode. Only one automatically displayed filter set is used per context.', 'mrz-product-filter-exp' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Price filter', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzpfe[enable_price]" value="1" <?php checked( ! empty( $values['enable_price'] ) ); ?> />
					<?php esc_html_e( 'Enable the price slider', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:8px;">
					<input type="text" name="mrzpfe[price_label]" value="<?php echo esc_attr( $values['price_label'] ); ?>" placeholder="<?php esc_attr_e( 'Price title (optional)', 'mrz-product-filter-exp' ); ?>" class="regular-text" />
				</p>
				<label>
					<input type="checkbox" name="mrzpfe[price_open]" value="1" <?php checked( ! empty( $values['price_open'] ) ); ?> />
					<?php esc_html_e( 'Section expanded by default', 'mrz-product-filter-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Number of products', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzpfe[override_loop]" value="1" <?php checked( ! empty( $values['override_loop'] ) ); ?> />
					<?php esc_html_e( 'Control the number of products displayed (overrides the theme setting)', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:10px;">
					<label>
						<?php esc_html_e( 'Products per page', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrzpfe[per_page]" value="<?php echo esc_attr( $values['per_page'] ); ?>" min="0" max="100" step="1" style="width:6em;" />
					</label>
					<span class="description"><?php esc_html_e( '(0 = do not change)', 'mrz-product-filter-exp' ); ?></span>
				</p>
				<p style="margin-top:6px;">
					<strong><?php esc_html_e( 'Columns per row:', 'mrz-product-filter-exp' ); ?></strong>
				</p>
				<p>
					<label style="margin-right:14px;">
						<?php esc_html_e( 'Wide screen', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrzpfe[cols_wide]" value="<?php echo esc_attr( $values['cols_wide'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
					<label style="margin-right:14px;">
						<?php esc_html_e( 'Desktop', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrzpfe[cols_desktop]" value="<?php echo esc_attr( $values['cols_desktop'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
					<label style="margin-right:14px;">
						<?php esc_html_e( 'Tablet', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrzpfe[cols_tablet]" value="<?php echo esc_attr( $values['cols_tablet'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
					<label>
						<?php esc_html_e( 'Mobile', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrzpfe[cols_mobile]" value="<?php echo esc_attr( $values['cols_mobile'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
				</p>
				<p class="description">
					<?php
					printf(
						/* translators: 1: wide breakpoint, 2: tablet breakpoint, 3: mobile breakpoint (pixels). */
						esc_html__( 'Breakpoints: wide screen ≥ %1$d px, tablet ≤ %2$d px, mobile ≤ %3$d px. Applies to the targets checked under “Automatic display” above (shop / archives).', 'mrz-product-filter-exp' ),
						1600,
						1024,
						767
					);
					?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Options', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="mrzpfe[default_open]" value="1" <?php checked( ! empty( $values['default_open'] ) ); ?> />
					<?php esc_html_e( 'Sections expanded by default (unless set per section)', 'mrz-product-filter-exp' ); ?>
				</label>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="mrzpfe[show_counts]" value="1" <?php checked( ! empty( $values['show_counts'] ) ); ?> />
					<?php esc_html_e( 'Show the product count for each term', 'mrz-product-filter-exp' ); ?>
				</label>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="mrzpfe[show_reset]" value="1" <?php checked( ! empty( $values['show_reset'] ) ); ?> />
					<?php esc_html_e( 'Show the reset button', 'mrz-product-filter-exp' ); ?>
				</label>
				<input type="text" name="mrzpfe[reset_text]" value="<?php echo esc_attr( $values['reset_text'] ); ?>" placeholder="<?php esc_attr_e( 'Button text (default: Reset filters)', 'mrz-product-filter-exp' ); ?>" class="regular-text" />
			</td>
		</tr>
	</tbody>
</table>
