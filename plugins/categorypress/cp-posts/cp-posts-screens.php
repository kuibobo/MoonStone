<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_posts_screen_category() {
	if ( cp_is_posts_component() ) {
		cp_update_is_directory( true, 'posts' );
		
		do_action( 'cp_posts_screen_index' );
		
		cp_core_load_template( apply_filters( 'cp_posts_screen_index', 'posts/index' ) );
	}
}

function cp_posts_screen_single() {
	if ( cp_is_posts_component() && cp_is_single_item() ) {
		
		do_action( 'cp_posts_screen_single' );
		
		cp_core_load_template( apply_filters( 'cp_posts_screen_single', 'posts/single/home' ) );
	}
}