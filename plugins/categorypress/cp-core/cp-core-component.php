<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CP_Component' ) ) :

/**
 * CategoryPress Component Class
 * *
 * @package CategoryPress
 * @subpackage Component
 *
 * @since Sigma (1.1)
 */

class CP_Component {
	
	public $id;
	public $name;
	public $path;
	public $slug = '';
	public $has_directory = false;
	
	function start( $id, $name, $path ) {
		// Internal identifier of component
		$this->id   = $id;

		// Internal component name
		$this->name = $name;

		// Path for includes
		$this->path = $path;

		// Move on to the next step
		$this->setup_actions();
	}
	
	
	function setup_actions() {
		// Setup globals
		add_action( 'cp_setup_globals', array ( $this, 'setup_globals' ), 10);

		add_action( 'cp_include',       array ( $this, 'includes'      ), 8);
		
		// Setup screen
		add_action( 'cp_setup_nav',       array( $this, 'setup_screens' ), 10 );
	}
	
	function setup_globals( $args = array() ) {
		
		$default_root_slug = isset( categorypress()->pages->{$this->id}->slug ) ? categorypress()->pages->{$this->id}->slug : '';
		
		$defaults = array(
			'slug'                  => $this->id,
				'has_directory'         => false,
			'global_tables'         => ''
			);
		
		$r = wp_parse_args( $args, $defaults );
		
		if ( !empty( $r['global_tables'] ) ) {
			foreach ( $r['global_tables'] as $global_name => $table_name ) {
				$this->{$global_name} = $table_name;
			}
		}
		
		$this->slug                  = apply_filters( 'cp_' . $this->id . '_slug',                  $r['slug']                  );
		$this->has_directory         = apply_filters( 'cp_' . $this->id . '_has_directory',         $r['has_directory']         );
		
		categorypress()->loaded_components[$this->slug] = $this->id;
	}
	
	public function setup_screens( $screen_function = '' ) {
		if ( is_callable( $screen_function ) ) 
			add_action( 'cp_screens', $screen_function, 3 );
	}
	
	public function includes( $includes = array() ) {
		if ( empty( $includes ) )
			return;

		$slashed_path = trailingslashit( $this->path );

		// Loop through files to be included
		foreach ( $includes as $file ) {

			$paths = array(

				// Passed with no extension
				'cp-' . $this->id . '/cp-' . $this->id . '-' . $file  . '.php',
				'cp-' . $this->id . '-' . $file . '.php',
				'cp-' . $this->id . '/' . $file . '.php',

				// Passed with extension
				$file,
				'cp-' . $this->id . '-' . $file,
				'cp-' . $this->id . '/' . $file,
				);

			foreach ( $paths as $path ) {
				if ( @is_file( $slashed_path . $path ) ) {
					require( $slashed_path . $path );
					continue;
				}
			}
		}

		// Call action
		do_action( 'cp_' . $this->id . '_includes' );
	}
}

endif;
