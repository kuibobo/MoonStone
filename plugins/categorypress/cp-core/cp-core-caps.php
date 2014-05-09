<?php

if ( !defined( 'ABSPATH' ) ) exit;

function cp_current_user_can( $capability, $blog_id = 0 ) {
	
	// Use root blog if no ID passed
	if ( empty( $blog_id ) )
		$blog_id = cp_get_root_blog_id();
	
	$retval = current_user_can_for_blog( $blog_id, $capability );
	
	return (bool) apply_filters( 'cp_current_user_can', $retval, $capability, $blog_id );
}