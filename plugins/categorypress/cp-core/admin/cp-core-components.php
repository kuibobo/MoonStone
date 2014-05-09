<?php

/**
 * BuddyPress Admin Component Functions
 *
 * @package BuddyPress
 * @subpackage CoreAdministration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_admin_components_settings_handler() {
}
add_action( 'cp_admin_init', 'cp_core_admin_components_settings_handler' );