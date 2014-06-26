<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_modify_admin_menu_highlight() {
	global $plugin_page, $submenu_file;
	
	// This tweaks the Settings subnav menu to show only one CategoryPress menu item
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

	// If there's no cp-general-settings menu (perhaps because the current
	// user is not an Administrator), there's nothing to do here
	if ( ! isset( $submenu['cp-general-settings'] ) ) {
		return;
	}

	/**
	 * By default, only the core "Help" submenu is added under the top-level BuddyPress menu.
	 * This means that if no third-party plugins have registered their admin pages into the
	 * 'cp-general-settings' menu, it will only contain one item. Kill it.
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
add_action( cp_core_admin_hook(), 'cp_core_admin_backpat_menu', 999 );

function cp_core_admin_tabs( $active_tab = '' ) {

	// Declare local variables
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';

	// Setup core admin tabs
	$tabs = array(
		'0' => array(
			'href' => cp_get_admin_url( add_query_arg( array( 'page' => 'cp-general-settings' ), 'admin.php' ) ),
			'name' => __( 'Components', 'categorypress' )
		),
		'1' => array(
			'href' => cp_get_admin_url( add_query_arg( array( 'page' => 'cp-page-settings' ), 'admin.php' ) ),
			'name' => __( 'Pages', 'categorypress' )
		),
		'2' => array(
			'href' => cp_get_admin_url( add_query_arg( array( 'page' => 'cp-settings' ), 'admin.php' ) ),
			'name' => __( 'Settings', 'categorypress' )
		)
	);


	// Allow the tabs to be filtered
	$tabs = apply_filters( 'cp_core_admin_tabs', $tabs );

	// Loop through tabs and build navigation
	foreach ( array_values( $tabs ) as $tab_data ) {
		$is_current = (bool) ( $tab_data['name'] == $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
	}

	// Output the tabs
	echo $tabs_html;

	// Do other fun things
	do_action( 'cp_admin_tabs' );
}

function cp_register_admin_settings() {
	do_action( 'cp_register_admin_settings' );
}