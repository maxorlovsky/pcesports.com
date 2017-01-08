<?php
add_action( 'rest_api_init', function () {

	// View number of all published posts
	register_rest_route(
		'pce-api',
		'/post-count/',
		array(
			'methods' => 'GET',
			'callback' => function() {
				return wp_count_posts();
			},
		)
	);

	// Fetch menus
	register_rest_route(
		'pce-api',
		'/menu/',
		array(
			'methods'	=> 'GET',
			'callback'	=> function() {
				return wp_get_nav_menu_items(get_query_var('menu', 'main-menu'));
			},
		)
	);

	// Add additional field to posts, to display thumbnail
	register_rest_field( 'post',
        'image',
        array(
            'get_callback'    => function( $object, $field_name, $request ) {
            	return get_the_post_thumbnail( $object['id'], array(790, 370));
            },
            'update_callback' => null,
            'schema'          => null,
        )
    );
});


// Forbid full access to REST API
/*function forbid_rest_api_access ( $access ) {
	if( $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' ) {
		return new WP_Error(
			'rest_cannot_access',
			__( 'Only authenticated users can access the REST API.', 'disable-json-api' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}
	return $access;
}

add_filter( 'rest_authentication_errors', 'forbid_rest_api_access' );*/

// Add additional fields to already existed classes in WP API
/*if ( is_user_logged_in() ) {
	add_action( 'rest_api_init', 'slug_register_starship' );
	function slug_register_starship() {
	    register_rest_field( 'user',
	        'meta',
	        array(
	            'get_callback'    => 'slug_get_starship',
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	function slug_get_starship( $object, $field_name, $request ) {
	    return get_user_meta( $object[ 'id' ] );
	}
}*/