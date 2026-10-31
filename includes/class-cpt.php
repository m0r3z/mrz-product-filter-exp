<?php
/**
 * Enregistre le Custom Post Type des jeux de filtres.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPT {

	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_' . MRZPFE_CPT . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . MRZPFE_CPT . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_menu_icon_style' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Filter sets', 'post type general name', 'mrz-product-filter-exp' ),
			'singular_name'      => _x( 'Filter set', 'post type singular name', 'mrz-product-filter-exp' ),
			'menu_name'          => _x( 'MRZ Product Filter', 'admin menu', 'mrz-product-filter-exp' ),
			'name_admin_bar'     => _x( 'Filter set', 'add new on admin bar', 'mrz-product-filter-exp' ),
			'add_new'            => _x( 'Add', 'filter set', 'mrz-product-filter-exp' ),
			'add_new_item'       => __( 'Add filter set', 'mrz-product-filter-exp' ),
			'new_item'           => __( 'New filter set', 'mrz-product-filter-exp' ),
			'edit_item'          => __( 'Edit filter set', 'mrz-product-filter-exp' ),
			'view_item'          => __( 'View filter set', 'mrz-product-filter-exp' ),
			'all_items'          => __( 'All filter sets', 'mrz-product-filter-exp' ),
			'search_items'       => __( 'Search filter sets', 'mrz-product-filter-exp' ),
			'not_found'          => __( 'No filter sets found.', 'mrz-product-filter-exp' ),
			'not_found_in_trash' => __( 'No filter sets found in Trash.', 'mrz-product-filter-exp' ),
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

		register_post_type( MRZPFE_CPT, $args );
	}

	/**
	 * Injecte l'icône du menu via CSS mask-image (couleur conforme au thème admin).
	 */
	public function enqueue_menu_icon_style() {
		$handle = 'mrz-product-filter-exp-menu-icon';
		wp_register_style( $handle, false, array(), MRZPFE_VERSION );
		wp_enqueue_style( $handle );

		$url = esc_url( MRZPFE_URL . 'assets/menu-icon.svg?ver=' . MRZPFE_VERSION );
		$sel = '#adminmenu li.menu-top[id*="' . MRZPFE_CPT . '"]';

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
				$new['mrzpfe_shortcode'] = __( 'Shortcode', 'mrz-product-filter-exp' );
			}
		}
		return $new;
	}

	public function column_content( $column, $post_id ) {
		if ( 'mrzpfe_shortcode' !== $column ) {
			return;
		}
		printf(
			'<code>[mrzpfe_product_filter id="%d"]</code>',
			(int) $post_id
		);
	}
}
