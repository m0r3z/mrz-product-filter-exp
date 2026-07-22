<?php
/**
 * Métabox : shortcode.
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'Collez ce shortcode pour afficher ce jeu de filtres manuellement :', 'mrz-product-filter-exp' ); ?></p>
<p>
	<code>[mrzpfe_product_filter id="<?php echo (int) $post->ID; ?>"]</code>
</p>
<p class="description">
	<?php esc_html_e( 'Ou activez l\'auto-affichage dans la métabox « Affichage » pour l\'injecter automatiquement sur la boutique.', 'mrz-product-filter-exp' ); ?>
</p>
