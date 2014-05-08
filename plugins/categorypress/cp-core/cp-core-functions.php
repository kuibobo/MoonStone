<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_version() {
	echo cp_get_version();
}

function cp_get_version() {
	return categorypress()->version;
}

function cp_db_version() {
	echo cp_get_db_version();
}

function cp_get_db_version() {
	return categorypress()->db_version;
}

function cp_db_version_raw() {
	echo cp_get_db_version_raw();
}

function cp_get_db_version_raw() {
	$cp     = categorypress();
	return !empty( $cp->db_version_raw ) ? $cp->db_version_raw : 0;
}

/**
 * Get the $wpdb base prefix, run through the 'cp_core_get_table_prefix' filter.
 *
 * The filter is intended primarily for use in multinetwork installations.
 *
 * @global object $wpdb WordPress database object.
 *
 * @return string Filtered database prefix.
 */
function cp_core_get_table_prefix() {
	global $wpdb;
	
	return apply_filters( 'cp_core_get_table_prefix', $wpdb->base_prefix );
}

/**
 * Return the domain for the root blog.
 *
 * eg: http://domain.com OR https://domain.com
 *
 * @uses get_blog_option() WordPress function to fetch blog meta.
 *
 * @return string The domain URL for the blog.
 */
function cp_core_get_root_domain() {
	$domain = get_home_url( cp_get_root_blog_id() );
	
	return apply_filters( 'cp_core_get_root_domain', $domain );
}

function cp_get_files( $pathname, $depth = 1 ){
	
	$files = array();
	
	if ( $depth == -1 )
		return $files;
		
	foreach( glob( $pathname ) as $filename ){
		if( is_dir( $filename ) )
			$files = array_merge( $files, cp_get_files( $filename . '/*', --$depth ) );  
		else
			$files[] = $filename ;
	}
	
	return $files;
}

function cp_get_file_content( $file ) {
	$contents = '';
	
	$handle = fopen ( $file, 'rb' );
	while ( !feof( $handle ) ) 
		$contents .= fread( $handle, 1024 );

	fclose( $handle );
	
	return $contents;
}

function cp_get_root_blog_id() {
	global $cp;
	
	return (int) apply_filters( 'cp_get_root_blog_id', (int) $cp->root_blog_id );
}

/**
 * Is BuddyPress active at the network level for this network?
 *
 * Used to determine admin menu placement, and where settings and options are
 * stored. If you're being *really* clever and manually pulling BuddyPress in
 * with an mu-plugin or some other method, you'll want to filter
 * 'cp_is_network_activated' and override the auto-determined value.
 *
 * @since BuddyPress (1.7.0)
 *
 * @return bool True if BuddyPress is network activated.
 */
function cp_is_network_activated() {
	
	// Default to is_multisite()
	$retval  = is_multisite();
	
	// Check the sitewide plugins array
	$base    = categorypress()->basename;
	$plugins = get_site_option( 'active_sitewide_plugins' );
	
	// Override is_multisite() if not network activated
	if ( ! is_array( $plugins ) || ! isset( $plugins[$base] ) )
		$retval = false;
	
	return (bool) apply_filters( 'cp_is_network_activated', $retval );
}

/**
 * Should BuddyPress appear in network admin (vs a single site Dashboard)?
 *
 * Because BuddyPress can be installed in multiple ways and with multiple
 * configurations, we need to check a few things to be confident about where
 * to hook into certain areas of WordPress's admin.
 *
 * @since BuddyPress (1.5.0)
 *
 * @uses cp_is_network_activated()
 * @uses cp_is_multiblog_mode()
 *
 * @return bool True if the BP admin screen should appear in the Network Admin,
 *         otherwise false.
 */
function cp_is_multiblog_mode() {
	
	// Setup some default values
	$retval         = false;
	$is_multisite   = is_multisite();
	$network_active = cp_is_network_activated();
	$is_multiblog   = defined( 'CP_ENABLE_MULTIBLOG' ) && CP_ENABLE_MULTIBLOG;
	
	// Multisite, Network Activated, and Specifically Multiblog
	if ( $is_multisite && $network_active && $is_multiblog ) {
		$retval = true;
		
		// Multisite, but not network activated
	} elseif ( $is_multisite && ! $network_active ) {
		$retval = true;
	}
	
	return apply_filters( 'cp_is_multiblog_mode', $retval );
}

/**
 * Get the current GMT time to save into the DB.
 *
 * @since BuddyPress (1.2.6)
 *
 * @param bool $gmt True to use GMT (rather than local) time. Default: true.
 * @return string Current time in 'Y-m-d h:i:s' format.
 */
function cp_core_current_time( $gmt = true ) {
	// Get current time in MYSQL format
	$current_time = current_time( 'mysql', $gmt );
	
	return apply_filters( 'cp_core_current_time', $current_time );
}

/**
 * Fetch a list of BP directory pages from the appropriate meta table.
 *
 * @since BuddyPress (1.5.0)
 *
 * @return array|string An array of page IDs, keyed by component names, or an
 *         empty string if the list is not found.
 */
function cp_core_get_directory_page_ids() {
	$page_ids = cp_get_option( 'cp-pages' );
	
	// Ensure that empty indexes are unset. Should only matter in edge cases
	if ( !empty( $page_ids ) && is_array( $page_ids ) ) {
		foreach( (array) $page_ids as $component_name => $page_id ) {
			if ( empty( $component_name ) || empty( $page_id ) ) {
				unset( $page_ids[$component_name] );
			}
		}
	}
	
	return apply_filters( 'cp_core_get_directory_page_ids', $page_ids );
}

/**
 * Return the referrer URL without the http(s)://
 *
 * @return string The referrer URL.
 */
function cp_core_referrer() {
	$referer = explode( '/', wp_get_referer() );
	unset( $referer[0], $referer[1], $referer[2] );
	return implode( '/', $referer );
}

/**
 * Get names and slugs for BuddyPress component directory pages.
 *
 * @since BuddyPress (1.5.0).
 *
 * @return object Page names, IDs, and slugs.
 */
function cp_core_get_directory_pages() {
	global $wpdb;
	
	// Set pages as standard class
	$pages = new stdClass;
	
	// Get pages and IDs
	$page_ids = cp_core_get_directory_page_ids();
	if ( !empty( $page_ids ) ) {
		
		// Always get page data from the root blog, except on multiblog mode, when it comes
		// from the current blog
		$posts_table_name = cp_is_multiblog_mode() ? $wpdb->posts : $wpdb->get_blog_prefix( cp_get_root_blog_id() ) . 'posts';
		$page_ids_sql     = implode( ',', wp_parse_id_list( $page_ids ) );
		$page_names       = $wpdb->get_results( "SELECT ID, post_name, post_parent, post_title FROM {$posts_table_name} WHERE ID IN ({$page_ids_sql}) AND post_status = 'publish' " );
		
		foreach ( (array) $page_ids as $component_id => $page_id ) {
			foreach ( (array) $page_names as $page_name ) {
				if ( $page_name->ID == $page_id ) {
					if ( !isset( $pages->{$component_id} ) || !is_object( $pages->{$component_id} ) ) {
						$pages->{$component_id} = new stdClass;
					}
					
					$pages->{$component_id}->name  = $page_name->post_name;
					$pages->{$component_id}->id    = $page_name->ID;
					$pages->{$component_id}->title = $page_name->post_title;
					$slug[]                           = $page_name->post_name;
					
					// Get the slug
					while ( $page_name->post_parent != 0 ) {
						$parent                 = $wpdb->get_results( $wpdb->prepare( "SELECT post_name, post_parent FROM {$posts_table_name} WHERE ID = %d", $page_name->post_parent ) );
						$slug[]                 = $parent[0]->post_name;
						$page_name->post_parent = $parent[0]->post_parent;
					}
					
					$pages->{$component_id}->slug = implode( '/', array_reverse( (array) $slug ) );
				}
				
				unset( $slug );
			}
		}
	}
	
	return apply_filters( 'cp_core_get_directory_pages', $pages );
}

/**
 * Is this the root blog?
 *
 * @since BuddyPress (1.5.0)
 *
 * @param int $blog_id Optional. Default: the ID of the current blog.
 * @return bool $is_root_blog Returns true if this is cp_get_root_blog_id().
 */
function cp_is_root_blog( $blog_id = 0 ) {
	
	// Assume false
	$is_root_blog = false;
	
	// Use current blog if no ID is passed
	if ( empty( $blog_id ) )
		$blog_id = get_current_blog_id();
	
	// Compare to root blog ID
	if ( $blog_id == cp_get_root_blog_id() )
		$is_root_blog = true;
	
	return (bool) apply_filters( 'cp_is_root_blog', (bool) $is_root_blog );
}

/**
 * Get the path of of the current site.
 *
 * @global object $current_site
 *
 * @return string URL to the current site.
 */
function cp_core_get_site_path() {
	global $current_site;
	
	if ( is_multisite() )
		$site_path = $current_site->path;
	else {
		$site_path = (array) explode( '/', home_url() );
		
		if ( count( $site_path ) < 2 )
			$site_path = '/';
		else {
			// Unset the first three segments (http(s)://domain.com part)
			unset( $site_path[0] );
			unset( $site_path[1] );
			unset( $site_path[2] );
			
			if ( !count( $site_path ) )
				$site_path = '/';
			else
				$site_path = '/' . implode( '/', $site_path ) . '/';
		}
	}
	
	return apply_filters( 'cp_core_get_site_path', $site_path );
}

/**
 * Creates necessary directory pages.
 *
 * Directory pages are those WordPress pages used by BP components to display
 * content (eg, the 'groups' page created by BP).
 *
 * @since BuddyPress (1.7.0)
 *
 * @param array $default_components Components to create pages for.
 * @param string $existing 'delete' if you want to delete existing page
 *        mappings and replace with new ones. Otherwise existing page mappings
 *        are kept, and the gaps filled in with new pages. Default: 'keep'.
 */
function cp_core_add_page_mappings( $components, $existing = 'keep' ) {

	// Make sure that the pages are created on the root blog no matter which Dashboard the setup is being run on
	if ( ! cp_is_root_blog() )
		switch_to_blog( cp_get_root_blog_id() );

	$pages = cp_core_get_directory_page_ids();

	// Delete any existing pages
	if ( 'delete' == $existing ) {
		foreach ( (array) $pages as $page_id ) {
			wp_delete_post( $page_id, true );
		}

		$pages = array();
	}

	$page_titles = array(
		'categories' => _x( 'Categories', 'Page title for the Categorys directory.', 'categorypress' ),
		'posts'      => _x( 'Posts', 'Page title for the Posts directory.', 'categorypress' ),
		//'members'    => _x( 'Members', 'Page title for the Members directory.', 'categorypress' )
	);

	$pages_to_create = array();
	foreach ( array_keys( $components ) as $component_name ) {
		if ( ! isset( $pages[ $component_name ] ) && isset( $page_titles[ $component_name ] ) ) {
			$pages_to_create[ $component_name ] = $page_titles[ $component_name ];
		}
	}

	// Register and Activate are not components, but need pages when
	// registration is enabled
	//if ( cp_get_signup_allowed() ) {
	//	foreach ( array( 'register', 'activate' ) as $slug ) {
	//		if ( ! isset( $pages[ $slug ] ) ) {
	//			$pages_to_create[ $slug ] = $page_titles[ $slug ];
	//		}
	//	}
	//}

	// No need for a Sites directory unless we're on multisite
	if ( ! is_multisite() && isset( $pages_to_create['sites'] ) ) {
		unset( $pages_to_create['sites'] );
	}

	// Members must always have a page, no matter what
	//if ( ! isset( $pages['members'] ) && ! isset( $pages_to_create['members'] ) ) {
	//	$pages_to_create['members'] = $page_titles['members'];
	//}

	// Create the pages
	foreach ( $pages_to_create as $component_name => $page_name ) {
		$pages[ $component_name ] = wp_insert_post( array(
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_status'    => 'publish',
			'post_title'     => $page_name,
			'post_type'      => 'page',
		) );
	}

	// Save the page mapping
	cp_update_option( 'cp-pages', $pages );

	// If we had to switch_to_blog, go back to the original site.
	if ( ! cp_is_root_blog() )
		restore_current_blog();
}

/**
 * Set the $bp->is_directory global.
 *
 * @global BuddyPress $bp The one true BuddyPress instance.
 *
 * @param bool $is_directory Optional. Default: false.
 * @param string $component Optional. Component name. Default: the current
 *        component.
 */
function cp_update_is_directory( $is_directory = false, $component = '' ) {
	global $bp;
	
	if ( empty( $component ) )
		$component = cp_current_component();
	
	$bp->is_directory = apply_filters( 'cp_update_is_directory', $is_directory, $component );
}

/**
 * Sanitize an 'order' parameter for use in building SQL queries.
 *
 * Strings like 'DESC', 'desc', ' desc' will be interpreted into 'DESC'.
 * Everything else becomes 'ASC'.
 *
 * @since BuddyPress (1.8.0)
 *
 * @param string $order The 'order' string, as passed to the SQL constructor.
 * @return string The sanitized value 'DESC' or 'ASC'.
 */
function cp_esc_sql_order( $order = '' ) {
	$order = strtoupper( trim( $order ) );
	return 'DESC' === $order ? 'DESC' : 'ASC';
}

function cp_get_thumbnail_url ( $url, $size = array( 80, 80 ) ) {
	if ( !empty( $url ) )
		return $url;
	
	$url = get_template_directory_uri() . '/images/thumb/' . $size[0] . '_' . $size[1] . '.png';
	return $url;
}


function cp_get_short_time( $the_time ) {
	$now_time = date( "Y-m-d H:i:s" );
	$now_time = strtotime( $now_time );
	$show_time = strtotime( $the_time );
	$dur = $now_time - $show_time;
	
	if ( $dur < 60 ) {
		return $dur.'秒前';
	} else {
		if ( $dur < 3600 ) {
			return floor( $dur/60 ).'分钟前';
		} else {
			if ( $dur < 86400 ) {
				return floor( $dur / 3600 ).'小时前';
			} else {
				if ( $dur < 259200 ) {// 3 day
					return floor( $dur / 86400 ).'天前';
				} else {
					return $the_time;
				}
			}
		}
	}
}

function cp_human_time_diff ( $from, $to = '', $less_than = DAY_IN_SECONDS ) {
	if ( empty( $to ) )
		$to = time();
	
	$diff = (int) abs( $to - $from );
	
	if ( $diff < WEEK_IN_SECONDS ) 
		return human_time_diff( $from, $to );
	else
		return date( 'Y-m-d', $from );
}


function cp_core_admin_hook() {
	$hook = cp_core_do_network_admin() ? 'network_admin_menu' : 'admin_menu';
	
	return apply_filters( 'cp_core_admin_hook', $hook );
}

function cp_core_do_network_admin() {
	
	// Default
	$retval = cp_is_network_activated();
	
	if ( cp_is_multiblog_mode() )
		$retval = false;
	
	return (bool) apply_filters( 'cp_core_do_network_admin', $retval );
}


function cp_admin_url( $path = '', $scheme = 'admin' ) {
	echo cp_get_admin_url( $path, $scheme );
}
	/**
	 * Return the correct admin URL based on BuddyPress and WordPress configuration.
	 *
	 * @since BuddyPress (1.5.0)
	 *
	 * @uses bp_core_do_network_admin()
	 * @uses network_admin_url()
	 * @uses admin_url()
	 *
	 * @param string $path Optional. The sub-path under /wp-admin to be
	 *        appended to the admin URL.
	 * @param string $scheme The scheme to use. Default is 'admin', which
	 *        obeys {@link force_ssl_admin()} and {@link is_ssl()}. 'http'
	 *        or 'https' can be passed to force those schemes.
	 * @return string Admin url link with optional path appended.
	 */
	function cp_get_admin_url( $path = '', $scheme = 'admin' ) {

		// Links belong in network admin
		if ( cp_core_do_network_admin() ) {
			$url = network_admin_url( $path, $scheme );

		// Links belong in site admin
		} else {
			$url = admin_url( $path, $scheme );
		}

		return $url;
	}
	
function cp_admin_list_table_current_bulk_action() {
	
	$action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	
	// If the bottom is set, let it override the action
	if ( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] != "-1" ) {
		$action = $_REQUEST['action2'];
	}
	
	return $action;
}