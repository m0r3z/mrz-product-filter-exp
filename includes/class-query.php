<?php
/**
 * Filtrage de la requête produits sur la boutique et les archives.
 *
 * Les attributs (filter_*) et le prix (min_price/max_price) sont gérés
 * nativement par WooCommerce ; on ajoute ici les filtres catégorie/étiquette
 * propres au plugin. Le front récupère la vraie page filtrée en AJAX (fetch)
 * puis remplace la boucle produits : aucune requête reconstruite côté serveur.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Query {

	public function register() {
		add_action( 'woocommerce_product_query', array( $this, 'filter_main_query' ) );
	}

	/**
	 * Ajoute les catégories/étiquettes choisies à la requête principale.
	 */
	public function filter_main_query( $q ) {
		$tax_query = (array) $q->get( 'tax_query' );

		foreach ( array(
			'product_cat'   => 'mpf_product_cat',
			'product_tag'   => 'mpf_product_tag',
			'product_brand' => 'mpf_product_brand',
		) as $taxonomy => $param ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			$slugs = $this->get_slugs_param( $param );
			if ( ! empty( $slugs ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slugs,
					'operator' => 'IN',
				);
			}
		}

		$q->set( 'tax_query', $tax_query );
	}

	/**
	 * Récupère un paramètre « liste de slugs séparés par des virgules » depuis
	 * la requête (GET de l'archive ou fallback formulaire).
	 */
	private function get_slugs_param( $param ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtre public en lecture.
		if ( ! isset( $_GET[ $param ] ) ) {
			return array();
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$raw = wp_unslash( $_GET[ $param ] );

		$parts = is_array( $raw ) ? $raw : explode( ',', (string) $raw );

		$slugs = array();
		foreach ( $parts as $part ) {
			$slug = sanitize_title( $part );
			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}
		return array_values( array_unique( $slugs ) );
	}
}
