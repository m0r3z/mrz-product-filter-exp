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
<p><?php esc_html_e( 'Paste this shortcode to display this filter set manually:', 'mrz-product-filter-exp' ); ?></p>
<p>
	<code>[mrzpfe_product_filter id="<?php echo (int) $post->ID; ?>"]</code>
</p>
<p class="description">
	<?php esc_html_e( 'Or enable automatic display in the “Display” meta box to insert it into the shop automatically.', 'mrz-product-filter-exp' ); ?>
</p>
