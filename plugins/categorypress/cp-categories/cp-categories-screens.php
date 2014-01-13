<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_categories_screen_index() {
	if ( cp_is_categories_component() ) {
		cp_update_is_directory( true, 'categories' );
		
		do_action( 'cp_categories_screen_index' );
		
		cp_core_load_template( apply_filters( 'cp_categories_screen_index', 'categories/index' ) );
	}
}
add_action( 'cp_screens', 'cp_categories_screen_index' );