<?php
/**
 * Classe principale : bootstrap des modules.
 */

namespace Mrzpfe;

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

	/**
	 * Déclare le dossier languages/ du plugin comme source de traductions.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'mrz-product-filter-exp', false, dirname( MRZPFE_BASENAME ) . '/languages' );
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		// Traductions livrées dans languages/ (fr_FR notamment). Un pack de
		// translate.wordpress.org, s'il existe, reste prioritaire.
		add_action( 'init', array( $this, 'load_textdomain' ), 1 );

		if ( ! mrzpfe_has_woocommerce() ) {
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
		( new LoopDisplay() )->register();
	}

	public function notice_missing_woocommerce() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'MRZ Product Filter requires WooCommerce to work.', 'mrz-product-filter-exp' )
		);
	}
}
