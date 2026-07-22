<?php
/**
 * Métaboxes de configuration d'un jeu de filtres + sauvegarde sécurisée.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FilterConfig {

	const NONCE_ACTION = 'mrzpfe_save';
	const NONCE_NAME   = '_mrzpfe_nonce';

	/**
	 * Valeurs autorisées pour les champs à choix fermé.
	 */
	public static function modes() {
		return array( 'checkbox', 'radio', 'dropdown' );
	}
	public static function logics() {
		return array( 'or', 'and' );
	}
	public static function positions() {
		return array( 'left', 'top' );
	}
	public static function auto_targets_allowed() {
		return array( 'shop', 'product_cat', 'product_tag' );
	}

	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'save_post_' . MRZPFE_CPT, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'wp_ajax_mrzpfe_fetch_terms', array( $this, 'ajax_fetch_terms' ) );
	}

	public function add_metaboxes() {
		add_meta_box( 'mrzpfe_source', __( 'Sources des filtres', 'mrz-product-filter-exp' ), array( $this, 'render' ), MRZPFE_CPT, 'normal', 'high', array( 'view' => 'source' ) );
		add_meta_box( 'mrzpfe_colors', __( 'Pastilles de couleur', 'mrz-product-filter-exp' ), array( $this, 'render' ), MRZPFE_CPT, 'normal', 'default', array( 'view' => 'colors' ) );
		add_meta_box( 'mrzpfe_display', __( 'Affichage', 'mrz-product-filter-exp' ), array( $this, 'render' ), MRZPFE_CPT, 'normal', 'default', array( 'view' => 'display' ) );
		add_meta_box( 'mrzpfe_shortcode', __( 'Shortcode', 'mrz-product-filter-exp' ), array( $this, 'render' ), MRZPFE_CPT, 'side', 'high', array( 'view' => 'shortcode' ) );
	}

	public function render( $post, $metabox ) {
		static $nonce_printed = false;
		if ( ! $nonce_printed ) {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
			$nonce_printed = true;
		}

		$view = isset( $metabox['args']['view'] ) ? $metabox['args']['view'] : '';
		$file = MRZPFE_DIR . 'admin/views/metabox-' . $view . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		$values = self::get_values( $post->ID );
		include $file;
	}

	/**
	 * Charge la configuration d'un jeu de filtres, avec defaults.
	 */
	public static function get_values( $post_id ) {
		$defaults = array(
			'sources'         => array(),
			'color_attribute' => '',
			'enable_price'    => 1,
			'price_label'     => '',
			'price_open'      => 1,
			'position'        => 'left',
			'sticky'          => 0,
			'sticky_offset'   => 20,
			'auto_display'    => 0,
			'auto_targets'    => array( 'shop', 'product_cat' ),
			'default_open'    => 1,
			'show_counts'     => 1,
			'show_reset'      => 1,
			'reset_text'      => '',
			'override_loop'   => 0,
			'per_page'        => 12,
			'cols_wide'       => 5,
			'cols_desktop'    => 4,
			'cols_tablet'     => 3,
			'cols_mobile'     => 2,
		);

		$out = array();
		foreach ( $defaults as $key => $default ) {
			$stored = get_post_meta( $post_id, '_mrzpfe_' . $key, true );
			if ( '' === $stored || null === $stored ) {
				$out[ $key ] = $default;
			} elseif ( is_array( $default ) ) {
				$out[ $key ] = is_array( $stored ) ? $stored : $default;
			} elseif ( is_int( $default ) ) {
				$out[ $key ] = (int) $stored;
			} else {
				$out[ $key ] = $stored;
			}
		}

		return $out;
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = isset( $_POST['mrzpfe'] ) && is_array( $_POST['mrzpfe'] )
			? wp_unslash( $_POST['mrzpfe'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- chaque champ est assaini individuellement ci-dessous.
			: array();

		$clean = array();

		// Clés de sources autorisées (product_cat, product_tag, attributs pa_*).
		$allowed_keys = array_keys( DataProvider::get_available_sources() );
		$attr_keys    = array_keys( DataProvider::get_attribute_taxonomies() );

		// Répéteur de sources (ordre conservé, clés dédupliquées).
		$sources = array();
		$seen    = array();
		if ( isset( $raw['sources'] ) && is_array( $raw['sources'] ) ) {
			foreach ( $raw['sources'] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$key = isset( $row['key'] ) ? sanitize_key( $row['key'] ) : '';
				if ( '' === $key || ! in_array( $key, $allowed_keys, true ) || isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;

				$mode  = ( isset( $row['mode'] ) && in_array( $row['mode'], self::modes(), true ) ) ? $row['mode'] : 'checkbox';
				$logic = ( isset( $row['logic'] ) && in_array( $row['logic'], self::logics(), true ) ) ? $row['logic'] : 'or';
				$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
				$open  = ! empty( $row['open'] ) ? 1 : 0;

				$sources[] = array(
					'key'   => $key,
					'mode'  => $mode,
					'logic' => $logic,
					'label' => $label,
					'open'  => $open,
				);
			}
		}
		$clean['sources'] = $sources;

		// Attribut couleur (parmi les attributs pa_* uniquement).
		$clean['color_attribute'] = ( isset( $raw['color_attribute'] ) && in_array( $raw['color_attribute'], $attr_keys, true ) )
			? $raw['color_attribute']
			: '';

		// Prix.
		$clean['enable_price'] = ! empty( $raw['enable_price'] ) ? 1 : 0;
		$clean['price_label']  = isset( $raw['price_label'] ) ? sanitize_text_field( (string) $raw['price_label'] ) : '';
		$clean['price_open']   = ! empty( $raw['price_open'] ) ? 1 : 0;

		// Affichage.
		$clean['position']      = ( isset( $raw['position'] ) && in_array( $raw['position'], self::positions(), true ) ) ? $raw['position'] : 'left';
		$clean['sticky']        = ! empty( $raw['sticky'] ) ? 1 : 0;
		$clean['sticky_offset'] = isset( $raw['sticky_offset'] ) ? min( 400, absint( $raw['sticky_offset'] ) ) : 20;
		$clean['auto_display']  = ! empty( $raw['auto_display'] ) ? 1 : 0;

		$targets = array();
		if ( isset( $raw['auto_targets'] ) && is_array( $raw['auto_targets'] ) ) {
			foreach ( $raw['auto_targets'] as $target ) {
				$target = sanitize_key( $target );
				if ( in_array( $target, self::auto_targets_allowed(), true ) ) {
					$targets[] = $target;
				}
			}
		}
		$clean['auto_targets'] = array_values( array_unique( $targets ) );

		$clean['default_open'] = ! empty( $raw['default_open'] ) ? 1 : 0;
		$clean['show_counts']  = ! empty( $raw['show_counts'] ) ? 1 : 0;
		$clean['show_reset']   = ! empty( $raw['show_reset'] ) ? 1 : 0;
		$clean['reset_text']   = isset( $raw['reset_text'] ) ? sanitize_text_field( (string) $raw['reset_text'] ) : '';

		// Contrôle de la boucle produits (par page + colonnes responsives).
		$clean['override_loop'] = ! empty( $raw['override_loop'] ) ? 1 : 0;
		$clean['per_page']      = isset( $raw['per_page'] ) ? min( 100, absint( $raw['per_page'] ) ) : 12;
		$clean['cols_wide']     = isset( $raw['cols_wide'] ) ? max( 1, min( 8, absint( $raw['cols_wide'] ) ) ) : 5;
		$clean['cols_desktop']  = isset( $raw['cols_desktop'] ) ? max( 1, min( 8, absint( $raw['cols_desktop'] ) ) ) : 4;
		$clean['cols_tablet']   = isset( $raw['cols_tablet'] ) ? max( 1, min( 8, absint( $raw['cols_tablet'] ) ) ) : 3;
		$clean['cols_mobile']   = isset( $raw['cols_mobile'] ) ? max( 1, min( 8, absint( $raw['cols_mobile'] ) ) ) : 2;

		foreach ( $clean as $key => $value ) {
			update_post_meta( $post_id, '_mrzpfe_' . $key, $value );
		}

		// Couleurs par terme — stockées globalement en term_meta.
		if ( isset( $raw['term_colors'] ) && is_array( $raw['term_colors'] ) ) {
			foreach ( $raw['term_colors'] as $term_id => $color ) {
				$term_id = absint( $term_id );
				if ( ! $term_id || ! term_exists( $term_id ) ) {
					continue;
				}
				$hex = mrzpfe_sanitize_hex_color( (string) $color );
				if ( '' === $hex ) {
					delete_term_meta( $term_id, '_mrzpfe_color' );
				} else {
					update_term_meta( $term_id, '_mrzpfe_color', $hex );
				}
			}
		}

		// Invalide le cache transient de ce jeu de filtres.
		delete_transient( 'mrzpfe_' . (int) $post_id );
	}

	/**
	 * Handler AJAX (admin) : récupère les termes d'un attribut + leur couleur.
	 */
	public function ajax_fetch_terms() {
		check_ajax_referer( 'mrzpfe_fetch_terms', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'mrz-product-filter-exp' ) ), 403 );
		}

		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			wp_send_json_error( array( 'message' => __( 'Attribut invalide.', 'mrz-product-filter-exp' ) ), 400 );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			wp_send_json_error( array( 'message' => $terms->get_error_message() ), 500 );
		}

		$out = array();
		foreach ( $terms as $term ) {
			$out[] = array(
				'id'    => (int) $term->term_id,
				'name'  => $term->name,
				'color' => (string) get_term_meta( $term->term_id, '_mrzpfe_color', true ),
			);
		}
		wp_send_json_success( $out );
	}

	/**
	 * Enqueue admin.js / admin.css uniquement sur l'écran d'édition du CPT.
	 */
	public function enqueue_admin( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || MRZPFE_CPT !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'mrz-product-filter-exp-admin',
			MRZPFE_URL . 'admin/css/admin.css',
			array(),
			MRZPFE_VERSION
		);

		wp_enqueue_script(
			'mrz-product-filter-exp-admin',
			MRZPFE_URL . 'admin/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
			MRZPFE_VERSION,
			true
		);
	}
}
