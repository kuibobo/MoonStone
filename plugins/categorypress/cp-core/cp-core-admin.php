<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CP_Admin' ) ) :

class CP_Admin {
	
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}
	
	
	private function setup_globals() {
		$cp = categorypress();
		
		// Paths and URLs
		$this->admin_dir  = trailingslashit( $cp->plugin_dir  . 'cp-core/admin' ); // Admin path
		$this->admin_url  = trailingslashit( $cp->plugin_url  . 'cp-core/admin' ); // Admin url
		$this->images_url = trailingslashit( $this->admin_url . 'images'        ); // Admin images URL
		$this->css_url    = trailingslashit( $this->admin_url . 'css'           ); // Admin css URL
		$this->js_url     = trailingslashit( $this->admin_url . 'js'            ); // Admin css URL
		
		// Main settings page
		$this->settings_page = 'cp-general-settings';//cp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
	}
	
	private function includes() {
		require( $this->admin_dir . 'cp-core-actions.php'    );
		require( $this->admin_dir . 'cp-core-settings.php'   );
		require( $this->admin_dir . 'cp-core-functions.php'  );
		require( $this->admin_dir . 'cp-core-components.php' );
		require( $this->admin_dir . 'cp-core-slugs.php'      );
	}
	
	private function setup_actions() {
		
		/** General Actions ***************************************************/
		
		// Add some page specific output to the <head>
		add_action( 'cp_admin_head',            array( $this, 'admin_head'  ), 999 );
		
		// Add menu item to settings menu
		add_action( cp_core_admin_hook(),       array( $this, 'admin_menus' ), 5 );
		
		// Enqueue all admin JS and CSS
		add_action( 'cp_admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		/** CategoryPress Actions ************************************************/
		
		// Load the CategoryPress metabox in the WP Nav Menu Admin UI
		add_action( 'load-nav-menus.php', 'cp_admin_wp_nav_menu_meta_box' );
		
		// Add settings
		add_action( 'cp_register_admin_settings', array( $this, 'register_admin_settings' ) );
		
		// Add a link to CategoryPress About page to the admin bar
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_about_link' ), 15 );
		
		/** Filters ***********************************************************/
		
		// Add link to settings page
		add_filter( 'plugin_action_links',               array( $this, 'modify_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );
	}
	
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not categorypress
		if ( plugin_basename( categorypress()->file ) != $file )
			return $links;

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . add_query_arg( array( 'page' => 'cp-components' ), cp_get_admin_url( $this->settings_page ) ) . '">' . esc_html__( 'Settings', 'categorypress' ) . '</a>',
			'about'    => '<a href="' . add_query_arg( array( 'page' => 'cp-about'      ), cp_get_admin_url( 'index.php'          ) ) . '">' . esc_html__( 'About',    'categorypress' ) . '</a>'
		) );
	}
	
	public function admin_head() {
		
		// Settings pages
		remove_submenu_page( $this->settings_page, 'cp-page-settings' );
		remove_submenu_page( $this->settings_page, 'cp-settings'      );
		
		// About and Credits pages
		remove_submenu_page( 'index.php', 'cp-about'   );
		remove_submenu_page( 'index.php', 'cp-credits' );
	}
	
	public function admin_menus() {
		
		// Bail if user cannot moderate
		if ( ! cp_current_user_can( 'manage_options' ) )
			return;
			
		// About
		add_dashboard_page(
				__( 'Welcome to CategoryPress',  'categorypress' ),
				__( 'Welcome to CategoryPress',  'categorypress' ),
				'manage_options',
				'cp-about',
				array( $this, 'about_screen' )
				);
				
		$hooks = array();

		$hooks[] = add_menu_page(
			__( 'CategoryPress', 'categorypress' ),
			__( 'CategoryPress', 'categorypress' ),
			'manage_options',
			$this->settings_page,
			'cp_core_admin_backpat_menu'
		);
		
		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'CategoryPress Settings', 'categorypress' ),
			__( 'Settings', 'categorypress' ),
			'manage_options',
			'cp-general-settings',
			'cp_core_admin_components_settings'
		);
		
		// Add the option pages
		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'CategoryPress Pages', 'categorypress' ),
			__( 'CategoryPress Pages', 'categorypress' ),
			'manage_options',
			'cp-page-settings',
			'cp_core_admin_slugs_settings'
		);

		$hooks[] = add_submenu_page(
			$this->settings_page,
			__( 'CategoryPress Settings', 'categorypress' ),
			__( 'CategoryPress Settings', 'categorypress' ),
			'manage_options',
			'cp-settings',
			'cp_core_admin_settings'
		);

		// Fudge the highlighted subnav item when on a CategoryPress admin page
		foreach( $hooks as $hook ) {
			add_action( "admin_head-$hook", 'cp_core_modify_admin_menu_highlight' );
		}
	}
	
	public function register_admin_settings() {
	
		// Add the main section
		add_settings_section( 'cp_main',           __( 'Main Settings',    'categorypress' ), 'cp_admin_setting_callback_main_section',     'categorypress'            );
		add_settings_field( 'cp-meta-ed2k-name',   __( 'ed2k',  'categorypress' ),  'cp_admin_setting_callback_meta_ed2k_settings',     'categorypress', 'cp_main' );
		add_settings_field( 'cp-meta-magnet-name', __( 'magnet',  'categorypress' ), 'cp_admin_setting_callback_meta_maget_settings',     'categorypress', 'cp_main' );
		
	}
	
	public function admin_bar_about_link( $wp_admin_bar ) {
		if ( is_user_logged_in() ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'cp-logo',
				'id'     => 'cp-about',
				'title'  => esc_html__( 'About CategoryPress', 'categorypress' ),
				'href'   => add_query_arg( array( 'page' => 'cp-about' ), cp_get_admin_url( 'index.php' ) ),
			) );
		}
	}
	
	public function about_screen() {
		global $wp_rewrite;
		
		$is_new_install = ! empty( $_GET['is_new_install'] );

		$pretty_permalinks_enabled = ! empty( $wp_rewrite->permalink_structure );

		list( $display_version ) = explode( '-', cp_get_version() ); ?>
		
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to CategoryPress %s', 'categorypress' ), $display_version ); ?></h1>
		</div>
		<?
	}
} 

endif;

function cp_admin() {
	categorypress()->admin = new CP_Admin();
}
