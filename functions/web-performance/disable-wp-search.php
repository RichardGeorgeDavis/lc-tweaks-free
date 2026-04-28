<?php
/**
 * @package Disable WordPress Search Feature
 * @version 1.1
 */

function fb_filter_query( $query, $error = true ) {
	
	if ( is_search() && ! is_admin() ) {
		$query->is_search = false;
		$query->query_vars['s'] = false;
		$query->query['s'] = false;
		
		// to error
		if ( $error == true )
			$query->is_404 = true;
	}
}

add_action( 'parse_query', 'fb_filter_query' );
add_filter( 'get_search_form', function($a) {return null;});

?>
