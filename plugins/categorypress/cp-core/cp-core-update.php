<?php

if ( !defined( 'ABSPATH' ) ) exit;

function cp_is_install() {
	return ! cp_get_db_version_raw();
}

function cp_is_update() {
	
	// Current DB version of this site (per site in a multisite network)
	$current_db   = cp_get_option( '_cp_db_version' );
	$current_live = cp_get_db_version();
	
	// Compare versions (cast as int and bool to be safe)
	$is_update = (bool) ( (int) $current_db < (int) $current_live );
	
	// Return the product of version comparison
	return $is_update;
}

function cp_setup_updater() {
	
	// Are we running an outdated version of BuddyPress?
	if ( ! cp_is_update() )
		return;
	
	cp_version_updater();
}

function cp_version_updater() {
	
	// Get the raw database version
	$raw_db_version = (int) cp_get_db_version_raw();
	
	$default_components = apply_filters( 'cp_new_install_default_components', 
		array( 
		'categories' => 1, 
		'posts' => 1, 
		//'members' => 1 
		) 
	);
	require_once( CP_PLUGIN_DIR . '/cp-core/admin/cp-core-schema.php' );
	
	if ( cp_is_install() ) {
		cp_core_install( $default_components );
		cp_update_option( 'cp-active-components', $default_components );
		cp_core_add_page_mappings( $default_components, 'delete' );
		
	// Upgrades
	} else {
		
		// Run the schema install to update tables
		cp_core_install();
	}
	
	/** All done! *************************************************************/
	
	// Bump the version
	cp_version_bump();
}

function cp_version_bump() {
	cp_update_option( '_cp_db_version', cp_get_db_version() );
}