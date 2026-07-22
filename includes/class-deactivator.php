<?php
/**
 * Actions exécutées à la désactivation du plugin.
 */

namespace Mrzpfe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
