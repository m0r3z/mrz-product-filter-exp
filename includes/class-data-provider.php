<?php
/**
 * Fournit les données de filtres (sources, termes, couleurs, prix) et
 * construit la configuration consommée par le rendu.
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DataProvider {

	const CACHE_PREFIX = 'mrz_product_filter_exp_';

	/**
	 * Liste des attributs de produits globaux : [ pa_slug => label ].
	 */
	public static function get_attribute_taxonomies() {
		$out = array();
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return $out;
		}
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
			if ( taxonomy_exists( $taxonomy ) ) {
				$out[ $taxonomy ] = $attribute->attribute_label ? $attribute->attribute_label : $attribute->attribute_name;
			}
		}
		return $out;
	}

	/**
	 * Sources filtrables disponibles : [ key => [ 'type', 'label' ] ].
	 * Ordre : catégories, étiquettes, puis attributs.
	 */
	public static function get_available_sources() {
		$sources = array(
			'product_cat' => array(
				'type'  => 'taxonomy',
				'label' => __( 'Catégories de produits', 'mrz-product-filter-exp' ),
			),
			'product_tag' => array(
				'type'  => 'taxonomy',
				'label' => __( 'Étiquettes de produits', 'mrz-product-filter-exp' ),
			),
		);
		foreach ( self::get_attribute_taxonomies() as $taxonomy => $label ) {
			$sources[ $taxonomy ] = array(
				'type'  => 'attribute',
				'label' => $label,
			);
		}
		return $sources;
	}

	/**
	 * Nom du paramètre d'URL utilisé pour filtrer une source.
	 *
	 * Les attributs réutilisent le paramètre natif WooCommerce filter_{slug} ;
	 * catégories et étiquettes utilisent un paramètre préfixé propre au plugin.
	 */
	public static function param_for_source( $key ) {
		if ( 'product_cat' === $key ) {
			return 'mpf_product_cat';
		}
		if ( 'product_tag' === $key ) {
			return 'mpf_product_tag';
		}
		if ( function_exists( 'wc_attribute_taxonomy_slug' ) ) {
			return 'filter_' . wc_attribute_taxonomy_slug( $key );
		}
		return 'filter_' . $key;
	}

	/**
	 * Fourchette de prix (min/max) de tous les produits, arrondie.
	 */
	public static function get_price_range() {
		global $wpdb;

		$cached = get_transient( self::CACHE_PREFIX . 'price_range' );
		if ( is_array( $cached ) && isset( $cached['min'], $cached['max'] ) ) {
			return $cached;
		}

		$lookup = $wpdb->prefix . 'wc_product_meta_lookup';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- table de lookup WooCommerce, nom de table non paramétrable, résultat mis en cache via transient.
		$row = $wpdb->get_row( "SELECT MIN(min_price) AS min_price, MAX(max_price) AS max_price FROM {$lookup} WHERE min_price IS NOT NULL" );

		$min = ( $row && null !== $row->min_price ) ? (float) $row->min_price : 0;
		$max = ( $row && null !== $row->max_price ) ? (float) $row->max_price : 0;

		$range = array(
			'min' => (int) floor( $min ),
			'max' => (int) ceil( $max ),
		);
		set_transient( self::CACHE_PREFIX . 'price_range', $range, HOUR_IN_SECONDS );

		return $range;
	}

	/**
	 * Construit la configuration complète de rendu d'un jeu de filtres.
	 * Retourne null si l'id est invalide.
	 */
	public static function get_config( $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id <= 0 || get_post_type( $post_id ) !== MRZ_PRODUCT_FILTER_EXP_CPT ) {
			return null;
		}

		$values          = FilterConfig::get_values( $post_id );
		$available        = self::get_available_sources();
		$color_attribute = (string) $values['color_attribute'];
		$default_open    = ! empty( $values['default_open'] );

		$sections = array();
		foreach ( (array) $values['sources'] as $source ) {
			$key = isset( $source['key'] ) ? $source['key'] : '';
			if ( '' === $key || ! isset( $available[ $key ] ) || ! taxonomy_exists( $key ) ) {
				continue;
			}

			$mode = isset( $source['mode'] ) ? $source['mode'] : 'checkbox';
			if ( '' !== $color_attribute && $key === $color_attribute ) {
				$mode = 'color';
			}

			$label = ! empty( $source['label'] ) ? $source['label'] : $available[ $key ]['label'];
			$open  = isset( $source['open'] ) ? ! empty( $source['open'] ) : $default_open;

			$term_args = array(
				'taxonomy'   => $key,
				'hide_empty' => true,
			);
			// Sur une archive de catégorie, ne proposer que les sous-catégories
			// de la catégorie courante (ex. dans « Déco », seulement les
			// sous-catégories de Déco).
			if ( 'product_cat' === $key && function_exists( 'is_product_category' ) && is_product_category() ) {
				$current = get_queried_object();
				if ( $current && isset( $current->term_id ) ) {
					$term_args['parent'] = (int) $current->term_id;
				}
			}

			$terms = get_terms( $term_args );
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			$term_rows = array();
			foreach ( $terms as $term ) {
				$row = array(
					'slug'  => $term->slug,
					'name'  => $term->name,
					'count' => (int) $term->count,
				);
				if ( 'color' === $mode ) {
					$row['color'] = (string) get_term_meta( $term->term_id, '_mrz_product_filter_exp_color', true );
				}
				$term_rows[] = $row;
			}

			$sections[] = array(
				'key'      => $key,
				'taxonomy' => $key,
				'param'    => self::param_for_source( $key ),
				'label'    => $label,
				'mode'     => $mode,
				'logic'    => isset( $source['logic'] ) ? $source['logic'] : 'or',
				'open'     => $open,
				'terms'    => $term_rows,
			);
		}

		$price = null;
		if ( ! empty( $values['enable_price'] ) ) {
			$range = self::get_price_range();
			$price = array(
				'label' => ! empty( $values['price_label'] ) ? $values['price_label'] : __( 'Prix', 'mrz-product-filter-exp' ),
				'open'  => ! empty( $values['price_open'] ),
				'min'   => $range['min'],
				'max'   => $range['max'],
			);
		}

		return array(
			'id'           => $post_id,
			'position'     => in_array( $values['position'], FilterConfig::positions(), true ) ? $values['position'] : 'left',
			'default_open' => $default_open,
			'show_counts'  => ! empty( $values['show_counts'] ),
			'show_reset'   => ! empty( $values['show_reset'] ),
			'reset_text'   => ! empty( $values['reset_text'] ) ? $values['reset_text'] : __( 'Réinitialiser', 'mrz-product-filter-exp' ),
			'sections'     => $sections,
			'price'        => $price,
		);
	}
}
