<?php
/**
 * Plugin Name: CategoryPress
 * Plugin URI: http://Sigma
 * Description: This is a plugin for Sigma Project
 * Version: 1.0.0
 * Author: Bourne Jiang
 * Author URI: http://Sigma
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CategoryPress' ) ) :
	
	/**
	 * This is class CategoryPress
	 * 
	 * @time 2013-09-12
	 * @author Bourne
	 */
	class CategoryPress {
		
		public $table_prefix;

		public $version = '1.0.0';

		public $plugin_dir = '';

		public $plugin_url = '';

		public $themes_dir = '';

		public $themes_url = '';

		public $lang_dir = '';
		
		public $options = array();
		
		public $loaded_components = array();
		
		private static $instance;
		
		private function __construct() {}
		
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new CategoryPress;
				
				self::$instance->constants();
				self::$instance->setup_globals();
				self::$instance->includes();
				self::$instance->setup_actions();
			}
			return self::$instance;
		}
		
		private function constants() {
			if ( !defined( 'CP_PLUGIN_DIRNAME' ) )
				define( 'CP_PLUGIN_DIRNAME', basename( dirname( __FILE__ ) ) );

			// Path and URL
			if ( !defined( 'CP_PLUGIN_DIR' ) )
				define( 'CP_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/' . CP_PLUGIN_DIRNAME ) );

			if ( !defined( 'CP_PLUGIN_URL' ) ) {
				$plugin_url = trailingslashit( plugins_url( CP_PLUGIN_DIRNAME ) );
				define( 'CP_PLUGIN_URL', $plugin_url );
			}
			
			if ( !defined( 'CP_ROOT_BLOG' ) ) {
				$root_blog_id = get_current_blog_id();
				
				define( 'CP_ROOT_BLOG', $root_blog_id );
			}
			
			// Define the database version
			if ( !defined( 'CP_DB_VERSION' ) )
				define( 'CP_DB_VERSION', $this->db_version );
				
			if ( !defined( 'CP_PAGE_EXTENSION' ) )
				define( 'CP_PAGE_EXTENSION', 'shtml' );
		}
		
		private function includes() {
			require( CP_PLUGIN_DIR . '/cp-core/cp-core-wpabstraction.php' );
			
			$this->versions();
			
			require( $this->plugin_dir . 'cp-core/cp-core-dependency.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-actions.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-caps.php'       );
			require( $this->plugin_dir . 'cp-core/cp-core-update.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-options.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-classes.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-template.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-catchuri.php'   );
			require( $this->plugin_dir . 'cp-core/cp-core-component.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-functions.php' );
			require( $this->plugin_dir . 'cp-core/cp-core-loader.php' );
		}
		
		private function versions() {
			$versions               = array();
			$versions['1.6-single'] = get_blog_option( $this->root_blog_id, '_cp_db_version' );
			
			$this->db_version_raw = (int) $versions['1.0-single'];
		}
		
		private function setup_globals() {
			
			$this->version    = '1.0.0';
			$this->db_version = 1004;
			
			/**
			* @var string Name of the current BuddyPress component (primary)
			*/
			$this->current_component = '';
			
			/**
			 * @var string Name of the current BuddyPress item (secondary)
			 */
			$this->current_post = '';
			
			$this->current_categories = '';
						
			$this->root_blog_id = (int) apply_filters( 'cp_get_root_blog_id', CP_ROOT_BLOG );
			
			// root directory
			$this->file       = __FILE__;
			$this->basename   = plugin_basename( $this->file );
			$this->plugin_dir = CP_PLUGIN_DIR;
			$this->plugin_url = CP_PLUGIN_URL;			

			// Themes
			$this->themes_dir = $this->plugin_dir . 'cp-themes';
			$this->themes_url = $this->plugin_url . 'cp-themes';
			
			// Languages
			$this->lang_dir   = $this->plugin_dir . 'cp-languages';
		}
		
		private function setup_actions() {
			add_action( 'activate_'   . $this->basename, 'cp_activation'   );
			add_action( 'deactivate_' . $this->basename, 'cp_deactivation' );

			// Register the CP theme directory
			register_theme_directory( $this->themes_dir );
		}
	}
	
	function categorypress() {
		return CategoryPress::instance();
	}
	
	if ( defined( 'CATEGORYRESS_LATE_LOAD' ) ) {
		add_action( 'plugins_loaded', 'categorypress', (int) CATEGORYRESS_LATE_LOAD );
		
		// "And now here's something we hope you'll really like!"
	} else {
		// CategoryPress init
		$GLOBALS['cp'] = &categorypress();
	}
endif;
