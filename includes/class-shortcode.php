<?php
/**
 * Shortcode [mrz_product_filter id="X"].
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Shortcode {

	public function register() {
		add_shortcode( 'mrz_product_filter', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'mrz_product_filter'
		);

		$id = absint( $atts['id'] );
		if ( $id <= 0 || get_post_type( $id ) !== MRZ_PRODUCT_FILTER_EXP_CPT ) {
			return '';
		}

		return Renderer::render( $id );
	}
}
