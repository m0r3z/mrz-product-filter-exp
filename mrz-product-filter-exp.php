<?php
/**
 * Plugin Name:       MRZ Product Filter Exp
 * Plugin URI:        https://github.com/m0r3z/mrz-product-filter-exp
 * Description:       Filtres de produits WooCommerce (catégories, attributs, étiquettes, prix, pastilles de couleur) affichés en colonne ou au-dessus de la boutique, avec mise à jour AJAX. Mise en page minimaliste.
 * Version:           1.4.1
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

define( 'MRZPFE_VERSION', '1.4.1' );
define( 'MRZPFE_FILE', __FILE__ );
define( 'MRZPFE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MRZPFE_URL', plugin_dir_url( __FILE__ ) );
define( 'MRZPFE_BASENAME', plugin_basename( __FILE__ ) );
// Le nom d'un post type est limité à 20 caractères par WordPress, d'où le slug court.
define( 'MRZPFE_CPT', 'mrzpfe_filter' );

require_once MRZPFE_DIR . 'includes/helpers.php';

spl_autoload_register(
	static function ( $class ) {
		if ( strpos( $class, 'Mrzpfe\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( 'Mrzpfe\\' ) );
		$relative = str_replace( '\\', '/', $relative );
		$parts    = explode( '/', $relative );
		$last     = array_pop( $parts );
		// Insère un tiret avant une majuscule qui suit une minuscule/chiffre,
		// ou avant une majuscule suivie d'une minuscule (fin d'acronyme).
		$last     = preg_replace( '/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '-$0', $last );
		$last     = strtolower( $last );
		$prefix   = empty( $parts ) ? '' : strtolower( implode( '/', $parts ) ) . '/';
		$path     = MRZPFE_DIR . 'includes/' . $prefix . 'class-' . $last . '.php';

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( 'Mrzpfe\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Mrzpfe\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		Mrzpfe\Plugin::instance()->boot();
	}
);
