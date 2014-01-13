<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_posts_screen_index() {
	if ( cp_is_posts_component() ) {
		cp_update_is_directory( true, 'posts' );
		
		do_action( 'cp_posts_screen_index' );
		
		cp_core_load_template( apply_filters( 'cp_posts_screen_index', 'posts/index' ) );
	}
}
add_action( 'cp_screens', 'cp_posts_screen_index' );