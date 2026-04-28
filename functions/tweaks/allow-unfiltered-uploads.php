<?php
/**
 * Allow unfiltered uploads for admins without needing wp-config.php.
 */
add_filter(
	'user_has_cap',
	static function ( $allcaps, $caps, $args ) {
		if ( empty( $args[0] ) || 'unfiltered_upload' !== $args[0] ) {
			return $allcaps;
		}

		$user_id = isset( $args[1] ) ? (int) $args[1] : 0;
		if ( $user_id && user_can( $user_id, 'manage_options' ) ) {
			$allcaps['unfiltered_upload'] = true;
		}

		return $allcaps;
	},
	10,
	3
);
