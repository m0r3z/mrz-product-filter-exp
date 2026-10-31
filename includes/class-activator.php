<?php
/**
 * Actions exécutées à l'activation du plugin.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {

	public static function activate() {
		if ( ! mrzpfe_has_woocommerce() ) {
			deactivate_plugins( MRZPFE_BASENAME );
			wp_die(
				esc_html__( 'MRZ Product Filter requires WooCommerce. Please install and activate WooCommerce before activating this plugin.', 'mrz-product-filter-exp' ),
				esc_html__( 'Missing dependency', 'mrz-product-filter-exp' ),
				array( 'back_link' => true )
			);
		}

		( new CPT() )->register_post_type();
		flush_rewrite_rules();
	}
}
