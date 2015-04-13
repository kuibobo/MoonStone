<?php

/**
 * CategoryPress Core Loader
 *
 * Core contains the commonly used functions, classes, and API's
 *
 * @package CategoryPress
 * @subpackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Core extends CP_Component {
	
	function __construct() {
		parent::start(
				'core',
				__( 'CategoryPress Core', 'CategoryPress' )
				, CP_PLUGIN_DIR
				);
		
		$this->bootstrap();
	}
	
	private function bootstrap() {
		global $cp;
		
		// Set the included and optional components.
		$cp->optional_components = apply_filters( 'cp_optional_components', array( '' ) );
		
		// Set the required components
		$cp->required_components = apply_filters( 'cp_required_components', array( 'categories', 'posts' ) );
		
		// Loop through optional components
		foreach( $cp->optional_components as $component )
			if ( file_exists( CP_PLUGIN_DIR . '/cp-' . $component . '/cp-' . $component . '-loader.php' ) )
				include( CP_PLUGIN_DIR . '/cp-' . $component . '/cp-' . $component . '-loader.php' );

		// Loop through required components
		foreach( $cp->required_components as $component )
			if ( file_exists( CP_PLUGIN_DIR . '/cp-' . $component . '/cp-' . $component . '-loader.php' ) )
				include( CP_PLUGIN_DIR . '/cp-' . $component . '/cp-' . $component . '-loader.php' );

		// Add Core to required components
		$cp->required_components[] = 'core';
	}
	
	public function setup_globals( $args = array() ) {
		global $cp;
		
		if ( empty( $cp->table_prefix ) )
			$cp->table_prefix = cp_core_get_table_prefix();
					
		if ( empty( $cp->root_domain ) )
			$cp->root_domain = cp_core_get_root_domain();
		
		if ( empty( $cp->site_options ) )
			$cp->site_options = cp_core_get_root_options();
		
		if ( empty( $cp->pages ) )
			$cp->pages = cp_core_get_directory_pages();
	}
	
	public function includes( $includes = array() ) {
		
		if ( !is_admin() )
			return;
		
		$includes = array(
				'admin'
				);
		
		parent::includes( $includes );
	}
	
}

/**
 * Setup the CategoryPress Core component
 *
 * @since Sigma (1.1)
 *
 * @global CategoryPress $cp
 */
function cp_setup_core() {
	global $cp;
	$cp->core = new CP_Core();
}
add_action( 'cp_setup_components', 'cp_setup_core', 2 );

?>