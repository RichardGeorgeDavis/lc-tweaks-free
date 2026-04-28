<?php
/**
 * Hide the Divi Projects post type and portfolio modules.
 */

function dlck_hide_project_post_type( $args ) {
	return array_merge(
		$args,
		array(
			'public'              => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'show_in_nav_menus'   => false,
			'show_ui'             => false,
		)
	);
}
add_filter( 'et_project_posttype_args', 'dlck_hide_project_post_type', 10, 1 );

function dlck_filter_portfolio_modules( $modules, $post_type ) {
	foreach ( $modules as $key => $module ) {
		if ( strpos( $key, 'portfolio' ) !== false && strpos( $key, 'dp' ) === false ) {
			unset( $modules[ $key ] );
		}
	}
	return $modules;
}
add_filter( 'et_builder_get_parent_modules', 'dlck_filter_portfolio_modules', 50, 2 );
add_filter( 'et_builder_get_child_modules', 'dlck_filter_portfolio_modules', 50, 2 );
