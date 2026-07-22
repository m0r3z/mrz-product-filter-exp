<?php
/**
 * Nettoyage à la désinstallation du plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Supprime tous les posts du CPT et leurs métas associées.
$cpt      = 'mrzpfe_filter';
$post_ids = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
		$cpt
	)
);

if ( ! empty( $post_ids ) ) {
	foreach ( $post_ids as $post_id ) {
		wp_delete_post( (int) $post_id, true );
	}
}

// Supprime les post_meta orphelines éventuelles (_mrzpfe_*).
$meta_like = $wpdb->esc_like( '_mrzpfe_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		$meta_like
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
$transient_like         = $wpdb->esc_like( '_transient_mrzpfe_' ) . '%';
$transient_timeout_like = $wpdb->esc_like( '_transient_timeout_mrzpfe_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$transient_like,
		$transient_timeout_like
	)
);
