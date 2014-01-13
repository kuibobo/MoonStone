<?php

/**
 * CategoryPress Post Loader
 *
 * A Posts component
 *
 * @package CategoryPress
 * @suCPackage PostLoader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class CP_Posts_Component extends CP_Component {
	
	/**
	 * Start the posts component creation process
	 *
	 * @since categorypress (1.5)
	 */
	function __construct() {
		parent::start(
			'posts',
			__( 'posts', 'categorypress' ),
			CP_PLUGIN_DIR
		);
	}


	/**
	 * Include files
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'actions',
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

		if ( !defined( 'CP_POSTS_SLUG' ) )
			define( 'CP_POSTS_SLUG', $this->id );

		// Global tables for messaging component
		$global_tables = array(
			'table_name'           => $cp->table_prefix . 'cp_posts',
			'table_name_postmeta' => $cp->table_prefix . 'cp_postmeta'
		);

		// All globals for posts component.
		// Note that global_tables is included in this array.
		$globals = array(
			'slug'                  => CP_POSTS_SLUG,
			'root_slug'             => isset( $cp->pages->posts->slug ) ? $cp->pages->posts->slug : CP_POSTS_SLUG,
			'has_directory'         => true,
			'notification_callback' => 'posts_format_notifications',
			'search_string'         => __( 'Search Posts...', 'categorypress' ),
			'global_tables'         => $global_tables
		);

		parent::setup_globals( $globals );
	}
}

function cp_setup_post() {
	global $cp;
	
	$cp->posts = new CP_Posts_Component();
}

add_action( 'cp_setup_components',    'cp_setup_post',  6);