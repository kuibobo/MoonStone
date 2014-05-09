<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_modify_admin_menu_highlight() {
	global $plugin_page, $submenu_file;
	
	// This tweaks the Settings subnav menu to show only one BuddyPress menu item
	if ( ! in_array( $plugin_page, array( 'cp-category', 'cp-general-settings', ) ) )
		$submenu_file = 'cp-components';
}

function cp_core_admin_backpat_page() {
	$url          = cp_core_do_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' );
	$settings_url = add_query_arg( 'page', 'cp-components', $url ); ?>

	<div class="wrap">
		<?php screen_icon( 'cateogrypress' ); ?>
		<h2><?php _e( 'Why have all my CategoryPress menus disappeared?', 'cateogrypress' ); ?></h2>

	</div>

	<?php
}

function cp_core_admin_backpat_menu() {
	global $_parent_pages, $_registered_pages, $submenu;

	// If there's no bp-general-settings menu (perhaps because the current
	// user is not an Administrator), there's nothing to do here
	if ( ! isset( $submenu['cp-general-settings'] ) ) {
		return;
	}

	/**
	 * By default, only the core "Help" submenu is added under the top-level BuddyPress menu.
	 * This means that if no third-party plugins have registered their admin pages into the
	 * 'bp-general-settings' menu, it will only contain one item. Kill it.
	 */
	if ( 1 != count( $submenu['cp-general-settings'] ) ) {
		return;
	}

	// This removes the top-level menu
	remove_submenu_page( 'cp-general-settings', 'cp-general-settings' );
	remove_menu_page( 'cp-general-settings' );

	// These stop people accessing the URL directly
	unset( $_parent_pages['cp-general-settings'] );
	unset( $_registered_pages['toplevel_page_cp-general-settings'] );
}
//add_action( cp_core_admin_hook(), 'cp_core_admin_backpat_menu', 999 );