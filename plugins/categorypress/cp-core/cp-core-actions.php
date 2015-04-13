<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


add_action( 'plugins_loaded',          'cp_loaded',                 10    );
add_action( 'init',                    'cp_init',                   10    );
add_action( 'template_redirect',       'cp_template_redirect',      10    );


add_action( 'cp_loaded',  'cp_setup_components', 2 );
add_action( 'cp_loaded',  'cp_include',          4 );

add_action( 'cp_init',    'cp_core_set_uri_globals',    2  );
add_action( 'cp_init',    'cp_setup_globals',           5 );
add_action( 'cp_init',    'cp_setup_nav',                  6  );

add_action( 'cp_template_redirect', 'cp_actions', 4 );
add_action( 'cp_template_redirect', 'cp_screens', 6 );



/** Sub-Actions ***************************************************************/

// Load the admin
if ( is_admin() ) {
	add_action( 'cp_loaded', 'cp_admin' );
}