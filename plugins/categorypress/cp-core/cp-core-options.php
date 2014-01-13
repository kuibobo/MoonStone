<?php
if ( !defined( 'ABSPATH' ) ) exit;

function cp_get_default_options() {
	
	// Default options
	$options = array (
			
			/** Components ********************************************************/
			
			'cp-deactivated-components'       => array(),
						
			/** XProfile **********************************************************/
			
			// Base profile groups name
			'cp-xprofile-base-group-name'     => 'Base',
			
			// Base fullname field name
			'cp-xprofile-fullname-field-name' => 'Name',
			
			/** Blogs *************************************************************/
			
			// Used to decide if blogs need indexing
			'cp-blogs-first-install'          => false,
			
			/** Settings **********************************************************/
			
			// Disable the WP to BP profile sync
			'cp-disable-profile-sync'         => false,
			
			// Hide the Toolbar for logged out users
			'hide-loggedout-adminbar'         => false,
			
			// Avatar uploads
			'cp-disable-avatar-uploads'       => false,
			
			// Allow users to delete their own accounts
			'cp-disable-account-deletion'     => false,
			
			// Allow comments on blog and forum activity items
			'cp-disable-blogforum-comments'   => true,
			
			// The ID for the current theme package.
			'_cp_theme_package_id'            => 'legacy',
			
			/** Groups ************************************************************/
			
			// @todo Move this into the groups component
			
			// Restrict group creation to super admins
			'cp_restrict_group_creation'      => false,
			
			/** Akismet ***********************************************************/
			
			// Users from all sites can post
			'_cp_enable_akismet'              => true,
			
			/** BuddyBar **********************************************************/
			
			// Force the BuddyBar
			'_cp_force_buddybar'              => false
			);
	
	return apply_filters( 'cp_get_default_options', $options );
}

function cp_get_option( $option_name, $default = '' ) {
	$value = get_blog_option( cp_get_root_blog_id(), $option_name, $default );
	
	return apply_filters( 'cp_get_option', $value );
}

function cp_update_option( $option_name, $value ) {
	update_blog_option( cp_get_root_blog_id(), $option_name, $value );
}

function cp_core_get_root_options() {
	global $wpdb;
	
	// Get all the BuddyPress settings, and a few useful WP ones too
	$root_blog_options                   = cp_get_default_options();
	$root_blog_options['registration']   = '0';
	$root_blog_options['avatar_default'] = 'mysteryman';
	$root_blog_option_keys               = array_keys( $root_blog_options );
	
	// Do some magic to get all the root blog options in 1 swoop
	$blog_options_keys      = "'" . join( "', '", (array) $root_blog_option_keys ) . "'";
	$blog_options_table	    = cp_is_multiblog_mode() ? $wpdb->options : $wpdb->get_blog_prefix( cp_get_root_blog_id() ) . 'options';
	$blog_options_query     = "SELECT option_name AS name, option_value AS value FROM {$blog_options_table} WHERE option_name IN ( {$blog_options_keys} )";
	$root_blog_options_meta = $wpdb->get_results( $blog_options_query );
	
	// On Multisite installations, some options must always be fetched from sitemeta
	if ( is_multisite() ) {
		$network_options = apply_filters( 'cp_core_network_options', array(
					'tags_blog_id'       => '0',
					'sitewide_tags_blog' => '',
					'registration'       => '0',
					'fileupload_maxk'    => '1500'
					) );
		
		$current_site           = get_current_site();
		$network_option_keys    = array_keys( $network_options );
		$sitemeta_options_keys  = "'" . join( "', '", (array) $network_option_keys ) . "'";
		$sitemeta_options_query = $wpdb->prepare( "SELECT meta_key AS name, meta_value AS value FROM {$wpdb->sitemeta} WHERE meta_key IN ( {$sitemeta_options_keys} ) AND site_id = %d", $current_site->id );
		$network_options_meta   = $wpdb->get_results( $sitemeta_options_query );
		
		// Sitemeta comes second in the merge, so that network 'registration' value wins
		$root_blog_options_meta = array_merge( $root_blog_options_meta, $network_options_meta );
	}
	
	// Missing some options, so do some one-time fixing
	if ( empty( $root_blog_options_meta ) || ( count( $root_blog_options_meta ) < count( $root_blog_option_keys ) ) ) {
		
		// Get a list of the keys that are already populated
		$existing_options = array();
		foreach( $root_blog_options_meta as $already_option ) {
			$existing_options[$already_option->name] = $already_option->value;
		}
		
		// Unset the query - We'll be resetting it soon
		unset( $root_blog_options_meta );
		
		// Loop through options
		foreach ( $root_blog_options as $old_meta_key => $old_meta_default ) {
			// Clear out the value from the last time around
			unset( $old_meta_value );
			
			if ( isset( $existing_options[$old_meta_key] ) ) {
				continue;
			}
			
			// Get old site option
			if ( is_multisite() )
				$old_meta_value = get_site_option( $old_meta_key );
			
			// No site option so look in root blog
			if ( empty( $old_meta_value ) )
				$old_meta_value = cp_get_option( $old_meta_key, $old_meta_default );
			
			// Update the root blog option
			cp_update_option( $old_meta_key, $old_meta_value );
			
			// Update the global array
			$root_blog_options_meta[$old_meta_key] = $old_meta_value;
		}
		
		$root_blog_options_meta = array_merge( $root_blog_options_meta, $existing_options );
		unset( $existing_options );
		
		// We're all matched up
	} else {
		// Loop through our results and make them usable
		foreach ( $root_blog_options_meta as $root_blog_option )
			$root_blog_options[$root_blog_option->name] = $root_blog_option->value;
		
		// Copy the options no the return val
		$root_blog_options_meta = $root_blog_options;
		
		// Clean up our temporary copy
		unset( $root_blog_options );
	}
	return apply_filters( 'cp_core_get_root_options', $root_blog_options_meta );
}