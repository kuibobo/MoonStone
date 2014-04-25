<?php

/**
 * CategoryPress Categories Loader
 *
 * A Categories component
 *
 * @package CategoryPress
 * @suCPackage CategoriesLoader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Categories_Component extends CP_Component {
	
	/**
	 * Start the categories component creation process
	 *
	 * @since categorypress (1.5)
	 */
	function __construct() {
		parent::start(
			'categories',
			__( 'categories', 'categorypress' ),
			CP_PLUGIN_DIR
		);
	}


	/**
	 * Include files
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'screens',
			'classes',
			'functions'
		);

		if ( is_admin() )
			$includes[] = 'admin';

		parent::includes( $includes );
	}


	function setup_globals() {
		global $cp;

		if ( !defined( 'CP_CATEGORIES_SLUG' ) )
			define( 'CP_CATEGORIES_SLUG', $this->id );

		// Global tables for messaging component
		$global_tables = array(
			'table_name'                     => $cp->table_prefix . 'cp_categories',
			'table_name_c_in_c'              => $cp->table_prefix . 'cp_categories_in_categories',
			'table_name_categorymeta'        => $cp->table_prefix . 'CP_Categorymeta'
		);

		// All globals for categories component.
		// Note that global_tables is included in this array.
		$globals = array(
			'slug'                  => CP_CATEGORIES_SLUG,
			'root_slug'             => isset( $cp->pages->categories->slug ) ? $cp->pages->categories->slug : CP_CATEGORIES_SLUG,
			'has_directory'         => true,
			'notification_callback' => 'categories_format_notifications',
			'search_string'         => __( 'Search Categories...', 'categorypress' ),
			'global_tables'         => $global_tables
		);

		parent::setup_globals( $globals );
	}
}

function cp_setup_category() {
	global $cp;
	
	$cp->categories = new CP_Categories_Component();
}

add_action( 'cp_setup_components',    'cp_setup_category',  7);