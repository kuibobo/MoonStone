<?php

/**
 * CategoryPress Categories Functions
 *
 * Functions are where all the magic happens in CategoryPress.
 *
 * @package CategoryPress
 * @subpackage CategoriesFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function categories_get_category( $args = '' ) {
	$defaults = array(
		'category_id' => false,
		'load_users'  => false
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	$cache_key = 'cp_categories_category_' . $category_id . ( $load_users ? '_load_users' : '_noload_users' );

	if ( !$category = wp_cache_get( $cache_key, 'cp' ) ) {
		$category = new CP_Categories_Category( $category_id, true, $load_users );
		wp_cache_set( $cache_key, $category, 'cp' );
	}

	return apply_filters( 'categories_get_category', $category );
}

/**
 * Get a category slug by its ID
 *
 * @param int $category_id The numeric ID of the category
 * @return string The category's slug
 */
function categories_get_slug( $category_id ) {
	$category = categories_get_category( array( 'category_id' => $category_id ) );
	return !empty( $category->slug ) ? $category->slug : '';
}


function categories_create_category( $args = '' ) {
	extract( $args );

	/**
	 * Possible parameters (pass as assoc array):
	 *	'category_id'
	 *	'parent_id'
	 *	'name'
	 *	'description'
	 *	'slug'
	 *	'date_created'
	 */

	if ( !empty( $category_id ) )
		$category = categories_get_category( array( 'category_id' => $category_id ) );
	else
		$category = new CP_Categories_Category;

	if ( !empty( $parent_id ) )
		$category->parent_id = $parent_id;
	else
		$category->parent_id = 0;

	if ( isset( $name ) )
		$category->name = $name;

	if ( isset( $description ) )
		$category->description = $description;

	if ( isset( $slug ) && categories_check_slug( $slug ) )
		$category->slug = $slug;

	if ( isset( $date_created ) )
		$category->date_created = $date_created;

	if ( !$category->save() )
		return false;

	do_action( 'categories_created_category', $category->id, $category );

	return $category->id;
}

function categories_update_categorymeta( $category_id, $meta_key, $meta_value ) {
	global $wpdb, $cp;

	if ( !is_numeric( $category_id ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string( $meta_value ) )
		$meta_value = stripslashes( esc_sql( $meta_value ) );

	$meta_value = maybe_serialize( $meta_value );

	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $cp->categories->table_name_categorymeta . " WHERE category_id = %d AND meta_key = %s", $category_id, $meta_key ) );

	if ( !$cur )
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $cp->categories->table_name_categorymeta . " ( category_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $category_id, $meta_key, $meta_value ) );
	else if ( $cur->meta_value != $meta_value )
		$wpdb->query( $wpdb->prepare( "UPDATE " . $cp->categories->table_name_categorymeta . " SET meta_value = %s WHERE category_id = %d AND meta_key = %s", $meta_value, $category_id, $meta_key ) );
	else
		return false;

	// Update the cached object and recache
	wp_cache_set( 'cp_categories_categorymeta_' . $category_id . '_' . $meta_key, $meta_value, 'cp' );

	return true;
}

function categories_get_id( $category_slug ) {
	return (int)CP_Categories_Category::category_exists( $category_slug );
}

function categories_get_categories( $args = '' ) {

	$defaults = array(
		'type'            => false,    // active, newest, alphabetical, random, popular, most-forum-topics or most-forum-posts
		'order'           => 'DESC',   // 'ASC' or 'DESC'
		'orderby'         => 'date_created', // date_created, last_activity, total_member_count, name, random
		'user_id'         => false,    // Pass a user_id to limit to only categories that this user is a member of
		'include'         => false,    // Only include these specific categories (category_ids)
		'exclude'         => false,    // Do not include these specific categories (category_ids)
		'search_terms'    => false,    // Limit to categories that match these search terms
		'meta_query'      => false,    // Filter by categorymeta. See WP_Meta_Query for syntax
		'show_hidden'     => false,    // Show hidden categories to non-admins
		'per_page'        => 20,       // The number of results to return per page
		'page'            => 1,        // The page to return if limiting per page
		'populate_extras' => true,     // Fetch meta such as is_banned and is_member
	);

	$r = wp_parse_args( $args, $defaults );

	$categories = CP_Categories_Category::get( array(
		'type'            => $r['type'],
		'user_id'         => $r['user_id'],
		'include'         => $r['include'],
		'exclude'         => $r['exclude'],
		'search_terms'    => $r['search_terms'],
		'meta_query'      => $r['meta_query'],
		'show_hidden'     => $r['show_hidden'],
		'per_page'        => $r['per_page'],
		'page'            => $r['page'],
		'populate_extras' => $r['populate_extras'],
		'order'           => $r['order'],
		'orderby'         => $r['orderby'],
	) );

	return apply_filters_ref_array( 'categories_get_categories', array( &$categories, &$r ) );
}

function categories_get_total_category_count() {
	if ( !$count = wp_cache_get( 'cp_total_category_count', 'cp' ) ) {
		$count = CP_Categories_Category::get_total_category_count();
		wp_cache_set( 'cp_total_category_count', $count, 'cp' );
	}

	return $count;
}

function categories_get_user_categories( $user_id = 0, $pag_num = 0, $pag_page = 0 ) {

	if ( empty( $user_id ) )
		$user_id = bp_displayed_user_id();

	return BP_Groups_Member::get_category_ids( $user_id, $pag_num, $pag_page );
}

function categories_total_categories_for_user( $user_id = 0 ) {

	if ( empty( $user_id ) )
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();

	if ( !$count = wp_cache_get( 'cp_total_categories_for_user_' . $user_id, 'cp' ) ) {
		$count = BP_Groups_Member::total_category_count( $user_id );
		wp_cache_set( 'cp_total_categories_for_user_' . $user_id, $count, 'cp' );
	}

	return $count;
}

function cp_do_404( $redirect = 'remove_canonical_direct' ) {
	global $wp_query;
	
	do_action( 'cp_do_404', $redirect );
	
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	
	if ( 'remove_canonical_direct' == $redirect )
		remove_action( 'template_redirect', 'redirect_canonical' );
}