<?php
/**
 * Nettoyage à la désinstallation du plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Requêtes directes voulues ici : le CPT n'est plus enregistré à la
// désinstallation, et aucune API ne supprime des métas ou des transients
// par préfixe. Toutes les valeurs passent par prepare().
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

// Supprime tous les posts du CPT et leurs métas associées.
$mrzpfe_cpt      = 'mrzpfe_filter';
$mrzpfe_post_ids = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
		$mrzpfe_cpt
	)
);

if ( ! empty( $mrzpfe_post_ids ) ) {
	foreach ( $mrzpfe_post_ids as $mrzpfe_post_id ) {
		wp_delete_post( (int) $mrzpfe_post_id, true );
	}
}

// Supprime les post_meta orphelines éventuelles (_mrzpfe_*).
$mrzpfe_meta_like = $wpdb->esc_like( '_mrzpfe_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		$mrzpfe_meta_like
	)
);

// Supprime les term_meta de couleur.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->termmeta} WHERE meta_key = %s",
		'_mrzpfe_color'
	)
);

// Supprime les transients de cache.
$mrzpfe_transient_like         = $wpdb->esc_like( '_transient_mrzpfe_' ) . '%';
$mrzpfe_transient_timeout_like = $wpdb->esc_like( '_transient_timeout_mrzpfe_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$mrzpfe_transient_like,
		$mrzpfe_transient_timeout_like
	)
);

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
