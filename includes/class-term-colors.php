<?php
/**
 * Permet d'attacher une couleur à chaque terme d'attribut de produit (pa_*),
 * pour l'affichage en pastilles. Stockage en term_meta globale.
 */

namespace MrzProductFilterExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TermColors {

	const NONCE_ACTION = 'mrz_product_filter_exp_save_term';
	const NONCE_NAME   = '_mrz_product_filter_exp_term_nonce';
	const META_KEY     = '_mrz_product_filter_exp_color';

	public function register() {
		// Les attributs sont enregistrés par WooCommerce sur init : on hooke après.
		add_action( 'init', array( $this, 'register_taxonomy_hooks' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function register_taxonomy_hooks() {
		foreach ( array_keys( DataProvider::get_attribute_taxonomies() ) as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_form_fields' ), 10, 1 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_form_fields' ), 10, 2 );
			add_action( "created_{$taxonomy}", array( $this, 'save' ), 10, 2 );
			add_action( "edited_{$taxonomy}", array( $this, 'save' ), 10, 2 );
		}
	}

	public function add_form_fields( $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<div class="form-field">
			<label for="mrz_product_filter_exp_color"><?php esc_html_e( 'Couleur du filtre', 'mrz-product-filter-exp' ); ?></label>
			<input type="text" name="mrz_product_filter_exp_color" id="mrz_product_filter_exp_color" value="" class="mrz-product-filter-exp-color-picker" />
			<p><?php esc_html_e( 'Couleur affichée en pastille dans le filtre par couleur.', 'mrz-product-filter-exp' ); ?></p>
		</div>
		<?php
	}

	public function edit_form_fields( $term, $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$color = (string) get_term_meta( $term->term_id, self::META_KEY, true );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="mrz_product_filter_exp_color"><?php esc_html_e( 'Couleur du filtre', 'mrz-product-filter-exp' ); ?></label>
			</th>
			<td>
				<input type="text" name="mrz_product_filter_exp_color" id="mrz_product_filter_exp_color" value="<?php echo esc_attr( $color ); ?>" class="mrz-product-filter-exp-color-picker" />
				<p class="description"><?php esc_html_e( 'Couleur affichée en pastille dans le filtre par couleur.', 'mrz-product-filter-exp' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public function save( $term_id, $tt_id = 0 ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$color = isset( $_POST['mrz_product_filter_exp_color'] )
			? mrz_product_filter_exp_sanitize_hex_color( wp_unslash( $_POST['mrz_product_filter_exp_color'] ) )
			: '';

		if ( '' === $color ) {
			delete_term_meta( $term_id, self::META_KEY );
		} else {
			update_term_meta( $term_id, self::META_KEY, $color );
		}
	}

	public function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 0 !== strpos( (string) $screen->taxonomy, 'pa_' ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(function($){$(".mrz-product-filter-exp-color-picker").wpColorPicker();});'
		);
	}
}
