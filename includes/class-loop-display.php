<?php
/**
 * Contrôle du nombre de produits affichés : produits par page (loop_shop_per_page)
 * et nombre de colonnes par palier responsive (écran / tablette / mobile).
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LoopDisplay {

	/**
	 * Points de rupture (en px).
	 */
	const BP_WIDE   = 1400; // min-width : grand écran.
	const BP_TABLET = 1024; // max-width : tablette.
	const BP_MOBILE = 767;  // max-width : mobile.

	/**
	 * @var bool
	 */
	private $resolved = false;

	/**
	 * @var array|null
	 */
	private $set = null;

	public function register() {
		// Nombre de produits par page : on force posts_per_page en pre_get_posts
		// avec une priorité tardive, pour primer sur le filtre loop_shop_per_page
		// que certains thèmes (ex. Salient) posent eux-mêmes.
		add_action( 'pre_get_posts', array( $this, 'set_per_page' ), 999 );
		add_filter( 'loop_shop_columns', array( $this, 'filter_columns' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ), 20 );
	}

	/**
	 * Force le nombre de produits par page sur la requête principale de la boutique.
	 */
	public function set_per_page( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		$set = $this->active_set();
		if ( ! $set || empty( $set['override_loop'] ) || (int) $set['per_page'] < 1 ) {
			return;
		}
		$q->set( 'posts_per_page', (int) $set['per_page'] );
	}

	/**
	 * Nombre de colonnes (classe columns-N + propriétés de boucle WooCommerce).
	 */
	public function filter_columns( $columns ) {
		$set = $this->active_set();
		if ( $set && ! empty( $set['override_loop'] ) && (int) $set['cols_desktop'] > 0 ) {
			return (int) $set['cols_desktop'];
		}
		return $columns;
	}

	/**
	 * Injecte la grille CSS responsive pour les colonnes.
	 */
	public function enqueue_css() {
		$set = $this->active_set();
		if ( ! $set || empty( $set['override_loop'] ) ) {
			return;
		}

		$wide    = max( 1, (int) $set['cols_wide'] );
		$desktop = max( 1, (int) $set['cols_desktop'] );
		$tablet  = max( 1, (int) $set['cols_tablet'] );
		$mobile  = max( 1, (int) $set['cols_mobile'] );

		// Base = écran standard, puis min-width (grand écran) et max-width
		// (tablette/mobile). Les plages ne se chevauchent pas.
		$grid  = 'ul.products{display:grid !important;grid-template-columns:repeat(%1$d,minmax(0,1fr)) !important;gap:24px !important;}';
		$grid .= 'ul.products::before,ul.products::after{content:none !important;display:none !important;}';
		$grid .= 'ul.products>li.product,ul.products>li{width:auto !important;max-width:none !important;margin:0 !important;float:none !important;clear:none !important;}';
		$grid .= '@media(min-width:%2$dpx){ul.products{grid-template-columns:repeat(%3$d,minmax(0,1fr)) !important;}}';
		$grid .= '@media(max-width:%4$dpx){ul.products{grid-template-columns:repeat(%5$d,minmax(0,1fr)) !important;}}';
		$grid .= '@media(max-width:%6$dpx){ul.products{grid-template-columns:repeat(%7$d,minmax(0,1fr)) !important;}}';

		$css = sprintf(
			$grid,
			$desktop,
			self::BP_WIDE,
			$wide,
			self::BP_TABLET,
			$tablet,
			self::BP_MOBILE,
			$mobile
		);

		$handle = 'mrz-product-filter-exp-loop';
		wp_register_style( $handle, false, array(), MRZ_PRODUCT_FILTER_EXP_VERSION );
		wp_enqueue_style( $handle );
		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Jeu de filtres actif pour le contexte courant (mémoïsé), avec override activé.
	 */
	private function active_set() {
		if ( $this->resolved ) {
			return $this->set;
		}
		$this->resolved = true;

		if ( is_admin() ) {
			return $this->set;
		}

		$target = $this->current_target();
		if ( '' === $target ) {
			return $this->set;
		}

		$id = DataProvider::find_set_for_target( $target, '_mrz_product_filter_exp_override_loop' );
		if ( $id ) {
			$this->set = FilterConfig::get_values( $id );
		}
		return $this->set;
	}

	/**
	 * Cible correspondant au contexte courant.
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
}
