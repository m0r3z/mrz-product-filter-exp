<?php
/**
 * Génère le HTML du panneau de filtres à partir d'une configuration.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Renderer {

	/**
	 * Rend le panneau de filtres pour un jeu de filtres donné.
	 *
	 * @param int $post_id ID du CPT jeu de filtres.
	 * @return string HTML (chaîne vide si config invalide).
	 */
	public static function render( $post_id ) {
		$config = DataProvider::get_config( $post_id );
		if ( null === $config || empty( $config['sections'] ) && empty( $config['price'] ) ) {
			return '';
		}

		( new Assets() )->enqueue_panel_assets();

		$uid    = 'mrz-product-filter-exp-' . (int) $config['id'] . '-' . wp_generate_uuid4();
		$chosen = self::get_chosen_values( $config );

		ob_start();
		include MRZPFE_DIR . 'public/views/filter-panel.php';
		return ob_get_clean();
	}

	/**
	 * Lit les valeurs actuellement sélectionnées depuis l'URL (fallback sans JS
	 * et cohérence à l'affichage). Aucune écriture, lecture seule des $_GET publics.
	 */
	private static function get_chosen_values( $config ) {
		$chosen = array(
			'terms'     => array(),
			'min_price' => '',
			'max_price' => '',
		);

		foreach ( $config['sections'] as $section ) {
			$param = $section['param'];
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'un filtre public en GET, valeurs assainies ci-dessous.
			$raw = isset( $_GET[ $param ] ) ? sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) : '';
			if ( '' === $raw ) {
				continue;
			}
			$slugs                       = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
			$chosen['terms'][ $param ]   = $slugs;
		}

		// Filtre prix public en GET : pas de nonce, valeur forcée en nombre.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( isset( $_GET['min_price'] ) ) {
			$chosen['min_price'] = (float) wp_unslash( $_GET['min_price'] );
		}
		if ( isset( $_GET['max_price'] ) ) {
			$chosen['max_price'] = (float) wp_unslash( $_GET['max_price'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return $chosen;
	}

	/**
	 * Indique si un slug de terme est actuellement sélectionné pour un paramètre.
	 */
	public static function is_chosen( $chosen, $param, $slug ) {
		return isset( $chosen['terms'][ $param ] ) && in_array( $slug, $chosen['terms'][ $param ], true );
	}
}
