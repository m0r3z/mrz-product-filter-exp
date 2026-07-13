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

$position     = (string) $values['position'];
$auto_targets = (array) $values['auto_targets'];
?>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Position', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="radio" name="mrz_product_filter_exp[position]" value="left" <?php checked( $position, 'left' ); ?> />
					<?php esc_html_e( 'Colonne à gauche', 'mrz-product-filter-exp' ); ?>
				</label>
				&nbsp;
				<label>
					<input type="radio" name="mrz_product_filter_exp[position]" value="top" <?php checked( $position, 'top' ); ?> />
					<?php esc_html_e( 'Au-dessus des produits', 'mrz-product-filter-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Auto-affichage', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_product_filter_exp[auto_display]" value="1" <?php checked( ! empty( $values['auto_display'] ) ); ?> />
					<?php esc_html_e( 'Afficher automatiquement ce jeu de filtres sur la boutique / les archives ci-dessous', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:8px;">
					<label style="margin-right:12px;">
						<input type="checkbox" name="mrz_product_filter_exp[auto_targets][]" value="shop" <?php checked( in_array( 'shop', $auto_targets, true ) ); ?> />
						<?php esc_html_e( 'Page boutique', 'mrz-product-filter-exp' ); ?>
					</label>
					<label style="margin-right:12px;">
						<input type="checkbox" name="mrz_product_filter_exp[auto_targets][]" value="product_cat" <?php checked( in_array( 'product_cat', $auto_targets, true ) ); ?> />
						<?php esc_html_e( 'Archives de catégories', 'mrz-product-filter-exp' ); ?>
					</label>
					<label>
						<input type="checkbox" name="mrz_product_filter_exp[auto_targets][]" value="product_tag" <?php checked( in_array( 'product_tag', $auto_targets, true ) ); ?> />
						<?php esc_html_e( 'Archives d\'étiquettes', 'mrz-product-filter-exp' ); ?>
					</label>
				</p>
				<p class="description">
					<?php esc_html_e( 'Sinon, placez le panneau manuellement avec le shortcode. Un seul jeu de filtres en auto-affichage est utilisé par contexte.', 'mrz-product-filter-exp' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Filtre par prix', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_product_filter_exp[enable_price]" value="1" <?php checked( ! empty( $values['enable_price'] ) ); ?> />
					<?php esc_html_e( 'Activer la réglette de prix', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:8px;">
					<input type="text" name="mrz_product_filter_exp[price_label]" value="<?php echo esc_attr( $values['price_label'] ); ?>" placeholder="<?php esc_attr_e( 'Titre du prix (optionnel)', 'mrz-product-filter-exp' ); ?>" class="regular-text" />
				</p>
				<label>
					<input type="checkbox" name="mrz_product_filter_exp[price_open]" value="1" <?php checked( ! empty( $values['price_open'] ) ); ?> />
					<?php esc_html_e( 'Section dépliée par défaut', 'mrz-product-filter-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Nombre de produits', 'mrz-product-filter-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_product_filter_exp[override_loop]" value="1" <?php checked( ! empty( $values['override_loop'] ) ); ?> />
					<?php esc_html_e( 'Contrôler le nombre de produits affichés (remplace le réglage du thème)', 'mrz-product-filter-exp' ); ?>
				</label>
				<p style="margin-top:10px;">
					<label>
						<?php esc_html_e( 'Produits par page', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrz_product_filter_exp[per_page]" value="<?php echo esc_attr( $values['per_page'] ); ?>" min="0" max="100" step="1" style="width:6em;" />
					</label>
					<span class="description"><?php esc_html_e( '(0 = ne pas modifier)', 'mrz-product-filter-exp' ); ?></span>
				</p>
				<p style="margin-top:6px;">
					<strong><?php esc_html_e( 'Colonnes par ligne :', 'mrz-product-filter-exp' ); ?></strong>
				</p>
				<p>
					<label style="margin-right:14px;">
						<?php esc_html_e( 'Grand écran', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrz_product_filter_exp[cols_wide]" value="<?php echo esc_attr( $values['cols_wide'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
					<label style="margin-right:14px;">
						<?php esc_html_e( 'Écran', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrz_product_filter_exp[cols_desktop]" value="<?php echo esc_attr( $values['cols_desktop'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
					<label style="margin-right:14px;">
						<?php esc_html_e( 'Tablette', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrz_product_filter_exp[cols_tablet]" value="<?php echo esc_attr( $values['cols_tablet'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
					<label>
						<?php esc_html_e( 'Mobile', 'mrz-product-filter-exp' ); ?>
						<input type="number" name="mrz_product_filter_exp[cols_mobile]" value="<?php echo esc_attr( $values['cols_mobile'] ); ?>" min="1" max="8" step="1" style="width:5em;" />
					</label>
				</p>
				<p class="description">
					<?php
					printf(
						/* translators: 1: wide breakpoint, 2: tablet breakpoint, 3: mobile breakpoint (pixels). */
						esc_html__( 'Paliers : grand écran ≥ %1$d px, tablette ≤ %2$d px, mobile ≤ %3$d px. S\'applique sur les cibles cochées dans « Auto-affichage » ci-dessus (boutique / archives).', 'mrz-product-filter-exp' ),
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
					<input type="checkbox" name="mrz_product_filter_exp[default_open]" value="1" <?php checked( ! empty( $values['default_open'] ) ); ?> />
					<?php esc_html_e( 'Sections dépliées par défaut (sauf réglage par section)', 'mrz-product-filter-exp' ); ?>
				</label>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="mrz_product_filter_exp[show_counts]" value="1" <?php checked( ! empty( $values['show_counts'] ) ); ?> />
					<?php esc_html_e( 'Afficher le nombre de produits par terme', 'mrz-product-filter-exp' ); ?>
				</label>
				<label style="display:block;margin-bottom:6px;">
					<input type="checkbox" name="mrz_product_filter_exp[show_reset]" value="1" <?php checked( ! empty( $values['show_reset'] ) ); ?> />
					<?php esc_html_e( 'Afficher le bouton de réinitialisation', 'mrz-product-filter-exp' ); ?>
				</label>
				<input type="text" name="mrz_product_filter_exp[reset_text]" value="<?php echo esc_attr( $values['reset_text'] ); ?>" placeholder="<?php esc_attr_e( 'Texte du bouton (par défaut : Réinitialiser)', 'mrz-product-filter-exp' ); ?>" class="regular-text" />
			</td>
		</tr>
	</tbody>
</table>
