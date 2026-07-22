<?php
/**
 * Fonctions utilitaires globales.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vérifie si WooCommerce est actif.
 */
function mrzpfe_has_woocommerce() {
	return class_exists( 'WooCommerce' );
}

/**
 * Assainit une couleur hexadécimale (#rgb ou #rrggbb). Retourne '' si invalide.
 *
 * La fonction cœur sanitize_hex_color() n'est pas toujours chargée hors
 * contexte customizer : on fournit notre propre implémentation.
 */
function mrzpfe_sanitize_hex_color( $color ) {
	$color = trim( (string) $color );
	if ( '' === $color ) {
		return '';
	}
	if ( preg_match( '/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color ) ) {
		return strtolower( $color );
	}
	return '';
}
