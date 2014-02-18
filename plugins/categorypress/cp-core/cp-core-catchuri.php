<?php


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function cp_core_set_uri_globals() {
	global $cp, $current_blog, $wp_rewrite;
	
	// Don't catch URIs on non-root blogs unless multiblog mode is on
	if ( !cp_is_root_blog() && !cp_is_multiblog_mode() )
		return false;
	
	// Define local variables
	$root_profile = $match   = false;
	$key_slugs    = $matches = $uri_chunks = array();
	
	// Fetch all the WP page names for each component
	if ( empty( $cp->pages ) )
		$cp->pages = cp_core_get_directory_pages();
	
	// Ajax or not?
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX || strpos( $_SERVER['REQUEST_URI'], 'wp-load.php' ) )
		$path = cp_core_referrer();
	else
		$path = esc_url( $_SERVER['REQUEST_URI'] );
	
	// Filter the path
	$path = apply_filters( 'cp_uri', $path );
	
	// Take GET variables off the URL to avoid problems
	$path = strtok( $path, '?' );
	
	// Fetch current URI and explode each part separated by '/' into an array
	$cp_uri = explode( '/', $path );
	
	// Loop and remove empties
	foreach ( (array) $cp_uri as $key => $uri_chunk ) {
		if ( empty( $cp_uri[$key] ) ) {
			unset( $cp_uri[$key] );
		}
	}
	
	// If running off blog other than root, any subdirectory names must be
	// removed from $cp_uri. This includes two cases:
	//
	//    1. when WP is installed in a subdirectory,
	//    2. when BP is running on secondary blog of a subdirectory
	//       multisite installation. Phew!
	if ( is_multisite() && !is_subdomain_install() && ( cp_is_multiblog_mode() || 1 != cp_get_root_blog_id() ) ) {
		
		// Blow chunks
		$chunks = explode( '/', $current_blog->path );
		
		// If chunks exist...
		if ( !empty( $chunks ) ) {
			
			// ...loop through them...
			foreach( $chunks as $key => $chunk ) {
				$bkey = array_search( $chunk, $cp_uri );
				
				// ...and unset offending keys
				if ( false !== $bkey ) {
					unset( $cp_uri[$bkey] );
				}
				
				$cp_uri = array_values( $cp_uri );
			}
		}
	}
	
	// Get site path items
	$paths = explode( '/', cp_core_get_site_path() );
	
	// Take empties off the end of path
	if ( empty( $paths[count( $paths ) - 1] ) )
		array_pop( $paths );
	
	// Take empties off the start of path
	if ( empty( $paths[0] ) )
		array_shift( $paths );
	
	// Reset indexes
	$cp_uri = array_values( $cp_uri );
	$paths  = array_values( $paths );
	
	// Unset URI indices if they intersect with the paths
	foreach ( (array) $cp_uri as $key => $uri_chunk ) {
		if ( isset( $paths[$key] ) && $uri_chunk == $paths[$key] ) {
			unset( $cp_uri[$key] );
		}
	}
	
	// Reset the keys by merging with an empty array
	$cp_uri = array_merge( array(), $cp_uri );
	
	// If a component is set to the front page, force its name into $cp_uri
	// so that $current_component is populated (unless a specific WP post is being requested
	// via a URL parameter, usually signifying Preview mode)
	if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) && empty( $cp_uri ) && empty( $_GET['p'] ) && empty( $_GET['page_id'] ) ) {
		$post = get_post( get_option( 'page_on_front' ) );
		if ( !empty( $post ) ) {
			$cp_uri[0] = $post->post_name;
		}
	}
	
	// Keep the unfiltered URI safe
	$cp->unfiltered_uri = $cp_uri;
	
	// Don't use $cp_unfiltered_uri, this is only for backpat with old plugins. Use $cp->unfiltered_uri.
	$GLOBALS['cp_unfiltered_uri'] = &$cp->unfiltered_uri;
	
	// Get slugs of pages into array
	foreach ( (array) $cp->pages as $page_key => $cp_page )
		$key_slugs[$page_key] = trailingslashit( '/' . $cp_page->slug );
	
	// Bail if keyslugs are empty, as BP is not setup correct
	if ( empty( $key_slugs ) )
		return;
	
	// Loop through page slugs and look for exact match to path
	foreach ( $key_slugs as $key => $slug ) {
		if ( $slug == $path ) {
			$match      = $cp->pages->{$key};
			$match->key = $key;
			$matches[]  = 1;
			break;
		}
	}
	
	// No exact match, so look for partials
	if ( empty( $match ) ) {

		// Loop through each page in the $cp->pages global
		foreach ( (array) $cp->pages as $page_key => $cp_page ) {

			// Look for a match (check members first)
			if ( in_array( $cp_page->name, (array) $cp_uri ) ) {

				// Match found, now match the slug to make sure.
				$uri_chunks = explode( '/', $cp_page->slug );

				// Loop through uri_chunks
				foreach ( (array) $uri_chunks as $key => $uri_chunk ) {

					// Make sure chunk is in the correct position
					if ( !empty( $cp_uri[$key] ) && ( $cp_uri[$key] == $uri_chunk ) ) {
						$matches[] = 1;

					// No match
					} else {
						$matches[] = 0;
					}
				}

				// Have a match
				if ( !in_array( 0, (array) $matches ) ) {
					$match      = $cp_page;
					$match->key = $page_key;
					break;
				};

				// Unset matches
				unset( $matches );
			}

			// Unset uri chunks
			unset( $uri_chunks );
		}
	}

	// URLs with cp_ENABLE_ROOT_PROFILES enabled won't be caught above
	if ( empty( $matches ) && cp_core_enable_root_profiles() ) {

		// Switch field based on compat
		$field = cp_is_username_compatibility_mode() ? 'login' : 'slug';

		// Make sure there's a user corresponding to $cp_uri[0]
		if ( !empty( $cp->pages->members ) && !empty( $cp_uri[0] ) && $root_profile = get_user_by( $field, $cp_uri[0] ) ) {

			// Force BP to recognize that this is a members page
			$matches[]  = 1;
			$match      = $cp->pages->members;
			$match->key = 'members';
		}
	}

	// Search doesn't have an associated page, so we check for it separately
	if ( !empty( $cp_uri[0] ) && ( cp_get_search_slug() == $cp_uri[0] ) ) {
		$matches[]   = 1;
		$match       = new stdClass;
		$match->key  = 'search';
		$match->slug = cp_get_search_slug();
	}

	// This is not a BuddyPress page, so just return.
	if ( empty( $matches ) )
		return false;

	$wp_rewrite->use_verbose_page_rules = false;

	// Find the offset. With $root_profile set, we fudge the offset down so later parsing works
	$slug       = !empty ( $match ) ? explode( '/', $match->slug ) : '';
	$uri_offset = empty( $root_profile ) ? 0 : -1;

	// Rejig the offset
	if ( !empty( $slug ) && ( 1 < count( $slug ) ) ) {
		array_pop( $slug );
		$uri_offset = count( $slug );
	}

	// Global the unfiltered offset to use in cp_core_load_template().
	// To avoid PHP warnings in cp_core_load_template(), it must always be >= 0
	$cp->unfiltered_uri_offset = $uri_offset >= 0 ? $uri_offset : 0;

	// We have an exact match
	if ( isset( $match->key ) ) {

		// Set current component to matched key
		$cp->current_component = $match->key;

		// If members component, do more work to find the actual component
		if ( 'members' == $match->key ) {

			// Viewing a specific user
			if ( !empty( $cp_uri[$uri_offset + 1] ) ) {

				// Switch the displayed_user based on compatbility mode
				if ( cp_is_username_compatibility_mode() ) {
					$cp->displayed_user->id = (int) cp_core_get_userid( urldecode( $cp_uri[$uri_offset + 1] ) );
				} else {
					$cp->displayed_user->id = (int) cp_core_get_userid_from_nicename( urldecode( $cp_uri[$uri_offset + 1] ) );
				}

				if ( !cp_displayed_user_id() ) {

					// Prevent components from loading their templates
					$cp->current_component = '';

					cp_do_404();
					return;
				}

				// If the displayed user is marked as a spammer, 404 (unless logged-
				// in user is a super admin)
				if ( cp_displayed_user_id() && cp_is_user_spammer( cp_displayed_user_id() ) ) {
					if ( cp_current_user_can( 'cp_moderate' ) ) {
						cp_core_add_message( __( 'This user has been marked as a spammer. Only site admins can view this profile.', 'buddypress' ), 'warning' );
					} else {
						cp_do_404();
						return;
					}
				}

				// Bump the offset
				if ( isset( $cp_uri[$uri_offset + 2] ) ) {
					$cp_uri                = array_merge( array(), array_slice( $cp_uri, $uri_offset + 2 ) );
					$cp->current_component = $cp_uri[0];

				// No component, so default will be picked later
				} else {
					$cp_uri                = array_merge( array(), array_slice( $cp_uri, $uri_offset + 2 ) );
					$cp->current_component = '';
				}

				// Reset the offset
				$uri_offset = 0;
			}
		}
	}

	if ( !empty( $cp_uri ) ) {
		$post_item = $cp_uri[ count( $cp_uri ) - 1 ];
		$str_len = strlen( $post_item );
		if ( strtolower( substr( $post_item, $str_len - 6, $str_len - 1 ) ) == '.shtml' ) {
			$post_item = array_pop( $cp_uri );
			$post_item = explode( '.', $post_item );
			$cp->current_post = $post_item[0];
		}
	}
	array_shift( $cp_uri );
	// Set the current categories
	$cp->current_categories = array_slice( $cp_uri, $uri_offset + 1 );//isset( $cp_uri[$uri_offset + 1] ) ? $cp_uri[$uri_offset + 1] : '';

	// Slice the rest of the $cp_uri array and reset offset
	$cp_uri      = array_slice( $cp_uri, $uri_offset + 2 );
	$uri_offset  = 0;

	// Set the entire URI as the action variables, we will unset the current_component and action in a second
	$cp->action_variables = $cp_uri;

	// Reset the keys by merging with an empty array
	$cp->action_variables = array_merge( array(), $cp->action_variables );
	
}

/**
 * Are root profiles enabled and allowed?
 *
 * @since BuddyPress (1.6.0)
 *
 * @return bool True if yes, false if no.
 */
function cp_core_enable_root_profiles() {
	
	$retval = false;
	
	if ( defined( 'CP_ENABLE_ROOT_PROFILES' ) && ( true == CP_ENABLE_ROOT_PROFILES ) )
		$retval = true;
	
	return apply_filters( 'cp_core_enable_root_profiles', $retval );
}

/**
 * Load a specific template file with fallback support.
 *
 * Example:
 *   cp_core_load_template( 'members/index' );
 * Loads:
 *   wp-content/themes/[activated_theme]/members/index.php
 *
 * @param array $templates Array of templates to attempt to load.
 * @return bool|null Returns false on failure.
 */
function cp_core_load_template( $templates ) {
	global $post, $bp, $wp_query, $wpdb;
	
	// Determine if the root object WP page exists for this request
	// note: get_page_by_path() breaks non-root pages
	if ( !empty( $bp->unfiltered_uri_offset ) ) {
		if ( !$page_exists = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s", $bp->unfiltered_uri[$bp->unfiltered_uri_offset] ) ) ) {
			return false;
		}
	}
	
	// Set the root object as the current wp_query-ied item
	$object_id = 0;
	foreach ( (array) $bp->pages as $page ) {
		if ( $page->name == $bp->unfiltered_uri[$bp->unfiltered_uri_offset] ) {
			$object_id = $page->id;
		}
	}
	
	// Make the queried/post object an actual valid page
	if ( !empty( $object_id ) ) {
		$wp_query->queried_object    = get_post( $object_id );
		$wp_query->queried_object_id = $object_id;
		$post                        = $wp_query->queried_object;
	}
	
	// Fetch each template and add the php suffix
	$filtered_templates = array();
	foreach ( (array) $templates as $template ) {
		$filtered_templates[] = $template . '.php';
	}
	
	// Filter the template locations so that plugins can alter where they are located
	$located_template = apply_filters( 'cp_located_template', locate_template( (array) $filtered_templates, false ), $filtered_templates );
	if ( !empty( $located_template ) ) {
		
		// Template was located, lets set this as a valid page and not a 404.
		status_header( 200 );
		$wp_query->is_page     = true;
		$wp_query->is_singular = true;
		$wp_query->is_404      = false;
		
		do_action( 'cp_core_pre_load_template', $located_template );
		
		load_template( apply_filters( 'cp_load_template', $located_template ) );
		
		do_action( 'cp_core_post_load_template', $located_template );
		
		// Kill any other output after this.
		exit();
		
		// No template found, so setup theme compatability
		// @todo Some other 404 handling if theme compat doesn't kick in
	} else {
		
		// We know where we are, so reset important $wp_query bits here early.
		// The rest will be done by cp_theme_compat_reset_post() later.
		if ( is_categorypress() ) {
			status_header( 200 );
			$wp_query->is_page     = true;
			$wp_query->is_singular = true;
			$wp_query->is_404      = false;
		}
		
		do_action( 'cp_setup_theme_compat' );
	}
}
