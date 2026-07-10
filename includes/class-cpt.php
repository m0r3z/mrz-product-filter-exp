<?php
/**
 * Enregistre le Custom Post Type des jeux de filtres.
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT {

	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_' . MRZ_PRODUCT_FILTER_EXP_CPT . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . MRZ_PRODUCT_FILTER_EXP_CPT . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_menu_icon_style' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Jeux de filtres', 'post type general name', 'mrz-product-filter-exp' ),
			'singular_name'      => _x( 'Jeu de filtres', 'post type singular name', 'mrz-product-filter-exp' ),
			'menu_name'          => _x( 'MRZ Product Filter', 'admin menu', 'mrz-product-filter-exp' ),
			'name_admin_bar'     => _x( 'Jeu de filtres', 'add new on admin bar', 'mrz-product-filter-exp' ),
			'add_new'            => _x( 'Ajouter', 'filter set', 'mrz-product-filter-exp' ),
			'add_new_item'       => __( 'Ajouter un jeu de filtres', 'mrz-product-filter-exp' ),
			'new_item'           => __( 'Nouveau jeu de filtres', 'mrz-product-filter-exp' ),
			'edit_item'          => __( 'Modifier le jeu de filtres', 'mrz-product-filter-exp' ),
			'view_item'          => __( 'Voir le jeu de filtres', 'mrz-product-filter-exp' ),
			'all_items'          => __( 'Tous les jeux de filtres', 'mrz-product-filter-exp' ),
			'search_items'       => __( 'Rechercher un jeu de filtres', 'mrz-product-filter-exp' ),
			'not_found'          => __( 'Aucun jeu de filtres trouvé.', 'mrz-product-filter-exp' ),
			'not_found_in_trash' => __( 'Aucun jeu de filtres dans la corbeille.', 'mrz-product-filter-exp' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'show_in_rest'       => false,
			'menu_icon'          => 'none',
			'menu_position'      => 90,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'query_var'          => false,
		);

		register_post_type( MRZ_PRODUCT_FILTER_EXP_CPT, $args );
	}

	/**
	 * Injecte l'icône du menu via CSS mask-image (couleur conforme au thème admin).
	 */
	public function enqueue_menu_icon_style() {
		$handle = 'mrz-product-filter-exp-menu-icon';
		wp_register_style( $handle, false, array(), MRZ_PRODUCT_FILTER_EXP_VERSION );
		wp_enqueue_style( $handle );

		$url = esc_url( MRZ_PRODUCT_FILTER_EXP_URL . 'assets/menu-icon.svg?ver=' . MRZ_PRODUCT_FILTER_EXP_VERSION );
		$sel = '#adminmenu li.menu-top[id*="' . MRZ_PRODUCT_FILTER_EXP_CPT . '"]';

		$css  = $sel . ' .wp-menu-image{background:none!important;background-color:rgba(240,246,252,.6)!important;';
		$css .= '-webkit-mask:url(\'' . $url . '\') no-repeat 9px 7px/20px;mask:url(\'' . $url . '\') no-repeat 9px 7px/20px;}';
		$css .= $sel . ' .wp-menu-image::before{display:none;}';
		$css .= $sel . ':hover .wp-menu-image,';
		$css .= $sel . '.wp-has-current-submenu .wp-menu-image,';
		$css .= $sel . '.current .wp-menu-image,';
		$css .= $sel . '.wp-menu-open .wp-menu-image{background-color:#fff!important;}';

		wp_add_inline_style( $handle, $css );
	}

	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['mrz_product_filter_exp_shortcode'] = __( 'Shortcode', 'mrz-product-filter-exp' );
			}
		}
		return $new;
	}

	public function column_content( $column, $post_id ) {
		if ( 'mrz_product_filter_exp_shortcode' !== $column ) {
			return;
		}
		printf(
			'<code>[mrz_product_filter id="%d"]</code>',
			(int) $post_id
		);
	}
}
