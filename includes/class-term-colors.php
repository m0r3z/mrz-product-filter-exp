<?php
/**
 * Permet d'attacher une couleur à chaque terme d'attribut de produit (pa_*),
 * pour l'affichage en pastilles. Stockage en term_meta globale.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TermColors {

	const NONCE_ACTION = 'mrzpfe_save_term';
	const NONCE_NAME   = '_mrzpfe_term_nonce';
	const META_KEY     = '_mrzpfe_color';

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
			<label for="mrzpfe_color"><?php esc_html_e( 'Filter color', 'mrz-product-filter-exp' ); ?></label>
			<input type="text" name="mrzpfe_color" id="mrzpfe_color" value="" class="mrz-product-filter-exp-color-picker" />
			<p><?php esc_html_e( 'Color shown as a swatch in the color filter.', 'mrz-product-filter-exp' ); ?></p>
		</div>
		<?php
	}

	public function edit_form_fields( $term, $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$color = (string) get_term_meta( $term->term_id, self::META_KEY, true );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="mrzpfe_color"><?php esc_html_e( 'Filter color', 'mrz-product-filter-exp' ); ?></label>
			</th>
			<td>
				<input type="text" name="mrzpfe_color" id="mrzpfe_color" value="<?php echo esc_attr( $color ); ?>" class="mrz-product-filter-exp-color-picker" />
				<p class="description"><?php esc_html_e( 'Color shown as a swatch in the color filter.', 'mrz-product-filter-exp' ); ?></p>
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

		$color = isset( $_POST['mrzpfe_color'] )
			? mrzpfe_sanitize_hex_color( wp_unslash( $_POST['mrzpfe_color'] ) )
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
