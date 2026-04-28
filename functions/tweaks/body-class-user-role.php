<?php

// Add current user role(s) to the body class list.
add_filter(
	'body_class',
	static function ( $classes ) {
		$user  = wp_get_current_user();
		$roles = is_array( $user->roles ) ? array_filter( $user->roles ) : array();

		if ( empty( $roles ) ) {
			return $classes;
		}

		// Ensure valid class names.
		$roles = array_map( 'sanitize_html_class', $roles );

		return array_merge( $classes, $roles );
	}
);
