<?php
/**
 * Enregistrement / enqueue conditionnel des assets front.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {

	const HANDLE_SCRIPT = 'mrz-product-filter-exp';
	const HANDLE_STYLE  = 'mrz-product-filter-exp';

	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		// Invalide le cache (fourchette de prix) quand un produit change.
		add_action( 'save_post_product', array( $this, 'invalidate_price_cache' ) );
		add_action( 'deleted_post', array( $this, 'invalidate_price_cache' ) );
	}

	public function register_assets() {
		wp_register_style(
			self::HANDLE_STYLE,
			MRZPFE_URL . 'public/css/public.css',
			array(),
			MRZPFE_VERSION
		);

		wp_register_script(
			self::HANDLE_SCRIPT,
			MRZPFE_URL . 'public/js/mrz-product-filter-exp.js',
			array( 'jquery', 'jquery-ui-slider' ),
			MRZPFE_VERSION,
			true
		);
	}

	/**
	 * Garantit l'enregistrement des assets même si le panneau est rendu avant
	 * wp_enqueue_scripts (widgets, auto-injection précoce).
	 */
	public function ensure_registered() {
		if ( ! wp_script_is( self::HANDLE_SCRIPT, 'registered' ) ) {
			$this->register_assets();
		}
	}

	/**
	 * Enqueue les assets pour un panneau rendu (shortcode ou auto-injection).
	 */
	public function enqueue_panel_assets() {
		$this->ensure_registered();
		wp_enqueue_script( self::HANDLE_SCRIPT );
		wp_enqueue_style( self::HANDLE_STYLE );
	}

	public function invalidate_price_cache() {
		delete_transient( DataProvider::CACHE_PREFIX . 'price_range' );
	}
}
