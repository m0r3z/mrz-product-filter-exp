<?php
/**
 * Classe principale : bootstrap des modules.
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	/**
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var bool
	 */
	private $booted = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function boot() {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		// Pas de load_plugin_textdomain() : depuis WordPress 4.6, les traductions
		// hébergées sur translate.wordpress.org sont chargées automatiquement par
		// le cœur pour les plugins publiés sur le répertoire officiel.

		if ( ! mrz_product_filter_exp_has_woocommerce() ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_woocommerce' ) );
			return;
		}

		( new CPT() )->register();
		( new FilterConfig() )->register();
		( new TermColors() )->register();
		( new Assets() )->register();
		( new Shortcode() )->register();
		( new AutoDisplay() )->register();
		( new Query() )->register();
	}

	public function notice_missing_woocommerce() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'MRZ Product Filter nécessite WooCommerce pour fonctionner.', 'mrz-product-filter-exp' )
		);
	}
}
