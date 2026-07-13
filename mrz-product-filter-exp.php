<?php
/**
 * Plugin Name:       MRZ Product Filter
 * Plugin URI:        https://github.com/m0r3z/mrz-product-filter-exp
 * Description:       Filtres de produits WooCommerce (catégories, attributs, étiquettes, prix, pastilles de couleur) affichés en colonne ou au-dessus de la boutique, avec mise à jour AJAX. Mise en page minimaliste.
 * Version:           1.2.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Morez.co
 * Author URI:        https://morez.co
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       mrz-product-filter-exp
 * Domain Path:       /languages
 *
 * MRZ Product Filter — Copyright (C) 2026 Morez.co <hello@morez.co>
 * "MRZ Product Filter" is a trademark of Morez.co. See LICENSE for full terms.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 as published by
 * the Free Software Foundation. See LICENSE for the full license text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRZ_PRODUCT_FILTER_EXP_VERSION', '1.2.0' );
define( 'MRZ_PRODUCT_FILTER_EXP_FILE', __FILE__ );
define( 'MRZ_PRODUCT_FILTER_EXP_DIR', plugin_dir_path( __FILE__ ) );
define( 'MRZ_PRODUCT_FILTER_EXP_URL', plugin_dir_url( __FILE__ ) );
define( 'MRZ_PRODUCT_FILTER_EXP_BASENAME', plugin_basename( __FILE__ ) );
// Le nom d'un post type est limité à 20 caractères par WordPress, d'où le slug court.
define( 'MRZ_PRODUCT_FILTER_EXP_CPT', 'mrz_pfe_filter' );

require_once MRZ_PRODUCT_FILTER_EXP_DIR . 'includes/helpers.php';

spl_autoload_register(
	static function ( $class ) {
		if ( strpos( $class, 'MrzProductFilterExp\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( 'MrzProductFilterExp\\' ) );
		$relative = str_replace( '\\', '/', $relative );
		$parts    = explode( '/', $relative );
		$last     = array_pop( $parts );
		// Insère un tiret avant une majuscule qui suit une minuscule/chiffre,
		// ou avant une majuscule suivie d'une minuscule (fin d'acronyme).
		$last     = preg_replace( '/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '-$0', $last );
		$last     = strtolower( $last );
		$prefix   = empty( $parts ) ? '' : strtolower( implode( '/', $parts ) ) . '/';
		$path     = MRZ_PRODUCT_FILTER_EXP_DIR . 'includes/' . $prefix . 'class-' . $last . '.php';

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( 'MrzProductFilterExp\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MrzProductFilterExp\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		MrzProductFilterExp\Plugin::instance()->boot();
	}
);
