<?php
/**
 * Fournit les données de filtres (sources, termes, couleurs, prix) et
 * construit la configuration consommée par le rendu.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DataProvider {

	const CACHE_PREFIX = 'mrzpfe_';

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
				'label' => __( 'Product categories', 'mrz-product-filter-exp' ),
			),
			'product_tag' => array(
				'type'  => 'taxonomy',
				'label' => __( 'Product tags', 'mrz-product-filter-exp' ),
			),
		);

		// Marques WooCommerce (taxonomie native product_brand, si présente).
		if ( taxonomy_exists( 'product_brand' ) ) {
			$sources['product_brand'] = array(
				'type'  => 'taxonomy',
				'label' => __( 'Brands', 'mrz-product-filter-exp' ),
			);
		}

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
		if ( 'product_brand' === $key ) {
			return 'mpf_product_brand';
		}
		if ( function_exists( 'wc_attribute_taxonomy_slug' ) ) {
			return 'filter_' . wc_attribute_taxonomy_slug( $key );
		}
		return 'filter_' . $key;
	}

	/**
	 * Retrouve le premier jeu de filtres publié dont la méta donnée vaut 1 et
	 * dont les cibles d'auto-affichage incluent le contexte courant.
	 *
	 * @param string $target   'shop' | 'product_cat' | 'product_tag'.
	 * @param string $meta_key Clé méta complète (ex. _mrzpfe_auto_display).
	 * @return int ID du jeu de filtres, ou 0.
	 */
	public static function find_set_for_target( $target, $meta_key ) {
		$ids = get_posts(
			array(
				'post_type'      => MRZPFE_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		foreach ( $ids as $id ) {
			$values = FilterConfig::get_values( $id );
			if ( in_array( $target, (array) $values['auto_targets'], true ) ) {
				return (int) $id;
			}
		}
		return 0;
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
		if ( $post_id <= 0 || get_post_type( $post_id ) !== MRZPFE_CPT ) {
			return null;
		}

		$values          = FilterConfig::get_values( $post_id );
		$available        = self::get_available_sources();
		$color_attribute = (string) $values['color_attribute'];
		$default_open    = ! empty( $values['default_open'] );

		// Sur une archive (catégorie / marque / étiquette / attribut), on
		// restreint tous les filtres aux produits réellement présents dans ce
		// contexte : évite de proposer un terme qui donnerait zéro résultat.
		$context_ids = self::get_context_product_ids();

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

			// Sur une archive de catégorie, ne proposer que les sous-catégories
			// de la catégorie courante (ex. dans « Déco », seulement les
			// sous-catégories de Déco).
			$parent = null;
			if ( 'product_cat' === $key && function_exists( 'is_product_category' ) && is_product_category() ) {
				$current = get_queried_object();
				if ( $current && isset( $current->term_id ) ) {
					$parent = (int) $current->term_id;
				}
			}

			if ( is_array( $context_ids ) ) {
				// Archive : termes présents parmi les produits du contexte,
				// avec le compte réel du contexte.
				$terms = self::get_context_terms( $key, $context_ids, $parent );
			} else {
				// Boutique / hors archive : tous les termes non vides.
				$term_args = array(
					'taxonomy'   => $key,
					'hide_empty' => true,
				);
				if ( null !== $parent ) {
					$term_args['parent'] = $parent;
				}
				$terms = get_terms( $term_args );
				if ( is_wp_error( $terms ) ) {
					$terms = array();
				}
			}

			if ( empty( $terms ) ) {
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
					$row['color'] = (string) get_term_meta( $term->term_id, '_mrzpfe_color', true );
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
				'label' => ! empty( $values['price_label'] ) ? $values['price_label'] : __( 'Price', 'mrz-product-filter-exp' ),
				'open'  => ! empty( $values['price_open'] ),
				'min'   => $range['min'],
				'max'   => $range['max'],
			);
		}

		$position = in_array( $values['position'], FilterConfig::positions(), true ) ? $values['position'] : 'left';

		return array(
			'id'            => $post_id,
			'position'      => $position,
			'sticky'        => ( ! empty( $values['sticky'] ) && 'left' === $position ),
			'sticky_offset' => (int) $values['sticky_offset'],
			'default_open'  => $default_open,
			'show_counts'  => ! empty( $values['show_counts'] ),
			'show_reset'   => ! empty( $values['show_reset'] ),
			'reset_text'   => ! empty( $values['reset_text'] ) ? $values['reset_text'] : __( 'Reset filters', 'mrz-product-filter-exp' ),
			'sections'     => $sections,
			'price'        => $price,
		);
	}

	/**
	 * IDs des produits du contexte d'archive courant (catégorie / marque /
	 * étiquette / attribut). Retourne null hors archive (boutique) = aucune
	 * restriction, ou un tableau (éventuellement vide) sur une archive.
	 */
	private static function get_context_product_ids() {
		if ( is_admin() || ! function_exists( 'is_product_taxonomy' ) || ! is_product_taxonomy() ) {
			return null;
		}

		$obj = get_queried_object();
		if ( ! $obj || empty( $obj->taxonomy ) || empty( $obj->term_id ) ) {
			return null;
		}

		$tax_query = array(
			array(
				'taxonomy' => $obj->taxonomy,
				'field'    => 'term_id',
				'terms'    => array( (int) $obj->term_id ),
			),
		);

		if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
			$visibility = wc_get_product_visibility_term_ids();
			if ( ! empty( $visibility['exclude-from-catalog'] ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $visibility['exclude-from-catalog'] ),
					'operator' => 'NOT IN',
				);
			}
			if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! empty( $visibility['outofstock'] ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $visibility['outofstock'] ),
					'operator' => 'NOT IN',
				);
			}
		}

		$query = new \WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'posts_per_page'      => -1,
				'fields'              => 'ids',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
				'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);

		return array_map( 'intval', $query->posts );
	}

	/**
	 * Termes d'une taxonomie présents parmi un jeu d'IDs de produits, avec le
	 * nombre de produits du contexte. Optionnellement limité à un parent (pour
	 * les sous-catégories). Retourne des objets {term_id, name, slug, count}.
	 */
	private static function get_context_terms( $taxonomy, array $ids, $parent = null ) {
		global $wpdb;

		if ( empty( $ids ) ) {
			return array();
		}
		$ids          = array_map( 'intval', $ids );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$sql = "SELECT t.term_id, t.name, t.slug, COUNT(tr.object_id) AS count
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
			INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.taxonomy = %s AND tr.object_id IN ($placeholders)";

		$params = array_merge( array( $taxonomy ), $ids );
		if ( null !== $parent ) {
			$sql     .= ' AND tt.parent = %d';
			$params[] = (int) $parent;
		}
		$sql .= ' GROUP BY t.term_id, t.name, t.slug ORDER BY t.name ASC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- $sql ne contient que des noms de tables core et des placeholders, toutes les valeurs passent par prepare() ; get_terms() ne sait pas compter les termes sur un sous-ensemble de produits.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		return is_array( $rows ) ? $rows : array();
	}
}
