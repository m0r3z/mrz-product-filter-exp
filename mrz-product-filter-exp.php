<?php
/**
 * Plugin Name:       MRZ Product Filter Exp
 * Plugin URI:        https://github.com/m0r3z/mrz-product-filter-exp
 * Description:       WooCommerce product filters (categories, attributes, tags, price, color swatches) shown in a column or above the shop, with AJAX updates. Minimalist layout.
 * Version:           1.5.2
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 8.0
 * WC tested up to:      11.2
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

define( 'MRZPFE_VERSION', '1.5.2' );
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

// Le plugin ne lit ni n'écrit aucune commande : compatible HPOS (tables de
// commandes personnalisées) sans adaptation.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
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
