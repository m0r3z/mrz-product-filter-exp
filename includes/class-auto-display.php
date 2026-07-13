<?php
/**
 * Auto-injection du panneau de filtres sur la boutique et les archives produits.
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AutoDisplay {

	/**
	 * @var int
	 */
	private $active_id = 0;

	/**
	 * @var string
	 */
	private $position = 'left';

	public function register() {
		add_action( 'template_redirect', array( $this, 'setup' ) );
	}

	public function setup() {
		if ( is_admin() ) {
			return;
		}

		$target = $this->current_target();
		if ( '' === $target ) {
			return;
		}

		$id = $this->find_filter_set( $target );
		if ( ! $id ) {
			return;
		}

		$values         = FilterConfig::get_values( $id );
		$this->active_id = $id;
		$this->position  = in_array( $values['position'], FilterConfig::positions(), true ) ? $values['position'] : 'left';

		if ( 'left' === $this->position ) {
			// Enveloppe result-count / tri / boucle / pagination dans une colonne
			// à côté du panneau (mise en page flex).
			add_action( 'woocommerce_before_shop_loop', array( $this, 'open_layout' ), 5 );
			add_action( 'woocommerce_after_shop_loop', array( $this, 'close_layout' ), 999 );
		} else {
			add_action( 'woocommerce_before_shop_loop', array( $this, 'render_panel' ), 5 );
		}
	}

	public function open_layout() {
		echo '<div class="mrz-product-filter-exp-layout mrz-product-filter-exp-layout--left"><aside class="mrz-product-filter-exp-aside">';
		$this->render_panel();
		echo '</aside><div class="mrz-product-filter-exp-main">';
	}

	public function close_layout() {
		echo '</div></div>';
	}

	public function render_panel() {
		// HTML de confiance généré par Renderer (valeurs déjà échappées dans la vue).
		echo Renderer::render( $this->active_id ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
	}

	/**
	 * Cible d'auto-affichage correspondant au contexte courant.
	 */
	private function current_target() {
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			return 'shop';
		}
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			return 'product_cat';
		}
		if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
			return 'product_tag';
		}
		return '';
	}

	/**
	 * Cherche un jeu de filtres en auto-affichage pour la cible donnée.
	 */
	private function find_filter_set( $target ) {
		return DataProvider::find_set_for_target( $target, '_mrz_product_filter_exp_auto_display' );
	}
}
