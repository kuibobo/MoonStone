<?php


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'admin_init',              'cp_admin_init' );

add_action( 'cp_admin_init', 'cp_setup_updater',          1000 );

/**
 * Piggy back admin_init action
 *
 * @since CategoryPress
 * @uses do_action() Calls 'cp_admin_init'
 */
function cp_admin_init() {
	do_action( 'cp_admin_init' );
}