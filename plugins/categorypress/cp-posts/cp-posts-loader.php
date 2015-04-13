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
			__( 'Posts', 'categorypress' ),
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


	public function setup_globals() {
		global $cp;

		if ( !defined( 'CP_POSTS_SLUG' ) )
			define( 'CP_POSTS_SLUG', $this->id );

		// Global tables for messaging component
		$global_tables = array(
			'table_name'           => $cp->table_prefix . 'cp_posts',
			'table_name_postmeta'  => $cp->table_prefix . 'cp_postmeta',
			'table_name_pinc'      => $cp->table_prefix . 'cp_post_in_categories'
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
		
		/** Single Post Globals *****************/
		$this->current_post = 0;
		$this->category_verifed = true;
		if ( cp_is_posts_component() ) {
			$categories = cp_current_categories();
						
			if ( !empty( $categories ) ) {
				$category_cout = count( $categories );
				
				//for ( $i = $category_cout - 1; $i > 0; $i-- ) {
				//	if ( !cp_categories_check_category_exists( $categories[ $i ], $categories[ $i - 1 ] ) ) {
				//		$this->category_verifed = false;
				//		break;
				//	}
				//}
				
				if ( $this->category_verifed == true )
					$this->category_verifed = cp_categories_check_category_exists( $categories[0] );
			}
			
			if ( $this->category_verifed == false ) {
				cp_do_404();
				return;
			}
			
			if ( $post_id = CP_Post::post_exists( cp_current_post_id() ) ) {
				
				if ( $this->category_verifed == true )
					$this->category_verifed = cp_categories_check_category_exists( $categories[1] );
					
				if ( $this->category_verifed == false ) {
					cp_do_404();
					return;
				}
				
				$cp->is_single_item  = true;
			} else {
				$cp->is_single_item  = false;
				
				cp_do_404();
				return;
			}
		} 
	}
	
	public function setup_screens( $screen_function = '' ) {
		
		/** is single page ********************/
		if ( $this->category_verifed && cp_is_posts_component() ) {
			$post_id = cp_current_post_id();
			if ( empty( $post_id ) ) 
				$screen_function = 'cp_posts_screen_category';
			else
				$screen_function = 'cp_posts_screen_single';
			
			parent::setup_screens( $screen_function );
		}
	}
}

function cp_setup_post() {
	global $cp;
	
	$cp->posts = new CP_Posts_Component();
}

add_action( 'cp_setup_components',    'cp_setup_post',  6);