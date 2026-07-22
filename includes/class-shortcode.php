<?php
/**
 * Shortcode [mrzpfe_product_filter id="X"].
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Shortcode {

	public function register() {
		add_shortcode( 'mrzpfe_product_filter', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'mrzpfe_product_filter'
		);

		$id = absint( $atts['id'] );
		if ( $id <= 0 || get_post_type( $id ) !== MRZPFE_CPT ) {
			return '';
		}

		return Renderer::render( $id );
	}
}
